<?php

namespace App\Http\Controllers;

use App\DataTables\BusinessesDriverDataTable;
use App\Enums\Salutation;
use App\Helper\Reply;
use App\Http\Requests\Admin\BusinessDriver\StoreRequest;
use App\Http\Requests\Admin\BusinessDriver\UpdateRequest;
use App\Models\Driver;
use App\Models\Business;
use App\Models\BusinessDriver;
use App\Models\LanguageSetting;
use App\Models\Role;
use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use App\Helper\Files;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BusinessDriverController extends AccountBaseController
{
    use ImportExcel;

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.drivers';
        $this->middleware(function ($request, $next) {
            // abort_403(!in_array('driv', $this->user->modules));

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Driver $driver)
    {
        $model = BusinessDriver::where('driver_id', $driver->id);
        return (new BusinessesDriverDataTable($model))->render('drivers.ajax.businesses', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Driver $driver)
    {
        $this->pageTitle = __('app.addBusiness');

        // $addPermission = user()->permission('add_employees');
        // abort_403(!in_array($addPermission, ['all', 'added']));


        $this->driver = $driver;
        $this->teams = []; // Team::all();
        $this->designations = []; // Designation::allDesignations();

        $this->skills = []; // Skill::all()->pluck('name')->toArray();
        $this->countries = countries();
        $this->lastEmployeeID = 0; // EmployeeDetails::count();
        $this->checkifExistEmployeeId = []; // EmployeeDetails::select('id')->where('employee_id', ($this->lastEmployeeID + 1))->first();
        $this->employees = []; // User::allEmployees(null, true);
        $this->languages = LanguageSetting::where('status', 'enabled')->get();
        $this->salutations = Salutation::cases();

        $userRoles = user()->roles->pluck('name')->toArray();

        if(in_array('admin', $userRoles))
        {
            $this->roles = Role::where('name', '<>', 'client')->get();
        }
        else
        {
            $this->roles = Role::whereNotIn('name', ['admin', 'client'])->get();
        }

        $this->view = 'drivers.business.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('drivers.business.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request, Driver $driver)
    {
        // $addPermission = user()->permission('add_employees');
        // abort_403(!in_array($addPermission, ['all', 'added']));

        // WORKSUITESAAS
        $company = company();

        if ($driver->businesses()->where('business_id', $request->business_id)->exists()) {
            return Reply::error(__('messages.businessExists'));
        }

        DB::beginTransaction();
        try {
            $validated = $request->validated();
            $driver->businesses()->attach($validated['business_id'], [
                'platform_id' => $validated['platform_id']
            ]);
            // Commit Transaction
            DB::commit();

            // WORKSUITESAAS
            session()->forget('company');

        } catch (\Exception $e) {
            logger($e->getMessage());
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support '. $e->getMessage());
        }


        if (request()->add_more == 'true') {
            $html = $this->create($driver);

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('drivers.show', $driver->id) . '?tab=businesses']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Driver $driver, Business $business)
    {
        $this->pageTitle = _('app.update');
        $this->driver = $driver;
        $this->business = $driver->businesses()->where('business_id', $business->id)->first();
        $this->view = 'drivers.business.ajax.edit';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }


        return view('drivers.business.create', $this->data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, Driver $driver, Business $business)
    {
        $validated = $request->validated();

        $driver->businesses()->updateExistingPivot($business->id, $validated);
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('drivers.show', $driver->id) . '?tab=businesses']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Driver $driver, Business $business)
    {
        $this->discussion = $driver->businesses()->detach($business->id);

        return Reply::success(__('messages.deleteSuccess'));
    }
}
