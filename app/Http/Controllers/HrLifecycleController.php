<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\EmployeeDetails;
use App\Models\HrEmployeeTransfer;
use App\Models\HrOffboardingCase;
use App\Models\HrOffboardingTask;
use App\Models\HrLifecycleEvent;
use App\Models\EmployeeTermination;
use App\Models\HrOnboardingCase;
use App\Models\Team;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Services\OffboardingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrLifecycleController extends AccountBaseController
{
    public function index(Request $request)
    {
        $this->pageTitle = 'HR lifecycle';
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
        $this->offboarding = HrOffboardingCase::where('employee_id', $employeeId)->whereIn('status', ['open', 'completion_pending'])->latest()->first();
        $this->onboardingTasks = $this->onboarding ? DB::table('hr_onboarding_tasks')->where('case_id', $this->onboarding->id)->orderBy('id')->get() : collect();
        $this->offboardingTasks = $this->offboarding ? DB::table('hr_offboarding_tasks')->where('case_id', $this->offboarding->id)->orderBy('id')->get() : collect();
        $this->transfers = HrEmployeeTransfer::where('employee_id', $employeeId)->latest()->get();
        $this->lifecycleEvents = HrLifecycleEvent::with('actor')->where('subject_user_id', $employeeId)->latest('created_at')->get();
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
        $data = $request->validate([
            'reason' => 'required|string|max:255',
            'notice_type' => 'required|in:immediate,notice',
            'notice_months' => 'required_if:notice_type,notice|nullable|in:1,2,3',
        ]);
        $terms = \App\Support\NoticeTerms::resolve($request->input('notice_type'), $request->input('notice_months'), null);
        app(OffboardingService::class)->request($employee, user()->id, EmployeeTermination::EXIT_TERMINATION, ['reason' => $data['reason']] + $terms);
        return $this->workflowResponse($request, 'Termination request submitted for approval.', $employeeId);
    }

    public function startResignation(Request $request, $employeeId)
    {
        $employee = $this->employee($employeeId); $this->authorizeEmployee($employee);
        $data = $request->validate([
            'reason' => 'required|string|max:255',
            'resignation_date' => 'required|date',
            'notice_type' => 'required|in:immediate,notice',
            'notice_months' => 'required_if:notice_type,notice|nullable|in:1,2,3',
        ]);
        $terms = \App\Support\NoticeTerms::resolve($request->input('notice_type'), $request->input('notice_months'), $data['resignation_date']);
        app(OffboardingService::class)->request($employee, user()->id, EmployeeTermination::EXIT_RESIGNATION, [
            'reason' => $data['reason'],
            'resignation_date' => $data['resignation_date'],
        ] + $terms);
        return $this->workflowResponse($request, 'Resignation request submitted for approval.', $employeeId);
    }

    public function updateExitTerms(Request $request, HrOffboardingCase $case)
    {
        $employee = $this->employee($case->employee_id);
        $this->authorizeEmployee($employee);
        abort_403(!in_array($case->status, ['open', 'completion_pending'], true) || $case->approval_status !== 'approved');

        $request->validate([
            'notice_type' => 'required|in:immediate,notice',
            'notice_months' => 'required_if:notice_type,notice|nullable|in:1,2,3',
        ]);

        $base = $case->exit_type === EmployeeTermination::EXIT_RESIGNATION && $case->resignation_date
            ? $case->resignation_date->toDateString()
            : ($case->notice_start_date?->toDateString() ?? now()->toDateString());
        $terms = \App\Support\NoticeTerms::resolve($request->input('notice_type'), $request->input('notice_months'), $base);

        $case->update($terms);
        if ($case->termination) {
            $case->termination->update(['last_working_date' => $terms['last_working_date']]);
        }
        HrLifecycleEvent::create([
            'subject_user_id' => $employee->id,
            'company_id' => $employee->company_id,
            'event' => 'offboarding_exit_terms_updated',
            'actor_id' => user()->id,
            'meta' => $terms,
        ]);

        return $this->workflowResponse($request, 'Exit terms updated. Last working day is now ' . $terms['last_working_date'] . '.', $employee->id);
    }

    private function createOffboardingCase(Request $request, User $employee, array $data, string $exitType)
    {
        if (HrOffboardingCase::where('employee_id', $employee->id)->where('status', 'open')->exists()) {
            return $this->workflowResponse($request, 'An offboarding clearance is already open for this employee.', $employee->id);
        }
        $case = HrOffboardingCase::create([
            'company_id' => $employee->company_id,
            'employee_id' => $employee->id,
            'exit_type' => $exitType,
            'reason' => $data['reason'],
            'resignation_date' => $data['resignation_date'] ?? null,
            'last_working_date' => $data['last_working_date'],
            'status' => 'open',
            'initiated_by' => user()->id,
        ]);
        foreach (['Return and verify assets', 'Calculate leave and advance settlement', 'Complete final payroll', 'Revoke DMS/DOBS access', 'Archive employee documents'] as $title) {
            DB::table('hr_offboarding_tasks')->insert(['case_id' => $case->id, 'title' => $title, 'owner_type' => 'hr', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
        }
        return $this->workflowResponse($request, ucfirst($exitType) . ' offboarding started.', $employee->id);
    }

    public function approveOffboarding(Request $request, HrOffboardingCase $case)
    {
        $employee = $this->employee($case->employee_id);
        $this->authorizeEmployee($employee);
        app(OffboardingService::class)->approve($case, user()->id);

        return $this->workflowResponse($request, 'Offboarding request approved.', $employee->id);
    }

    public function rejectOffboarding(Request $request, HrOffboardingCase $case)
    {
        $employee = $this->employee($case->employee_id);
        $this->authorizeEmployee($employee);
        $data = $request->validate(['reason' => 'required|string|max:1000']);
        app(OffboardingService::class)->reject($case, user()->id, $data['reason']);

        return $this->workflowResponse($request, 'Offboarding request rejected.', $employee->id);
    }

    /** Manager / HR queue of offboarding cases awaiting an approve/reject decision. */
    public function approvalsInbox()
    {
        $this->pageTitle = 'Offboarding approvals';
        $permission = user()->permission('edit_employees');
        abort_403(!in_array($permission, ['all', 'branch'], true));

        $cases = HrOffboardingCase::with(['employee', 'termination'])
            ->where('approval_status', 'awaiting_approval')
            ->whereIn('status', ['open', 'completion_pending'])
            ->latest()
            ->get();

        if ($permission === 'branch') {
            $cases = $cases->filter(fn ($case) => $case->employee?->branch_id === user()->branch_id)->values();
        }

        $this->cases = $cases;

        return view('hr-lifecycle.approvals', $this->data);
    }

    /** HR-0111 master clearance & offboarding document for a case. */
    public function clearancePdf(HrOffboardingCase $case)
    {
        $employee = $this->employee($case->employee_id);
        $this->authorizeEmployee($employee);

        $case->load(['tasks', 'termination', 'employee.employeeDetail.designation', 'employee.employeeDetail.department', 'employee.branch']);
        $settlement = $case->termination
            ? \App\Models\HrSettlementForm::where('employee_termination_id', $case->termination->id)->first()
            : null;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('hr-lifecycle.clearance-pdf', [
            'case' => $case,
            'employee' => $employee,
            'settlement' => $settlement,
            'company' => function_exists('company') ? company() : null,
        ])->setPaper('letter');

        return $pdf->download('HR-clearance-' . ($case->reference ?: $case->id) . '.pdf');
    }

    /** The gated HR Clearance & Offboarding (HR-0111) data-entry screen. */
    public function hrClearanceForm(HrOffboardingCase $case)
    {
        $employee = $this->employee($case->employee_id);
        $this->authorizeEmployee($employee);

        $case->load(['tasks.completedBy', 'termination', 'employee.employeeDetail.designation', 'employee.employeeDetail.department', 'employee.branch']);

        $this->case = $case;
        $this->employee = $employee;
        $this->settlement = $case->termination
            ? \App\Support\Clearance::finalSettlement($case->termination)
            : null;
        $this->settlementDraft = $case->termination
            ? \App\Models\HrSettlementForm::where('employee_termination_id', $case->termination->id)->first()
            : null;
        $this->handover = \App\Support\Clearance::HR_HANDOVER;
        $this->statutory = \App\Support\Clearance::HR_STATUTORY;
        $this->entitlements = \App\Support\Clearance::HR_ENTITLEMENTS;
        $this->decisions = \App\Support\Clearance::HR_DECISIONS;
        $this->hrData = $case->hr_clearance_data ?? [];

        return view('hr-lifecycle.hr-clearance', $this->data);
    }

    public function issueHrClearance(Request $request, HrOffboardingCase $case)
    {
        $employee = $this->employee($case->employee_id);
        $this->authorizeEmployee($employee);
        abort_403($case->approval_status !== 'approved' || !in_array($case->status, ['open', 'completion_pending'], true));

        $decision = (string) $request->input('hr_clearance_decision');

        $payload = [
            'separation' => [
                'contract_type'  => (string) $request->input('separation.contract_type'),
                'nationality'    => (string) $request->input('separation.nationality'),
                'total_service'  => (string) $request->input('separation.total_service'),
            ],
            'handover' => collect(\App\Support\Clearance::HR_HANDOVER)->map(fn ($label, $i) => [
                'label'    => $label,
                'result'   => (string) $request->input("handover.$i.result"),
                'handed_to' => (string) $request->input("handover.$i.handed_to"),
                'remarks'  => (string) $request->input("handover.$i.remarks"),
            ])->all(),
            'statutory' => collect(\App\Support\Clearance::HR_STATUTORY)->map(fn ($label, $i) => [
                'label'     => $label,
                'result'    => (string) $request->input("statutory.$i.result"),
                'reference' => (string) $request->input("statutory.$i.reference"),
                'date'      => (string) $request->input("statutory.$i.date"),
            ])->all(),
            'entitlements' => collect(\App\Support\Clearance::HR_ENTITLEMENTS)
                ->mapWithKeys(fn ($meta, $key) => [$key => (string) $request->input("entitlements.$key")])
                ->all(),
            'forwarding_address'       => (string) $request->input('forwarding_address'),
            'contact_number'           => (string) $request->input('contact_number'),
            'personal_email'           => trim((string) $request->input('personal_email')),
            'declaration_acknowledged' => $request->boolean('declaration_acknowledged'),
            'hr_officer'               => (string) $request->input('hr_officer'),
            'hr_manager'               => (string) $request->input('hr_manager'),
            'hr_remarks'               => (string) $request->input('hr_remarks'),
        ];

        if ($error = \App\Support\Clearance::hrIssueError($payload, $decision)) {
            return $this->workflowResponse($request, $error, $employee->id, false);
        }

        DB::transaction(function () use ($case, $employee, $payload, $decision) {
            $case->update([
                'hr_clearance_data'     => $payload,
                'hr_clearance_status'   => 'issued',
                'hr_clearance_decision' => $decision,
                'hr_cleared_by'         => user()->id,
                'hr_cleared_at'         => now(),
            ]);

            if (!empty($payload['personal_email']) && $employee->employeeDetail) {
                $employee->employeeDetail->personal_email = $payload['personal_email'];
                $employee->employeeDetail->save();
            }

            HrLifecycleEvent::create([
                'subject_user_id' => $employee->id,
                'company_id' => $employee->company_id,
                'event' => 'hr_clearance_issued',
                'actor_id' => user()->id,
                'meta' => ['case_id' => $case->id, 'decision' => $decision],
            ]);
        });

        \App\Jobs\SendEmployeeClearanceDocument::dispatch($employee->id, 'hr')->afterCommit();

        return Reply::successWithData('HR clearance issued.', [
            'redirectUrl' => route('hr-lifecycle.offboarding.hr-clearance', $case->id),
        ]);
    }

    /** Tabbed offboarding console for one case (Overview / Clearance / Finance / Assets / Statutory). */
    public function offboardingConsole(HrOffboardingCase $case)
    {
        $employee = $this->employee($case->employee_id);
        $this->authorizeEmployee($employee);

        $case->load(['tasks.completedBy', 'termination', 'employee.employeeDetail.designation', 'employee.employeeDetail.department', 'employee.branch']);

        $this->case = $case;
        $this->employee = $employee;
        $this->termination = $case->termination;
        $this->settlement = $case->termination
            ? \App\Models\HrSettlementForm::with('lineItems')->where('employee_termination_id', $case->termination->id)->first()
            : null;
        $this->assignedAssets = \App\Models\AssetAssignment::with('asset')
            ->where('employee_id', $employee->id)
            ->whereIn('status', [\App\Models\AssetAssignment::STATUS_PENDING, \App\Models\AssetAssignment::STATUS_ASSIGNED])
            ->get();
        $this->returnForms = \App\Models\AssetReturnForm::with('asset')
            ->where('employee_id', $employee->id)->latest()->get();
        $this->timeline = HrLifecycleEvent::with('actor')->where('subject_user_id', $employee->id)->latest('created_at')->get();

        return view('hr-lifecycle.offboarding-console', $this->data);
    }

    /** Employee self-service: own resignation request + read-only offboarding progress. */
    public function myResignation()
    {
        $this->pageTitle = 'My resignation';
        $employee = User::withoutGlobalScope(ActiveScope::class)->with('employeeDetail')->findOrFail(user()->id);

        $this->employee = $employee;
        $this->case = HrOffboardingCase::with(['tasks', 'termination'])
            ->where('employee_id', $employee->id)
            ->whereIn('status', ['open', 'completion_pending', 'completed'])
            ->latest()
            ->first();
        $this->settlement = $this->case?->termination
            ? \App\Models\HrSettlementForm::where('employee_termination_id', $this->case->termination->id)->first()
            : null;

        return view('hr-lifecycle.my-resignation', $this->data);
    }

    /** IT queue: return certifications + assets still out with departing staff. */
    public function itWorklist()
    {
        $this->pageTitle = 'IT offboarding worklist';
        $permission = user()->permission('manage_it_clearance');
        abort_403(!in_array($permission, ['all', 'branch'], true));
        $company = user()->company_id;

        $this->pendingCertifications = \App\Models\HrAssetCustodyRecord::with(['assignment.asset', 'assignment.employee'])
            ->whereNotNull('returned_at')->whereNull('certified_at')
            ->when($permission === 'branch', fn ($q) => $q->whereHas('assignment.asset', fn ($a) => $a->where('branch_id', user()->branch_id)))
            ->latest('returned_at')->get();

        $openCaseEmployeeIds = HrOffboardingCase::where('company_id', $company)
            ->whereIn('status', ['open', 'completion_pending'])->pluck('employee_id');

        $this->assetsStillOut = \App\Models\AssetAssignment::with(['asset', 'employee'])
            ->whereIn('employee_id', $openCaseEmployeeIds)
            ->whereIn('status', [\App\Models\AssetAssignment::STATUS_PENDING, \App\Models\AssetAssignment::STATUS_ASSIGNED])
            ->get();

        return view('hr-lifecycle.it-worklist', $this->data);
    }

    /** Finance queue: settlements to prepare / finalise + asset recoveries to approve. */
    public function financeWorklist()
    {
        $this->pageTitle = 'Finance offboarding worklist';
        abort_403(user()->permission('manage_finance_clearance') === 'none');
        $company = user()->company_id;

        $this->pendingTerminations = EmployeeTermination::with('employee')
            ->where('company_id', $company)
            ->where('status', EmployeeTermination::STATUS_PENDING)
            ->get()
            ->map(function ($t) {
                $t->setAttribute('settlement', \App\Models\HrSettlementForm::where('employee_termination_id', $t->id)->first());
                return $t;
            });

        $this->pendingRecoveries = \App\Models\AssetReturnForm::with(['asset', 'employee'])
            ->where('company_id', $company)
            ->whereIn('recovery_status', ['not_required', 'partially_approved'])
            ->whereNotNull('recommended_recovery_amount')
            ->where('recommended_recovery_amount', '>', 0)
            ->latest()->get();

        return view('hr-lifecycle.finance-worklist', $this->data);
    }

    public function updateTask(Request $request, string $type, int $taskId)
    {
        abort_403(!in_array($type, ['onboarding', 'offboarding'], true));
        $table = 'hr_' . $type . '_tasks';
        $caseTable = 'hr_' . $type . '_cases';
        $task = DB::table($table)->join($caseTable, $caseTable . '.id', '=', $table . '.case_id')->select($table . '.*', $caseTable . '.employee_id')->where($table . '.id', $taskId)->first(); abort_if(!$task, 404);
        // The user the task is assigned to (e.g. the line manager for the handover
        // clearance) may tick their own task without wider employee-edit rights.
        if ((int) ($task->assigned_to ?? 0) !== (int) user()->id) {
            $this->authorizeEmployee($this->employee($task->employee_id));
        }
        if ($type === 'offboarding') {
            app(OffboardingService::class)->completeTask(HrOffboardingTask::findOrFail($taskId), user()->id, $request->boolean('complete'));
        } else {
            DB::table($table)->where('id', $taskId)->update(['status' => $request->boolean('complete') ? 'completed' : 'pending', 'completed_at' => $request->boolean('complete') ? now() : null, 'updated_at' => now()]);
            $this->syncCaseCompletion($type, $task->case_id);
        }
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
        $hasActiveAssets = \App\Models\AssetAssignment::where('employee_id', $employee->id)->whereIn('status', [\App\Models\AssetAssignment::STATUS_PENDING, \App\Models\AssetAssignment::STATUS_ASSIGNED])->exists();
        $data = $request->validate(['to_branch_id' => 'required|exists:branches,id', 'to_department_id' => 'nullable|exists:teams,id', 'to_manager_id' => 'nullable|exists:users,id', 'effective_date' => 'required|date', 'reason' => 'nullable|string', 'asset_decision' => ($hasActiveAssets ? 'required|' : 'nullable|') . 'in:retain,return_required,reassign']);
        if (!empty($data['to_department_id']) && !Team::whereKey($data['to_department_id'])->where('company_id', $employee->company_id)->exists()) abort(422, 'The selected department is not available.');
        if (!empty($data['to_manager_id']) && !User::whereKey($data['to_manager_id'])->where('company_id', $employee->company_id)->exists()) abort(422, 'The selected manager is not available.');
        HrEmployeeTransfer::create(['company_id' => $employee->company_id, 'employee_id' => $employee->id, 'from_branch_id' => $employee->branch_id, 'to_branch_id' => $data['to_branch_id'], 'from_department_id' => $employee->employeeDetail?->department_id, 'to_department_id' => $data['to_department_id'] ?? $employee->employeeDetail?->department_id, 'from_manager_id' => $employee->employeeDetail?->reporting_to, 'to_manager_id' => $data['to_manager_id'] ?? $employee->employeeDetail?->reporting_to, 'effective_date' => $data['effective_date'], 'reason' => $data['reason'] ?? null, 'asset_decision' => $data['asset_decision'] ?? null, 'requested_by' => user()->id]);
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
        $hasActiveAssets = \App\Models\AssetAssignment::where('employee_id', $transfer->employee_id)->whereIn('status', [\App\Models\AssetAssignment::STATUS_PENDING, \App\Models\AssetAssignment::STATUS_ASSIGNED])->exists();
        if ($hasActiveAssets && $transfer->asset_decision !== 'retain') {
            return $this->workflowResponse($request, 'Complete the selected asset return or reassignment workflow before applying this transfer.', $transfer->employee_id);
        }
        DB::transaction(function () use ($transfer) {
            User::whereKey($transfer->employee_id)->update(['branch_id' => $transfer->to_branch_id]);
            EmployeeDetails::where('user_id', $transfer->employee_id)->update(['department_id' => $transfer->to_department_id ?? $transfer->from_department_id, 'reporting_to' => $transfer->to_manager_id ?? $transfer->from_manager_id]);
            $transfer->update(['status' => 'applied', 'applied_at' => now()]);
            $syncJob = \App\Models\HrSystemSyncJob::create([
                'employee_id' => $transfer->employee_id,
                'operation' => 'transfer_branch_sync',
                'systems' => ['dms', 'dobs'],
            ]);
            \App\Jobs\ProcessHrSystemSyncJob::dispatch($syncJob->id)->afterCommit();
        });
        return $this->workflowResponse($request, 'Transfer applied.', $transfer->employee_id);
    }

    private function employee($id): User { return User::withoutGlobalScope(ActiveScope::class)->with('employeeDetail')->findOrFail($id); }
    private function authorizeEmployee(User $employee): void { $permission = user()->permission('edit_employees'); abort_403(!($permission === 'all' || ($permission === 'branch' && user()->branch_id === $employee->branch_id))); }
    private function syncCaseCompletion(string $type, int $caseId): void { $taskTable = 'hr_' . $type . '_tasks'; $caseTable = 'hr_' . $type . '_cases'; $openTasks = DB::table($taskTable)->where('case_id', $caseId)->where('status', '!=', 'completed')->exists(); if (!$openTasks) { DB::table($caseTable)->where('id', $caseId)->update(['status' => 'completed', 'completed_at' => now(), 'updated_at' => now()]); } }
    private function workflowResponse(Request $request, string $message, int $employeeId, bool $ok = true)
    {
        if ($request->ajax()) {
            return $ok
                ? Reply::successWithData($message, ['redirectUrl' => route('hr-lifecycle.show', $employeeId)])
                : Reply::error($message);
        }
        return redirect()->route('hr-lifecycle.show', $employeeId)->with($ok ? 'success' : 'error', $message);
    }
}
