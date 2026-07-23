<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmployeeDetails;
use App\Models\HrEmployeeTransfer;
use App\Models\HrOffboardingCase;
use App\Models\HrOnboardingCase;
use App\Models\Team;
use App\Models\User;
use App\Scopes\ActiveScope;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrLifecycleController extends AccountBaseController
{
    public function index(Request $request)
    {
        $permission = user()->permission('edit_employees');
        abort_403(!in_array($permission, ['all', 'branch'], true));
        $this->status = $request->input('status', 'open');
        $this->onboardingCases = HrOnboardingCase::with('employee')->where('status', $this->status)->latest()->get();
        $this->offboardingCases = HrOffboardingCase::with('employee')->where('status', $this->status)->latest()->get();
        $this->transfers = HrEmployeeTransfer::with('employee')->whereIn('status', $this->status === 'open' ? ['pending', 'approved'] : [$this->status])->latest()->get();
        if ($permission === 'branch') {
            $filter = fn ($cases) => $cases->filter(fn ($case) => $case->employee?->branch_id === user()->branch_id);
            $this->onboardingCases = $filter($this->onboardingCases); $this->offboardingCases = $filter($this->offboardingCases); $this->transfers = $filter($this->transfers);
        }
        return view('hr-lifecycle.index', $this->data);
    }
    public function show($employeeId)
    {
        $employee = $this->employee($employeeId);
        $this->authorizeEmployee($employee);
        $this->employee = $employee;
        $this->onboarding = HrOnboardingCase::where('employee_id', $employeeId)->where('status', 'open')->latest()->first();
        $this->offboarding = HrOffboardingCase::where('employee_id', $employeeId)->where('status', 'open')->latest()->first();
        $this->onboardingTasks = $this->onboarding ? DB::table('hr_onboarding_tasks')->where('case_id', $this->onboarding->id)->orderBy('id')->get() : collect();
        $this->offboardingTasks = $this->offboarding ? DB::table('hr_offboarding_tasks')->where('case_id', $this->offboarding->id)->orderBy('id')->get() : collect();
        $this->transfers = HrEmployeeTransfer::where('employee_id', $employeeId)->latest()->get();
        $this->branches = \App\Models\Branch::orderBy('name')->get();
        $this->departments = Team::where('company_id', $employee->company_id)->orderBy('team_name')->get();
        $this->employees = User::allEmployees(null, false, 'all', $employee->company_id);
        return view('hr-lifecycle.show', $this->data);
    }

    public function startOnboarding(Request $request, $employeeId)
    {
        $employee = $this->employee($employeeId); $this->authorizeEmployee($employee);
        if (HrOnboardingCase::where('employee_id', $employee->id)->where('status', 'open')->exists()) {
            return $this->workflowResponse($request, 'An onboarding checklist is already open for this employee.', $employeeId);
        }
        $case = HrOnboardingCase::create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'template_name' => $employee->employeeDetail?->employee_type ?? 'expat', 'status' => 'open', 'due_date' => now()->addDays(14), 'initiated_by' => user()->id]);
        $tasks = ['Verify employee profile and documents', 'Set up bank and payroll', 'Assign insurance', 'Assign required assets', 'Grant DMS/DOBS access', 'Manager confirmation'];
        foreach ($tasks as $title) DB::table('hr_onboarding_tasks')->insert(['case_id' => $case->id, 'title' => $title, 'owner_type' => 'hr', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        return $this->workflowResponse($request, 'Onboarding checklist started.', $employeeId);
    }

    public function startOffboarding(Request $request, $employeeId)
    {
        $employee = $this->employee($employeeId); $this->authorizeEmployee($employee);
        $data = $request->validate(['reason' => 'required|string|max:255', 'last_working_date' => 'required|date']);
        if (HrOffboardingCase::where('employee_id', $employee->id)->where('status', 'open')->exists()) {
            return $this->workflowResponse($request, 'An offboarding clearance is already open for this employee.', $employeeId);
        }
        $case = HrOffboardingCase::create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'reason' => $data['reason'], 'last_working_date' => $data['last_working_date'], 'status' => 'open', 'initiated_by' => user()->id]);
        foreach (['Return and verify assets', 'Calculate leave and advance settlement', 'Complete final payroll', 'Revoke DMS/DOBS access', 'Archive employee documents'] as $title) DB::table('hr_offboarding_tasks')->insert(['case_id' => $case->id, 'title' => $title, 'owner_type' => 'hr', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        return $this->workflowResponse($request, 'Offboarding clearance started.', $employeeId);
    }

    public function updateTask(Request $request, string $type, int $taskId)
    {
        abort_403(!in_array($type, ['onboarding', 'offboarding'], true));
        $table = 'hr_' . $type . '_tasks';
        $caseTable = 'hr_' . $type . '_cases';
        $task = DB::table($table)->join($caseTable, $caseTable . '.id', '=', $table . '.case_id')->select($table . '.*', $caseTable . '.employee_id')->where($table . '.id', $taskId)->first(); abort_if(!$task, 404);
        $this->authorizeEmployee($this->employee($task->employee_id));
        DB::table($table)->where('id', $taskId)->update(['status' => $request->boolean('complete') ? 'completed' : 'pending', 'completed_at' => $request->boolean('complete') ? now() : null, 'updated_at' => now()]);
        $this->syncCaseCompletion($type, $task->case_id);
        return $this->workflowResponse($request, 'Task updated.', $task->employee_id);
    }

    public function addTask(Request $request, string $type, int $caseId)
    {
        abort_403(!in_array($type, ['onboarding', 'offboarding'], true));
        $caseTable = 'hr_' . $type . '_cases'; $taskTable = 'hr_' . $type . '_tasks';
        $case = DB::table($caseTable)->where('id', $caseId)->first(); abort_if(!$case, 404);
        $this->authorizeEmployee($this->employee($case->employee_id));
        $data = $request->validate(['title' => 'required|string|max:255', 'assigned_to' => 'nullable|exists:users,id', 'due_date' => 'nullable|date']);
        if (!empty($data['assigned_to']) && !User::whereKey($data['assigned_to'])->where('company_id', $case->company_id)->exists()) {
            abort(422, 'The task assignee must belong to the same company.');
        }
        DB::table($taskTable)->insert(['case_id' => $caseId, 'title' => $data['title'], 'owner_type' => 'hr', 'assigned_to' => $data['assigned_to'] ?? null, 'due_date' => $data['due_date'] ?? null, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        DB::table($caseTable)->where('id', $caseId)->update(['status' => 'open', 'completed_at' => null, 'updated_at' => now()]);
        return $this->workflowResponse($request, 'Task added.', $case->employee_id);
    }

    public function requestTransfer(Request $request, $employeeId)
    {
        $employee = $this->employee($employeeId); $this->authorizeEmployee($employee);
        $data = $request->validate(['to_branch_id' => 'required|exists:branches,id', 'to_department_id' => 'nullable|exists:teams,id', 'to_manager_id' => 'nullable|exists:users,id', 'effective_date' => 'required|date', 'reason' => 'nullable|string']);
        if (!empty($data['to_department_id']) && !Team::whereKey($data['to_department_id'])->where('company_id', $employee->company_id)->exists()) abort(422, 'The selected department is not available.');
        if (!empty($data['to_manager_id']) && !User::whereKey($data['to_manager_id'])->where('company_id', $employee->company_id)->exists()) abort(422, 'The selected manager is not available.');
        HrEmployeeTransfer::create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'from_branch_id' => $employee->branch_id, 'to_branch_id' => $data['to_branch_id'], 'from_department_id' => $employee->employeeDetail?->department_id, 'to_department_id' => $data['to_department_id'] ?? $employee->employeeDetail?->department_id, 'from_manager_id' => $employee->employeeDetail?->reporting_to, 'to_manager_id' => $data['to_manager_id'] ?? $employee->employeeDetail?->reporting_to, 'effective_date' => $data['effective_date'], 'reason' => $data['reason'] ?? null, 'requested_by' => user()->id]);
        return $this->workflowResponse($request, 'Transfer request created.', $employeeId);
    }

    public function approveTransfer(Request $request, $id)
    {
        abort_403(!in_array('admin', user_roles()));
        $transfer = HrEmployeeTransfer::findOrFail($id); $transfer->update(['status' => 'approved', 'approved_by' => user()->id]);
        return $this->workflowResponse($request, 'Transfer approved.', $transfer->employee_id);
    }

    public function applyTransfer(Request $request, $id)
    {
        abort_403(!in_array('admin', user_roles()));
        $transfer = HrEmployeeTransfer::findOrFail($id); abort_403($transfer->status !== 'approved' || Carbon::parse($transfer->effective_date)->isFuture());
        DB::transaction(function () use ($transfer) { User::whereKey($transfer->employee_id)->update(['branch_id' => $transfer->to_branch_id]); EmployeeDetails::where('user_id', $transfer->employee_id)->update(['department_id' => $transfer->to_department_id ?? $transfer->from_department_id, 'reporting_to' => $transfer->to_manager_id ?? $transfer->from_manager_id]); $transfer->update(['status' => 'applied', 'applied_at' => now()]); });
        return $this->workflowResponse($request, 'Transfer applied.', $transfer->employee_id);
    }

    private function employee($id): User { return User::withoutGlobalScope(ActiveScope::class)->with('employeeDetail')->findOrFail($id); }
    private function authorizeEmployee(User $employee): void { $permission = user()->permission('edit_employees'); abort_403(!($permission === 'all' || ($permission === 'branch' && user()->branch_id === $employee->branch_id))); }
    private function syncCaseCompletion(string $type, int $caseId): void { $taskTable = 'hr_' . $type . '_tasks'; $caseTable = 'hr_' . $type . '_cases'; $openTasks = DB::table($taskTable)->where('case_id', $caseId)->where('status', '!=', 'completed')->exists(); if (!$openTasks) DB::table($caseTable)->where('id', $caseId)->update(['status' => 'completed', 'completed_at' => now(), 'updated_at' => now()]); }
    private function workflowResponse(Request $request, string $message, int $employeeId) { return $request->ajax() ? Reply::successWithData($message, ['redirectUrl' => route('hr-lifecycle.show', $employeeId)]) : redirect()->route('hr-lifecycle.show', $employeeId)->with('success', $message); }
}
