<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmployeeDetails;
use App\Models\HrEmployeeTransfer;
use App\Models\HrOffboardingCase;
use App\Models\HrOnboardingCase;
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
        $this->onboarding = HrOnboardingCase::where('employee_id', $employeeId)->latest()->first();
        $this->offboarding = HrOffboardingCase::where('employee_id', $employeeId)->latest()->first();
        $this->onboardingTasks = $this->onboarding ? DB::table('hr_onboarding_tasks')->where('case_id', $this->onboarding->id)->orderBy('id')->get() : collect();
        $this->offboardingTasks = $this->offboarding ? DB::table('hr_offboarding_tasks')->where('case_id', $this->offboarding->id)->orderBy('id')->get() : collect();
        $this->transfers = HrEmployeeTransfer::where('employee_id', $employeeId)->latest()->get();
        $this->branches = \App\Models\Branch::orderBy('name')->get();
        $this->employees = User::allEmployees();
        return view('hr-lifecycle.show', $this->data);
    }

    public function startOnboarding(Request $request, $employeeId)
    {
        $employee = $this->employee($employeeId); $this->authorizeEmployee($employee);
        $case = HrOnboardingCase::create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'template_name' => $employee->employeeDetail?->employee_type ?? 'expat', 'status' => 'open', 'due_date' => now()->addDays(14), 'initiated_by' => user()->id]);
        $tasks = ['Verify employee profile and documents', 'Set up bank and payroll', 'Assign insurance', 'Assign required assets', 'Grant DMS/DOBS access', 'Manager confirmation'];
        foreach ($tasks as $title) DB::table('hr_onboarding_tasks')->insert(['case_id' => $case->id, 'title' => $title, 'owner_type' => 'hr', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        return Reply::successWithData('Onboarding checklist started.', ['redirectUrl' => route('hr-lifecycle.show', $employeeId)]);
    }

    public function startOffboarding(Request $request, $employeeId)
    {
        $employee = $this->employee($employeeId); $this->authorizeEmployee($employee);
        $data = $request->validate(['reason' => 'required|string|max:255', 'last_working_date' => 'required|date']);
        $case = HrOffboardingCase::create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'reason' => $data['reason'], 'last_working_date' => $data['last_working_date'], 'status' => 'open', 'initiated_by' => user()->id]);
        foreach (['Return and verify assets', 'Calculate leave and advance settlement', 'Complete final payroll', 'Revoke DMS/DOBS access', 'Archive employee documents'] as $title) DB::table('hr_offboarding_tasks')->insert(['case_id' => $case->id, 'title' => $title, 'owner_type' => 'hr', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        return Reply::successWithData('Offboarding clearance started.', ['redirectUrl' => route('hr-lifecycle.show', $employeeId)]);
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
        return Reply::success('Task updated.');
    }

    public function addTask(Request $request, string $type, int $caseId)
    {
        abort_403(!in_array($type, ['onboarding', 'offboarding'], true));
        $caseTable = 'hr_' . $type . '_cases'; $taskTable = 'hr_' . $type . '_tasks';
        $case = DB::table($caseTable)->where('id', $caseId)->first(); abort_if(!$case, 404);
        $this->authorizeEmployee($this->employee($case->employee_id));
        $data = $request->validate(['title' => 'required|string|max:255', 'assigned_to' => 'nullable|exists:users,id', 'due_date' => 'nullable|date']);
        DB::table($taskTable)->insert(['case_id' => $caseId, 'title' => $data['title'], 'owner_type' => 'hr', 'assigned_to' => $data['assigned_to'] ?? null, 'due_date' => $data['due_date'] ?? null, 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        DB::table($caseTable)->where('id', $caseId)->update(['status' => 'open', 'completed_at' => null, 'updated_at' => now()]);
        return Reply::success('Task added.');
    }

    public function requestTransfer(Request $request, $employeeId)
    {
        $employee = $this->employee($employeeId); $this->authorizeEmployee($employee);
        $data = $request->validate(['to_branch_id' => 'required|exists:branches,id', 'effective_date' => 'required|date', 'reason' => 'nullable|string']);
        HrEmployeeTransfer::create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'from_branch_id' => $employee->branch_id, 'to_branch_id' => $data['to_branch_id'], 'from_department_id' => $employee->employeeDetail?->department_id, 'from_manager_id' => $employee->employeeDetail?->reporting_to, 'effective_date' => $data['effective_date'], 'reason' => $data['reason'] ?? null, 'requested_by' => user()->id]);
        return Reply::successWithData('Transfer request created.', ['redirectUrl' => route('hr-lifecycle.show', $employeeId)]);
    }

    public function approveTransfer($id)
    {
        abort_403(!in_array('admin', user_roles()));
        $transfer = HrEmployeeTransfer::findOrFail($id); $transfer->update(['status' => 'approved', 'approved_by' => user()->id]);
        return Reply::success('Transfer approved.');
    }

    public function applyTransfer($id)
    {
        abort_403(!in_array('admin', user_roles()));
        $transfer = HrEmployeeTransfer::findOrFail($id); abort_403($transfer->status !== 'approved' || Carbon::parse($transfer->effective_date)->isFuture());
        DB::transaction(function () use ($transfer) { User::whereKey($transfer->employee_id)->update(['branch_id' => $transfer->to_branch_id]); EmployeeDetails::where('user_id', $transfer->employee_id)->update(['department_id' => $transfer->to_department_id, 'reporting_to' => $transfer->to_manager_id]); $transfer->update(['status' => 'applied', 'applied_at' => now()]); });
        return Reply::success('Transfer applied.');
    }

    private function employee($id): User { return User::withoutGlobalScope(ActiveScope::class)->with('employeeDetail')->findOrFail($id); }
    private function authorizeEmployee(User $employee): void { $permission = user()->permission('edit_employees'); abort_403(!($permission === 'all' || ($permission === 'branch' && user()->branch_id === $employee->branch_id))); }
    private function syncCaseCompletion(string $type, int $caseId): void { $taskTable = 'hr_' . $type . '_tasks'; $caseTable = 'hr_' . $type . '_cases'; $openTasks = DB::table($taskTable)->where('case_id', $caseId)->where('status', '!=', 'completed')->exists(); if (!$openTasks) DB::table($caseTable)->where('id', $caseId)->update(['status' => 'completed', 'completed_at' => now(), 'updated_at' => now()]); }
}
