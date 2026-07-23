<?php

namespace App\Http\Controllers;

use App\DataTables\InsuranceDataTable;
use App\Helper\Reply;
use App\Http\Requests\Insurance\StoreInsurance;
use App\Http\Requests\Insurance\UpdateInsurance;
use App\Models\Driver;
use App\Models\Insurance;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class InsuranceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.insurance');

        // $this->middleware(function ($request, $next) {
        // abort_403(!in_array('employees', $this->user->modules));

        //     return $next($request);
        // });
    }
    /**
     * @param InsuranceDataTable $dataTable
     * @return mixed|void
     */


    public function index(InsuranceDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_insurance');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $this->assignRole = user()->roles->pluck('name')->toArray();

        if(in_array($viewPermission, ['all','branch']) ){
            if($viewPermission == 'branch' && user()->branch_id == 6){
                $employeePermission = 'all';
            } else{
                $employeePermission = $viewPermission;
            }
        } else{
            $employeePermission = null;
        }
        $this->employees = User::allEmployees(null, true, $employeePermission);
        return $dataTable->render('insurances.index', $this->data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->addPermission = user()->permission('add_insurance');

        abort_403(!in_array($this->addPermission, ['all', 'added', 'branch']));

        $existEmployeeInsurance = Insurance::whereNotNull('employee_id')
            ->where('status', 'active')
            ->whereDate('expiry_date', '>', today())
            ->pluck('employee_id')
            ->unique()
            ->toArray();

        $existDriverInsurance = Insurance::whereNotNull('driver_id')
            ->where('status', 'active')
            ->whereDate('expiry_date', '>', today())
            ->pluck('driver_id')
            ->unique()
            ->toArray();

        if(in_array($this->addPermission, ['all','branch']) ){
            if($this->addPermission == 'branch' && user()->branch_id == 6){
                $employeePermission = 'all';
            } else{
                $employeePermission = $this->addPermission;
            }
        } else{
            $employeePermission = null;
        }
        $this->employees = User::allEmployees($existEmployeeInsurance, true, $employeePermission);

        $this->drivers = Driver::withoutGlobalScopes()->select(['id', 'name'])->whereNotIn('id', $existDriverInsurance)->orderBy('id', 'desc')->get();

        if (request()->ajax()) {
            $html = view('insurances.ajax.create', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'insurances.ajax.create';

        return view('insurances.create', $this->data);
    }

    /**
     * @param StoreInsurance $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreInsurance $request)
    {
        $this->addPermission = user()->permission('add_insurance');

        abort_403(!in_array($this->addPermission, ['all', 'added', 'branch']));

        // dd($request);
        $insurance = new Insurance();
        // if ($request->type == 'employee') {
        //     $insurance->employee_id = $request->employee;
        //     $insurance->driver_id = null;
        // } else {
        //     $insurance->employee_id = null;
        //     $insurance->driver_id = $request->driver;
        // }
        $insurance->employee_id = $request->employee;
        $insurance->issue_date = $request->issue_date;
        $insurance->expiry_date = $request->expiry_date;
        $insurance->company = $request->company_name;
        $insurance->policy_no = $request->policy_no;
        $insurance->class = $request->class;
        $insurance->added_by = user()->id;
        $insurance->status = 'active';
        $insurance->save();

        $redirectUrl = urldecode($request->redirect_url);

        if ($redirectUrl == '') {
            $redirectUrl = route('insurance.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $viewPermission = user()->permission('view_insurance');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $this->insurance = Insurance::with(['driver', 'employee'])->findOrFail($id);

        if (!$this->canManageRecord($this->insurance, $viewPermission)) {
            abort(403);
        }

        if (request()->ajax()) {
            $html = view('insurances.ajax.show', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'insurances.ajax.show';

        return view('insurances.create', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $this->editPermission = user()->permission('edit_insurance');

        abort_403(!in_array($this->editPermission, ['all', 'added', 'owned', 'both','branch']));

        $this->insurance = Insurance::with(['driver', 'employee'])->findOrFail($id);

        if (!$this->canManageRecord($this->insurance, $this->editPermission)) {
            abort(403);
        }


        $existEmployeeInsurance = Insurance::whereNotNull('employee_id')
            ->where('status', 'active')->where('employee_id', '!==', $id)
            ->whereDate('expiry_date', '>', today())
            ->pluck('employee_id')
            ->unique()
            ->toArray();

        $existDriverInsurance = Insurance::whereNotNull('driver_id')
            ->where('status', 'active')->where('driver_id', '!==', $id)
            ->whereDate('expiry_date', '>', today())
            ->pluck('driver_id')
            ->unique()
            ->toArray();

        if(in_array($this->editPermission, ['all','branch']) ){
            if($this->editPermission == 'branch' && user()->branch_id == 6){
                $employeePermission = 'all';
            } else{
                $employeePermission = $this->editPermission;
            }
        } else{
            $employeePermission = null;
        }
        $this->employees = User::allEmployees($existEmployeeInsurance, true, $employeePermission);
        // dd($this->employees);

        $this->drivers = Driver::withoutGlobalScopes()->select(['id', 'name'])->whereNotIn('id', $existDriverInsurance)->orderBy('id', 'desc')->get();

        if (request()->ajax()) {
            $html = view('insurances.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'insurances.ajax.edit';

        return view('insurances.create', $this->data);
    }

    /**
     * @param UpdateInsurance $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function update(UpdateInsurance $request, $id)
    {
        $editPermission = user()->permission('edit_insurance');

        abort_403(!in_array($editPermission, ['all', 'added', 'owned', 'both','branch']));

        $insurance = Insurance::findOrFail($id);

        if (!$this->canManageRecord($insurance, $editPermission)) {
            abort(403);
        }

        if ($request->type == 'employee') {
            $insurance->employee_id = $request->employee;
            $insurance->driver_id = null;
        } else {
            $insurance->employee_id = null;
            $insurance->driver_id = $request->driver;
        }
        $insurance->issue_date = $request->issue_date;
        $insurance->expiry_date = $request->expiry_date;
        $insurance->company = $request->company_name;
        $insurance->policy_no = $request->policy_no;
        $insurance->class = $request->class;
        // $insurance->status = $request->status;
        $insurance->save();

        $redirectUrl = route('insurance.index');

        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $deletePermission = user()->permission('delete_insurance');

        abort_403(!in_array($deletePermission, ['all', 'added', 'owned', 'both','branch']));

        $insurance = Insurance::findOrFail($id);
        if (!$this->canManageRecord($insurance, $deletePermission)) {
            abort(403);
        }

        $insurance->delete();

        $redirectUrl = route('insurance.index');

        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        $deletePermission = user()->permission('delete_insurance');

        abort_403(!in_array($deletePermission, ['all', 'added', 'owned', 'both','branch']));

        $ids = explode(',', $request->row_ids);

        if ($request->action_type === 'delete') {
            $insurances = Insurance::whereIn('id', $ids)->get();
            foreach ($insurances as $insurance) {
                if ($this->canManageRecord($insurance, $deletePermission)) {
                    $insurance->delete();
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Records deleted successfully.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid action.']);
    }

    protected function canManageRecord(Insurance $insurance, $permission): bool
    {
        if ($permission === 'all' || ($permission === 'branch' && user()->branch_id == 6)) {
            return true;
        }

        if ($permission === 'added' && $insurance->added_by == user()->id) {
            return true;
        }

        if ($permission === 'owned' && $insurance->employee_id == user()->id) {
            return true;
        }
        if ($permission == 'both' && (user()->id == $insurance->added_by || user()->id == $insurance->employee_id)){
            return true;
        }

        if ($permission === 'branch' && $insurance->employee->branch_id == user()->branch_id) {
            return true;
        }

        return false;

    }
}
