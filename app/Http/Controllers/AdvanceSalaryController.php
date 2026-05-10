<?php

namespace App\Http\Controllers;

use App\DataTables\AdvanceSalaryDataTable;
use App\Helper\Reply;
use App\Http\Requests\AdvanceSalary\StoreAdvanceSalary;
use App\Http\Requests\AdvanceSalary\UpdateAdvanceSalary;
use App\Models\AdvanceSalary;
use App\Models\User;
use Illuminate\Http\Request;

class AdvanceSalaryController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = __('app.menu.advanceSalaries');
    }

    public function index(AdvanceSalaryDataTable $dataTable)
    {
        $this->assignRole = user()->roles->pluck('name')->toArray();
        $this->employees = User::allEmployees();
        return $dataTable->render('advance-salaries.index', $this->data);
    }

    public function create()
    {
        $this->assignRole = user()->roles->pluck('name')->toArray();

        $today = now();

        $eligibleEmployees = User::with(['employeeDetails', 'advanceSalary']);

        if (!in_array('admin', $this->assignRole)) {
            $eligibleEmployees = $eligibleEmployees->where('id', user()->id);
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
        $salary = new AdvanceSalary();
        $salary->employee_id = $request->employee;
        $salary->date = $request->date;
        $salary->advance_salary = $request->advance_salary;
        $salary->status = $request->status ?? 'pending';
        $salary->save();

        $redirectUrl = urldecode($request->redirect_url);
        if ($redirectUrl == '') {
            $redirectUrl = route('advance-salaries.index');
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => $redirectUrl]);
    }

    public function show($id)
    {
        $this->advanceSalary = AdvanceSalary::with(['employee'])->findOrFail($id);
        if (request()->ajax()) {
            $html = view('advance-salaries.ajax.show', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'advance-salaries.ajax.show';
        return view('advance-salaries.create', $this->data);
    }

    public function edit(string $id)
    {
        $this->assignRole = user()->roles->pluck('name')->toArray();
        $this->advanceSalary = AdvanceSalary::findOrFail($id);
        if(!in_array('admin', $this->assignRole)){
            if($this->advanceSalary->status !== 'pending'){
                abort_403(true);
            }
        }
        $today = now();
        $currentEmployeeId = $this->advanceSalary->employee_id;

        $this->employees = User::with(['employeeDetails', 'advanceSalary'])
            ->get();

        if (request()->ajax()) {
            $html = view('advance-salaries.ajax.edit', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'advance-salaries.ajax.edit';
        return view('advance-salaries.create', $this->data);
    }

    public function update(UpdateAdvanceSalary $request, $id)
    {
        $editDepartment = user()->permission('edit_employees');
        abort_403($editDepartment != 'all');

        $salary = AdvanceSalary::findOrFail($id);
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
        $deletePermission = user()->permission('delete_employees');
        abort_403($deletePermission != 'all');

        AdvanceSalary::findOrFail($id)->delete();
        $redirectUrl = route('advance-salaries.index');
        return Reply::successWithData(__('messages.deleteSuccess'), ['redirectUrl' => $redirectUrl]);
    }

    public function applyQuickAction(Request $request)
    {
        $ids = explode(',', $request->row_ids);

        if ($request->action_type === 'delete') {
            AdvanceSalary::whereIn('id', $ids)->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Records deleted successfully.'
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'Invalid action.']);
    }

    public function approveSalary(Request $request)
    {
        $this->salaryID = $request->ticket_id;
        $this->salaryAction = $request->ticket_action;
        return view('advance-salaries.approve.index', $this->data);
    }

    public function rejectSalary(Request $request)
    {
        $this->salaryID = $request->ticket_id;
        $this->salaryAction = $request->ticket_action;
        return view('advance-salaries.reject.index', $this->data);
    }

    public function salaryAction(Request $request)
    {
        $salary = AdvanceSalary::findOrFail($request->salaryId);
        $salary->status = $request->action;

        if ($request->action == 'approved') {
            $salary->approve_reason = $request->approveReason;
        } else {
            $salary->reject_reason = $request->reason;
        }

        $salary->approved_by = user()->id;
        $salary->save();

        return Reply::success(__('messages.updateSuccess'));
    }
}
