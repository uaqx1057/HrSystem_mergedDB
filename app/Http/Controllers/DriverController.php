<?php

namespace App\Http\Controllers;

use App\DataTables\BusinessesDriverDataTable;
use App\DataTables\DriversDataTable;
use App\DataTables\InsuranceDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\Driver\StoreRequest;
use App\Models\{Driver, DriverDocument, DriverType, User};
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use App\Http\Requests\Admin\Driver\UpdateRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DriverController extends AccountBaseController
{
    use ImportExcel;

    public function __construct(private BusinessesDriverDataTable $businessDataTable)
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.drivers';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('drivers', $this->user->modules));
            return $next($request);
        });
    }

    public function getDriverType(Request $request){
        return DriverType::find($request->id);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(DriversDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_drivers');
        abort_403(!in_array($viewPermission, ['all']));
        return $dataTable->render('drivers.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $addPermission = user()->permission('add_drivers');
        abort_403(!in_array($addPermission, ['all', 'added']));

        $this->pageTitle = __('app.addDriver');
        $this->countries = countries();
        $this->view = 'drivers.ajax.create';
        $this->driver_types = DriverType::all();
        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('drivers.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $addPermission = user()->permission('add_drivers');
        abort_403(!in_array($addPermission, ['all', 'added']));
        DB::beginTransaction();
        try {
            $validated = $request->validated();

            $validated['insurance_expiry_date'] = $request->insurance_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->insurance_expiry_date)->format('Y-m-d') : null;
            $validated['license_expiry_date'] = $request->license_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->license_expiry_date)->format('Y-m-d') : null;
            $validated['iqaama_expiry_date'] = $request->iqaama_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->iqaama_expiry_date)->format('Y-m-d') : null;
            $validated['date_of_birth'] = $request->date_of_birth ? Carbon::createFromFormat($this->company->date_format, $request->date_of_birth)->format('Y-m-d') : null;

            if ($request->hasFile('image')) {
                $validated['image'] = Files::uploadLocalOrS3($request->image, 'avatar', 300);
            }

            Driver::create($validated);

            DB::commit();
        } catch (\Exception $e) {
            logger($e->getMessage());
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support '. $e->getMessage());
        }


        if (request()->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('drivers.index')]);
    }

    public function ajaxLoadDriver(Request $request)
    {
        $search = $request->search;

        $drivers = Driver::withoutGlobalScopes()->orderby('iqaama_number')
            ->select('id', 'iqaama_number')
            ->when($search, function ($query) use ($search) {
                $query->where('iqaama_number', 'like', '%' . $search . '%');
            })
            ->take(20)
            ->get();

        // dd($drivers);
        $response = array();

        foreach ($drivers as $driver) {

            $response[] = array(
                'id' => $driver->id,
                'text' => $driver->iqaama_number
            );

        }

        return response()->json($response);
    }

    public function ajaxLoadLinkedDriver(Request $request)
    {
        $this->linkDriverPermission = user()->permission('add_coordinator_reports') == 'all' || in_array('admin', user_roles());
        abort_403(!($this->linkDriverPermission));

        $search = $request->search;

        $drivers = [];


        if(in_array('admin', user_roles() )) {
            $drivers = Driver::query()
            ->orderby('name')
            ->select('drivers.id', 'drivers.name', 'drivers.iqaama_number')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                ->orWhere('iqaama_number', 'like', '%' . $search . '%');
            })
            ->take(20)
            ->get();
        }else{
            $driversQuery = Driver::select('drivers.id', 'drivers.name')
            ->join('branches', 'drivers.branch_id', '=', 'branches.id')
            ->join('branch_employee', 'branches.id', '=', 'branch_employee.branch_id')
            ->where('branch_employee.employee_id', user()->id)
            ->when($search, function ($query) use ($search) {
                $query->where('drivers.name', 'like', '%' . $search . '%')
                ->orWhere('drivers.iqaama_number', 'like', '%' . $search . '%');
            })
            // ->orderBy('drivers.name')
            ->distinct()
            ->take(20);

            // Get the drivers
            $drivers = $driversQuery->get();
        }

        $response = array();

        foreach ($drivers as $driver) {

            $response[] = array(
                'id' => $driver->id,
                'text' => $driver->iqaama_number .' - '. $driver->name
            );
        }
        return response()->json($response);
    }


    public function businesses()
    {
        $tab = request('tab');
        $this->activeTab = $tab ?: 'businesses';
        $this->view = 'drivers.ajax.businesses';

        return $this->businessDataTable->with('driver_id', $this->driver->id)->render('drivers.show', $this->data);
    }
    public function insurance($driverId)
    {
        $tab = request('tab');
        $this->activeTab = $tab ?: 'businesses';
        $this->view = 'drivers.ajax.insurance';

        $dataTable = new InsuranceDataTable(0,$driverId);

        return $dataTable->with('driver_id', $this->driver->id)->render('drivers.show', $this->data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->viewPermission = user()->permission('view_drivers');
        abort_403(!($this->viewPermission == 'all'));

        $this->driver = Driver::withoutGlobalScopes()->findOrFail($id);

        $this->driver_iqama = DriverDocument::where('driver_id', $id)->where('document_type', 'iqama')->first();
        $this->driver_license = DriverDocument::where('driver_id', $id)->where('document_type', 'license')->first();
        $this->driver_medical = DriverDocument::where('driver_id', $id)->where('document_type', 'medical')->first();
        $this->driver_contract = DriverDocument::where('driver_id', $id)->where('document_type', 'contract')->first();
        $this->driver_mobile = DriverDocument::where('driver_id', $id)->where('document_type', 'mobile')->first();
        $this->driver_other = DriverDocument::where('driver_id', $id)->where('document_type', 'other')->first();

        $tab = request('tab');

        $this->pageTitle = $this->driver->name;

        switch ($tab) {
            case 'employment':
                $this->view = 'drivers.ajax.employment';
                break;
            case 'documents':
                $this->view = 'drivers.ajax.documents';
                break;

            case 'locality':
                $this->view = 'drivers.ajax.locality';
                $this->countries = countries();
                break;

            case 'banking':
                $this->view = 'drivers.ajax.banking';
                break;
            case 'businesses':
                return $this->businesses();
            case 'insurance':
                return $this->insurance($id);

            default:
                $this->view = 'drivers.ajax.profile';
                break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['views' => $this->view, 'status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->activeTab = $tab ?: 'profile';

        return view('drivers.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->editPermission = user()->permission('edit_drivers');
        abort_403(!($this->editPermission == 'all'));

        $this->pageTitle = __('app.update');

        $this->driver = Driver::withoutGlobalScopes()->findOrFail($id);
        $this->driver_types = DriverType::all();

        $this->driver_iqama = DriverDocument::where('driver_id', $id)->where('document_type', 'iqama')->first();
        $this->driver_license = DriverDocument::where('driver_id', $id)->where('document_type', 'license')->first();
        $this->driver_medical = DriverDocument::where('driver_id', $id)->where('document_type', 'medical')->first();
        $this->driver_contract = DriverDocument::where('driver_id', $id)->where('document_type', 'contract')->first();
        $this->driver_mobile = DriverDocument::where('driver_id', $id)->where('document_type', 'mobile')->first();
        $this->driver_other = DriverDocument::where('driver_id', $id)->where('document_type', 'other')->first();

        $tab = request('tab');

        switch($tab) {
            case 'iqama':
                $this->view = 'drivers.ajax.iqama-modal';
                break;
            case 'license':
                $this->view = 'drivers.ajax.license-modal';
                break;
            case 'sim-form':
                $this->view = 'drivers.ajax.sim-form-modal';
                break;
            case 'mobile-form':
                $this->view = 'drivers.ajax.mobile-form-modal';
                break;
            case 'medical':
                $this->view = 'drivers.ajax.medical-modal';
                break;
            case 'other-document':
                $this->view = 'drivers.ajax.other-document-modal';
                break;
            default:
                $this->countries = countries();
                $this->view = 'drivers.ajax.edit';
        }

        if (request()->ajax()) {
            if (!$tab) {
                $html = view($this->view, $this->data)->render();
                return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
            }

            return view($this->view, $this->data);
        }

        return view('drivers.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, string $id)
    {
        $this->editPermission = user()->permission('edit_drivers');
        abort_403(!($this->editPermission == 'all'));

        $driver = Driver::withoutGlobalScopes()->findOrFail($id);

        $validated = $request->validated();

        $validated['insurance_expiry_date'] = $request->insurance_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->insurance_expiry_date)->format('Y-m-d') : $driver->insurance_expiry_date;
        $validated['license_expiry_date'] = $request->license_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->license_expiry_date)->format('Y-m-d') : $driver->license_expiry_date;
        $validated['iqaama_expiry_date'] = $request->iqaama_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->iqaama_expiry_date)->format('Y-m-d') : $driver->iqaama_expiry_date;
        $validated['date_of_birth'] = $request->date_of_birth ? Carbon::createFromFormat($this->company->date_format, $request->date_of_birth)->format('Y-m-d') : $driver->date_of_birth;
        $validated['joining_date'] = $request->joining_date ? Carbon::createFromFormat($this->company->date_format, $request->joining_date)->format('Y-m-d') : $driver->joining_date;

        if ($request->iqama_delete == 'yes') {
            Files::deleteFile($driver->iqama, 'iqama');
            $driver->iqama = null;
            $validated['iqaama_expiry_date'] = null;
        }

        $driver_iqama = DriverDocument::where('driver_id', $id)->where('document_type', 'iqama')->first();
        if ($request->hasFile('iqama')){

            if($driver_iqama){
                if ($driver_iqama->file_path && Storage::disk('driver_documents')->exists($driver_iqama->file_path)) {
                    Storage::disk('driver_documents')->delete($driver_iqama->file_path);
                }
                $file      = $request->file('iqama');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'iqama';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::where('id',$driver_iqama->id)->update([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'iqama',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->iqama_notes,
                    'expires_at'     => $validated['iqaama_expiry_date'] ?? null,
                ]);


            } else{
                $file      = $request->file('iqama');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'iqama';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::create([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'iqama',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->iqama_notes,
                    'expires_at'     => $validated['iqaama_expiry_date'] ?? null,
                ]);
            }
            Files::deleteFile($driver->iqama, 'iqama');
            $validated['iqama'] = null;
        }

        if($driver_iqama){
            DriverDocument::where('id',$driver_iqama->id)->update([
                'expires_at'     => $validated['iqaama_expiry_date'] ?? $driver_iqama->expires_at,
                'notes'          => $request->iqama_notes ? $request->iqama_notes : $driver_iqama->notes,
            ]);
        }

        if ($request->hasFile('image'))
            $validated['image'] = Files::uploadLocalOrS3($request->image, 'image', 300);

        if ($request->license_delete == 'yes') {
            Files::deleteFile($driver->license, 'license');
            $driver->license = null;
            $validated['license_expiry_date'] = null;
        }

        $driver_license = DriverDocument::where('driver_id', $id)->where('document_type', 'license')->first();
        if ($request->hasFile('license')){
            if($driver_license){
                if ($driver_license->file_path && Storage::disk('driver_documents')->exists($driver_license->file_path)) {
                    Storage::disk('driver_documents')->delete($driver_license->file_path);
                }
                $file      = $request->file('license');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'license';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::where('id',$driver_license->id)->update([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'license',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->license_notes,
                    'expires_at'     => $validated['license_expiry_date'] ?? null,
                ]);


            } else{
                $file      = $request->file('license');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'license';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::create([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'license',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->license_notes,
                    'expires_at'     => $validated['license_expiry_date'] ?? null,
                ]);
            }
            Files::deleteFile($driver->license, 'license');
            $validated['license'] = null;
        }

        if($driver_license){
            DriverDocument::where('id',$driver_license->id)->update([
                'expires_at'     => $validated['license_expiry_date'] ?? $driver_license->expires_at,
                'notes'          => $request->license_notes ? $request->license_notes : $driver_license->notes,
            ]);
        }

        if ($request->mobile_form_delete == 'yes') {
            Files::deleteFile($driver->mobile_form, 'mobile_form');
            $driver->mobile_form = null;
        }

        $driver_mobile = DriverDocument::where('driver_id', $id)->where('document_type', 'mobile')->first();
        if ($request->hasFile('mobile_form'))
        {
            if($driver_mobile){
                if ($driver_mobile->file_path && Storage::disk('driver_documents')->exists($driver_mobile->file_path)) {
                    Storage::disk('driver_documents')->delete($driver_mobile->file_path);
                }
                $file      = $request->file('mobile_form');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'mobile';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::where('id',$driver_mobile->id)->update([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'mobile',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->mobile_notes,
                    'expires_at'     => null,
                ]);


            } else{
                $file      = $request->file('mobile_form');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'mobile';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::create([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'mobile',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->mobile_notes,
                    'expires_at'     => null,
                ]);
            }
            Files::deleteFile($driver->mobile_form, 'mobile_form');
            $validated['mobile_form'] = null;
        }

        if($driver_mobile){
            DriverDocument::where('id',$driver_mobile->id)->update([
                'notes'          => $request->mobile_notes ? $request->mobile_notes : $driver_mobile->notes,
            ]);
        }

        if ($request->sim_form_delete == 'yes') {
            Files::deleteFile($driver->sim_form, 'sim_form');
            $driver->sim_form = null;
        }

        $driver_contract = DriverDocument::where('driver_id', $id)->where('document_type', 'contract')->first();
        if ($request->hasFile('sim_form')){
            if($driver_contract){
                if ($driver_contract->file_path && Storage::disk('driver_documents')->exists($driver_contract->file_path)) {
                    Storage::disk('driver_documents')->delete($driver_contract->file_path);
                }
                $file      = $request->file('sim_form');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'contract';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::where('id',$driver_contract->id)->update([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'contract',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->sim_notes,
                    'expires_at'     => null,
                ]);


            } else{
                $file      = $request->file('sim_form');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'contract';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::create([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'contract',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->sim_notes,
                    'expires_at'     => null,
                ]);
            }
            Files::deleteFile($driver->sim_form, 'sim_form');
            $validated['sim_form'] = null;
        }

        if($driver_contract){
            DriverDocument::where('id',$driver_contract->id)->update([
                'notes'          => $request->sim_notes ? $request->sim_notes : $driver_contract->notes,
            ]);
        }

        if ($request->medical_delete == 'yes') {
            Files::deleteFile($driver->medical, 'medical');
            $driver->medical = null;
        }

        $driver_medical = DriverDocument::where('driver_id', $id)->where('document_type', 'medical')->first();
        if ($request->hasFile('medical'))
        {
            if($driver_medical){
                if ($driver_medical->file_path && Storage::disk('driver_documents')->exists($driver_medical->file_path)) {
                    Storage::disk('driver_documents')->delete($driver_medical->file_path);
                }
                $file      = $request->file('medical');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'medical';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::where('id',$driver_medical->id)->update([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'medical',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->medical_notes,
                    'expires_at'     => null,
                ]);


            } else{
                $file      = $request->file('medical');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'medical';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::create([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'medical',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->medical_notes,
                    'expires_at'     => null,
                ]);
            }
            Files::deleteFile($driver->medical, 'medical');
            $validated['medical'] = null;
        }

        if($driver_medical){
            DriverDocument::where('id',$driver_medical->id)->update([
                'notes'          => $request->medical_notes ? $request->medical_notes : $driver_medical->notes,
            ]);
        }

        if ($request->other_document_delete == 'yes') {
            Files::deleteFile($driver->other_document, 'other_document');
            $driver->other_document = null;
        }

        $driver_other = DriverDocument::where('driver_id', $id)->where('document_type', 'other')->first();
        if ($request->hasFile('other_document')){
            if($driver_other){
                if ($driver_other->file_path && Storage::disk('driver_documents')->exists($driver_other->file_path)) {
                    Storage::disk('driver_documents')->delete($driver_other->file_path);
                }
                $file      = $request->file('other_document');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'other';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::where('id',$driver_other->id)->update([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'other',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->other_notes,
                    'expires_at'     => null,
                ]);


            } else{
                $file      = $request->file('other_document');
                $extension = $file->extension();
                $timestamp = now()->format('YmdHis');

                $namePart = Str::slug($driver->name, '_');
                $idPart   = $driver->iqaama_number;
                $typePart = 'other';

                $customName = "{$namePart}_{$idPart}_{$typePart}_{$timestamp}.{$extension}";
                $folder   = $driver->id;
                $relativePath = $file->storeAs($folder, $customName, 'driver_documents');

                DriverDocument::create([
                    'driver_id'      => $driver->id,
                    'document_type'  => 'other',
                    'file_path'      => $relativePath,
                    'original_name'  => $customName,
                    'file_size'      => $file->getSize(),
                    'uploaded_from'  => 'hr',
                    'uploaded_by'    => user()->id,
                    'notes'          => $request->other_notes,
                    'expires_at'     => null,
                ]);
            }
            Files::deleteFile($driver->other_document, 'other_document');
            $validated['other_document'] = null;
        }

        if($driver_other){
            DriverDocument::where('id',$driver_other->id)->update([
                'notes'          => $request->other_notes ? $request->other_notes : $driver_other->notes,
            ]);
        }

        $driver->update($validated);
        return Reply::success(__('messages.updateSuccess'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $deletePermission = user()->permission('delete_drivers');
        abort_403(!($deletePermission == 'all'));

        $this->driver = Driver::withoutGlobalScopes()->findOrFail($id);

        Driver::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
