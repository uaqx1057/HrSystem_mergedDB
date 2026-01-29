<?php

namespace App\Http\Controllers;

use App\DataTables\BusinessesDriverDataTable;
use App\DataTables\DriversDataTable;
use App\Helper\Reply;
use App\Http\Requests\Admin\Driver\StoreRequest;
use App\Models\{Driver, DriverType, User};
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use App\Http\Requests\Admin\Driver\UpdateRequest;
use Carbon\Carbon;
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

        $drivers = Driver::orderby('iqaama_number')
            ->select('id', 'iqaama_number')
            ->when($search, function ($query) use ($search) {
                $query->where('iqaama_number', 'like', '%' . $search . '%');
            })
            ->take(20)
            ->get();

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

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $this->viewPermission = user()->permission('view_drivers');
        abort_403(!($this->viewPermission == 'all'));

        $this->driver = Driver::findOrFail($id);

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
    public function edit(Driver $driver)
    {
        $this->editPermission = user()->permission('edit_drivers');
        abort_403(!($this->editPermission == 'all'));

        $this->pageTitle = __('app.update');

        $this->driver = $driver;
        $this->driver_types = DriverType::all();

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
    public function update(UpdateRequest $request, Driver $driver)
    {
        $this->editPermission = user()->permission('edit_drivers');
        abort_403(!($this->editPermission == 'all'));

        $validated = $request->validated();

        $validated['insurance_expiry_date'] = $request->insurance_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->insurance_expiry_date)->format('Y-m-d') : null;
        $validated['license_expiry_date'] = $request->license_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->license_expiry_date)->format('Y-m-d') : null;
        $validated['iqaama_expiry_date'] = $request->iqaama_expiry_date ? Carbon::createFromFormat($this->company->date_format, $request->iqaama_expiry_date)->format('Y-m-d') : null;
        $validated['date_of_birth'] = $request->date_of_birth ? Carbon::createFromFormat($this->company->date_format, $request->date_of_birth)->format('Y-m-d') : null;
        $validated['joining_date'] = $request->joining_date ? Carbon::createFromFormat($this->company->date_format, $request->joining_date)->format('Y-m-d') : null;

        if ($request->iqama_delete == 'yes') {
            Files::deleteFile($driver->iqama, 'iqama');
            $driver->iqama = null;
            $validated['iqaama_expiry_date'] = null;
        }

        if ($request->hasFile('iqama'))
            $validated['iqama'] = Files::uploadLocalOrS3($request->iqama, 'iqama', 300);

        if ($request->hasFile('image'))
            $validated['image'] = Files::uploadLocalOrS3($request->image, 'image', 300);

        if ($request->license_delete == 'yes') {
            Files::deleteFile($driver->license, 'license');
            $driver->license = null;
            $validated['license_expiry_date'] = null;
        }

        if ($request->hasFile('license'))
            $validated['license'] = Files::uploadLocalOrS3($request->license, 'license', 300);

        if ($request->mobile_form_delete == 'yes') {
            Files::deleteFile($driver->mobile_form, 'mobile_form');
            $driver->mobile_form = null;
        }

        if ($request->hasFile('mobile_form'))
            $validated['mobile_form'] = Files::uploadLocalOrS3($request->mobile_form, 'mobile_form', 300);

        if ($request->sim_form_delete == 'yes') {
            Files::deleteFile($driver->sim_form, 'sim_form');
            $driver->sim_form = null;
        }

        if ($request->hasFile('sim_form'))
            $validated['sim_form'] = Files::uploadLocalOrS3($request->sim_form, 'sim_form', 300);

        if ($request->medical_delete == 'yes') {
            Files::deleteFile($driver->medical, 'medical');
            $driver->medical = null;
        }

        if ($request->hasFile('medical'))
            $validated['medical'] = Files::uploadLocalOrS3($request->medical, 'medical', 300);

        if ($request->other_document_delete == 'yes') {
            Files::deleteFile($driver->other_document, 'other_document');
            $driver->other_document = null;
        }

        if ($request->hasFile('other_document'))
            $validated['other_document'] = Files::uploadLocalOrS3($request->other_document, 'other_document', 300);

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

        $this->driver = Driver::findOrFail($id);

        Driver::destroy($id);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
