<?php

namespace App\Http\Controllers;

use App\DataTables\AdvanceSalaryDataTable;
use App\Helper\Reply;
use App\Http\Requests\AdvanceSalary\StoreAdvanceSalary;
use App\Http\Requests\AdvanceSalary\UpdateAdvanceSalary;
use App\Models\AdvanceSalary;
use App\Models\User;
use App\Notifications\AdvanceSalaryStatusUpdate;
use App\Notifications\NewAdvanceSalaryRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

use App\Mail\AdvanceSalaryRequest;
use Illuminate\Support\Facades\Mail;

class AdvanceSalaryController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.advanceSalaries');
    }

    public function index(AdvanceSalaryDataTable $dataTable)
    {
        $viewPermission = user()->permission('view_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        if(in_array($viewPermission, ['all','branch']) ){
            if($viewPermission == 'branch' && hr_has_all_branch_access('advance_salaries')){
                $employeePermission = 'all';
            } else{
                $employeePermission = $viewPermission;
            }
        } else{
            $employeePermission = null;
        }
        $this->employees = User::allEmployees(null, true, $employeePermission);
        return $dataTable->render('advance-salaries.index', $this->data);
    }

    public function create()
    {
        $this->addPermission = user()->permission('add_advance_salary');

        abort_403(!in_array($this->addPermission, ['all', 'added', 'branch']));

        $this->assignRole = user()->roles->pluck('name')->toArray();

        $eligibleEmployees = User::with(['employeeDetails', 'advanceSalary'])->has('employeeDetail');

        if($this->addPermission == 'added'){
            $eligibleEmployees = $eligibleEmployees->where('id', user()->id);
        }

        if($this->addPermission == 'branch' && !hr_has_all_branch_access('advance_salaries')){
            $eligibleEmployees = $eligibleEmployees->where('branch_id', user()->branch_id);
        }

        $this->employees = $eligibleEmployees->get();

        if (request()->ajax()) {
            $html = view('advance-salaries.ajax.create', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'advance-salaries.ajax.create';
        return view('advance-salaries.create', $this->data);
    }


    public function store(StoreAdvanceSalary $request)
    {
        $viewPermission = user()->permission('add_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'branch']));

        $salary = new AdvanceSalary();
        $salary->employee_id = $request->employee;
        $salary->date = $request->date;
        $salary->advance_salary = $request->advance_salary;
        $salary->status = $request->status ?? 'pending';
        $salary->added_by = user()->id;
        $salary->save();

        $this->assignRole = user()->roles->pluck('name')->toArray();

        if (!in_array('admin', $this->assignRole)) {

            $adminUsers = User::allAdmins(user()->company->id);

            foreach ($adminUsers as $admin) {
                Mail::to($admin->email)->send(new AdvanceSalaryRequest($salary));
            }
        } else{
            if($salary->status !== 'pending'){
                Mail::to($salary->employee->email)->send(new \App\Mail\AdvanceSalaryStatusUpdate($salary));
            }
        }

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('advance-salaries.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $viewPermission = user()->permission('view_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $this->advanceSalary = AdvanceSalary::with(['employee'])->findOrFail($id);

        if (!$this->canManageRecord($this->advanceSalary, $viewPermission)) {
            abort(403);
        }


        if (request()->ajax()) {
            $html = view('advance-salaries.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'advance-salaries.ajax.show';
        return view('advance-salaries.create', $this->data);
    }

    public function edit(string $id)
    {
        $this->editPermission = user()->permission('edit_advance_salary');

        abort_403(!in_array($this->editPermission, ['all', 'added', 'owned', 'both','branch']));

        $this->assignRole = user()->roles->pluck('name')->toArray();
        $this->advanceSalary = AdvanceSalary::findOrFail($id);

        if (!$this->canManageRecord($this->advanceSalary, $this->editPermission)) {
            abort(403);
        }

        $eligibleEmployees = User::with(['employeeDetails', 'advanceSalary'])->has('employeeDetail');


        if($this->editPermission == 'branch' && !hr_has_all_branch_access('advance_salaries')){
            $eligibleEmployees = $eligibleEmployees->where('branch_id', user()->branch_id);
        }

        $this->employees = $eligibleEmployees->get();

        if (request()->ajax()) {
            $html = view('advance-salaries.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'advance-salaries.ajax.edit';
        return view('advance-salaries.create', $this->data);
    }

    public function update(UpdateAdvanceSalary $request, $id)
    {
        $viewPermission = user()->permission('edit_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $salary = AdvanceSalary::findOrFail($id);
        if (!$this->canManageRecord($salary, $viewPermission)) {
            abort(403);
        }

        $salary->employee_id = $request->employee;
        $salary->date = $request->date;
        $salary->advance_salary = $request->advance_salary;
        $salary->status = $request->status ?? $salary->status;
        $salary->save();

        $redirectUrl = route('advance-salaries.index');
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function destroy($id)
    {
        $viewPermission = user()->permission('delete_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $salary = AdvanceSalary::findOrFail($id);
        if (!$this->canManageRecord($salary, $viewPermission)) {
            abort(403);
        }
        $salary->delete();

        $redirectUrl = route('advance-salaries.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        $viewPermission = user()->permission('delete_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $ids = explode(',', $request->row_ids);

        if ($request->action_type === 'delete') {
            $salaries = AdvanceSalary::whereIn('id', $ids)->get();
            foreach ($salaries as $salary) {
                if ($this->canManageRecord($salary, $viewPermission)) {
                    $salary->delete();
                }
            }
            return response()->json([
                'status' => 'success',
                'message' => 'Records deleted successfully.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid action.']);
    }

    public function approveSalary(Request $request)
    {
        $viewPermission = user()->permission('approve_or_reject_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $this->salaryID = $request->ticket_id;
        $this->salaryAction = $request->ticket_action;
        return view('advance-salaries.approve.index', $this->data);
    }

    public function rejectSalary(Request $request)
    {
        $viewPermission = user()->permission('approve_or_reject_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $this->salaryID = $request->ticket_id;
        $this->salaryAction = $request->ticket_action;
        return view('advance-salaries.reject.index', $this->data);
    }

    public function salaryAction(Request $request)
    {
        $viewPermission = user()->permission('approve_or_reject_advance_salary');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $salary = AdvanceSalary::with('employee')->findOrFail($request->salaryId);

        if (!$this->canManageRecord($salary, $viewPermission)) {
            abort(403);
        }

        $salary->status = $request->action;

        if ($request->action == 'approved') {
            $salary->approve_reason = $request->approveReason;
        } else {
            $salary->reject_reason = $request->reason;
        }

        $salary->approved_by = user()->id;
        $salary->save();

        // Send email to the employee
        // if ($salary->employee && $salary->employee->email) {
        //     Mail::to($salary->employee->email)->send(new \App\Mail\AdvanceSalaryStatusUpdate($salary));
        // }

        return Reply::success(__('messages.updateSuccess'));
    }

    protected function canManageRecord(AdvanceSalary $salary, $permission): bool
    {
        if ($permission === 'all' || ($permission === 'branch' && hr_has_all_branch_access('advance_salaries'))) {
            return true;
        }

        if ($permission === 'added' && $salary->added_by == user()->id) {
            return true;
        }

        if ($permission === 'owned' && $salary->employee_id == user()->id) {
            return true;
        }
        if ($permission == 'both' && (user()->id == $salary->added_by || user()->id == $salary->employee_id)){
            return true;
        }

        if ($permission === 'branch' && $salary->employee->branch_id == user()->branch_id) {
            return true;
        }

        return false;

    }
}
