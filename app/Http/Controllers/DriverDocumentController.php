<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\DriverDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Response;

class DriverDocumentController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Driver Document';
    }

    public function index()
    {
        $this->documents = DriverDocument::with('driver')->latest()->paginate(20);

        return view('driver-documents.index', $this->data);
    }

    public function create()
    {
        $this->drivers = Driver::withoutGlobalScopes()->select('id', 'name')->get();

        return view('driver-documents.create', $this->data);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'driver_id'       => 'required|integer|exists:drivers,id',
            'document_type'   => 'required|in:iqama,passport,visa,license,medical,contract,other',
            'upload_document' => 'required|file|mimes:txt,pdf,doc,xls,xlsx,docx,rtf,png,jpg,jpeg,svg|max:10240',
            'expires_at'      => 'nullable|date',
            'notes'           => 'nullable|string',
        ]);

        $driver = Driver::withoutGlobalScopes()->findOrFail($validated['driver_id']);

        $file      = $request->file('upload_document');
        $extension = $file->extension();
        $timestamp = now()->format('YmdHis');

        // Build a clean, readable custom name: DriverName_EmployeeNo_DocType_Timestamp
        $namePart = Str::slug($driver->name, '_');                 // "Usman"
        $idPart   = $driver->iqaama_number;            // swap to actual field, e.g. iqama_no
        $typePart = $validated['document_type'];                    // "iqama"

        $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";

        // Keep files organized: document_type/driver_id/filename
        $folder   = $validated['driver_id'];
        $fileName = $timestamp . '_' . uniqid() . '.' . $extension; // physical filename stays unique/safe

        // Stored on the 'driver_documents' disk, which points at DRIVER_DOCUMENT_PATH
        $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

        DriverDocument::create([
            'driver_id'      => $validated['driver_id'],
            'document_type'  => $validated['document_type'],
            'file_path'      => $relativePath,
            'original_name'  => $customName,
            'file_size'      => $file->getSize(),
            'uploaded_from'  => 'hr',
            'uploaded_by'    => user()->id,
            'notes'          => $validated['notes'] ?? null,
            'expires_at'     => $validated['expires_at'] ?? null,
        ]);

        return redirect()
            ->route('driver-documents.index')
            ->with('success', 'Document uploaded successfully.');
    }

    public function show(string $id)
    {
        $document = DriverDocument::findOrFail($id);

        return view('driver-documents.show', ['document' => $document]);
    }



    public function edit(string $id)
    {
        $this->document = DriverDocument::findOrFail($id);
        $this->drivers  = Driver::withoutGlobalScopes()->select('id', 'name')->get();

        return view('driver-documents.edit', $this->data);
    }

    public function update(Request $request, string $id)
    {
        $document = DriverDocument::findOrFail($id);

        $validated = $request->validate([
            'driver_id'       => 'required|integer|exists:drivers,id',
            'document_type'   => 'required|in:iqama,passport,visa,license,medical,contract,mobile,other',
            'upload_document' => 'nullable|file|mimes:txt,pdf,doc,xls,xlsx,docx,rtf,png,jpg,jpeg,svg|max:10240',
            'expires_at'      => 'nullable|date',
            'notes'           => 'nullable|string',
        ]);

        $driver = Driver::withoutGlobalScopes()->findOrFail($validated['driver_id']);

        $data = [
            'driver_id'     => $validated['driver_id'],
            'document_type' => $validated['document_type'],
            'expires_at'    => $validated['expires_at'] ?? null,
            'notes'         => $validated['notes'] ?? null,
        ];

        if ($request->hasFile('upload_document')) {
            $file      = $request->file('upload_document');
            $extension = $file->extension();
            $timestamp = now()->format('YmdHis');

            // Same readable naming pattern as store(): DriverName_IqamaNo_DocType_Timestamp
            $namePart = Str::slug($driver->name, '_');
            $idPart   = $driver->iqaama_number;
            $typePart = $validated['document_type'];

            $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";

            // Keep files organized: document_type/driver_id/filename
            $folder   = $validated['driver_id'];
            $fileName = $timestamp . '_' . uniqid() . '.' . $extension;

            $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

            // Delete old file only after the new one is confirmed stored,
            // so a failed upload never leaves the document with no file at all
            if ($document->file_path && Storage::disk('driver_documents')->exists($document->file_path)) {
                Storage::disk('driver_documents')->delete($document->file_path);
            }

            $data['file_path']     = $relativePath;
            $data['original_name'] = $customName;
            $data['file_size']     = $file->getSize();
        }

        $document->update($data);

        return redirect()
            ->route('driver-documents.index')
            ->with('success', 'Document updated successfully.');
    }

    public function destroy(string $id)
    {
        // dd($id);
        $document = DriverDocument::findOrFail($id);

        Storage::disk('driver_documents')->delete($document->file_path);
        $document->delete(); // soft delete row

        return redirect()
            ->route('driver-documents.index')
            ->with('success', 'Document deleted successfully.');
    }
    public function preview($id)
    {
        $document = DriverDocument::findOrFail($id);

        $fullPath = Storage::disk('driver_documents')->path($document->file_path);

        if (!file_exists($fullPath)) {
            abort(404, 'File not found');
        }

        $contents = file_get_contents($fullPath);
        $mimeType = mime_content_type($fullPath);

        ob_clean();

        return response($contents, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . $document->original_name . '"')
            ->header('Content-Length', strlen($contents));
    }
}
