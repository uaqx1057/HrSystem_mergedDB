<?php

namespace App\Http\Controllers;

use App\Models\DriverDocument;
use Illuminate\Support\Facades\Storage;

/**
 * The standalone "Driver Documents" admin page (/account/driver-documents) was
 * removed. Driver documents are still managed from the driver profile (Branches /
 * Driver Types). Only the inline file preview is still served from here.
 */
class DriverDocumentController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Driver Document';
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
