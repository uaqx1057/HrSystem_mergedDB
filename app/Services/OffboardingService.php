<?php

namespace App\Services;

use App\Models\EmployeeTermination;
use App\Models\HrLifecycleEvent;
use App\Models\HrOffboardingCase;
use App\Models\HrOffboardingTask;
use App\Models\HrOffboardingTaskTemplate;
use App\Models\HrSystemSyncJob;
use App\Jobs\ProcessHrSystemSyncJob;
use App\Models\User;
use App\Scopes\ActiveScope;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OffboardingService
{
    /**
     * The HR-0111 departmental clearance grid. Fleet / Warehouse are optional
     * because most office exits never touch them - HR can waive or complete
     * them without blocking the case.
     */
    private const DEFAULT_TASKS = [
        ['title' => 'Line manager — work handover & pending deliverables', 'category' => 'handover', 'owner_type' => 'manager', 'is_required' => true],
        ['title' => 'IT / Assets — company assets returned & DMS/DOBS access revoked', 'category' => 'asset', 'owner_type' => 'it', 'is_required' => true],
        ['title' => 'Finance / Accounts — dues settled & final settlement prepared', 'category' => 'finance', 'owner_type' => 'finance', 'is_required' => true],
        ['title' => 'Operations / Fleet — vehicle, fuel card & fleet items returned', 'category' => 'asset', 'owner_type' => 'manager', 'is_required' => false],
        ['title' => 'Warehouse / Stores — stock, tools & keys returned', 'category' => 'handover', 'owner_type' => 'manager', 'is_required' => false],
        ['title' => 'Admin / Government Relations — iqama, exit visa & statutory actions', 'category' => 'statutory', 'owner_type' => 'hr', 'is_required' => true],
        ['title' => 'HR — exit interview, documents & personnel file archived', 'category' => 'documents', 'owner_type' => 'hr', 'is_required' => true],
    ];

    public function request(User $employee, int $actorId, string $exitType, array $data): HrOffboardingCase
    {
        return DB::transaction(function () use ($employee, $actorId, $exitType, $data) {
            $employee = User::withoutGlobalScope(ActiveScope::class)
                ->whereKey($employee->id)
                ->lockForUpdate()
                ->firstOrFail();

            $alreadyOpen = HrOffboardingCase::query()
                ->where('employee_id', $employee->id)
                ->whereIn('status', ['open', 'completion_pending'])
                ->lockForUpdate()
                ->exists();

            if ($alreadyOpen) {
                throw ValidationException::withMessages(['offboarding' => 'An offboarding case is already open for this employee.']);
            }

            $case = HrOffboardingCase::create([
                'company_id' => $employee->company_id,
                'employee_id' => $employee->id,
                'exit_type' => $exitType,
                'reason' => $data['reason'],
                'resignation_date' => $data['resignation_date'] ?? null,
                'last_working_date' => $data['last_working_date'],
                'status' => 'open',
                'approval_status' => 'awaiting_approval',
                'initiated_by' => $actorId,
            ]);

            $case->update(['reference' => 'HR-' . str_pad((string) $case->id, 6, '0', STR_PAD_LEFT)]);
            $this->log($employee, 'offboarding_requested', $actorId, ['case_id' => $case->id, 'exit_type' => $exitType]);

            return $case->fresh();
        });
    }

    public function approve(HrOffboardingCase $case, int $actorId): HrOffboardingCase
    {
        return DB::transaction(function () use ($case, $actorId) {
            $case = HrOffboardingCase::query()->whereKey($case->id)->lockForUpdate()->firstOrFail();

            if ($case->approval_status !== 'awaiting_approval') {
                throw ValidationException::withMessages(['offboarding' => 'This offboarding case has already been decided.']);
            }

            $employee = User::withoutGlobalScope(ActiveScope::class)->whereKey($case->employee_id)->lockForUpdate()->firstOrFail();

            $terminationData = [
                'user_id' => $employee->id,
                'company_id' => $employee->company_id,
                'initiated_by' => $case->initiated_by,
                'exit_type' => $case->exit_type,
                'reason' => $case->reason,
                'terminate_reason' => $case->reason,
                'resignation_date' => $case->resignation_date,
                'last_working_date' => $case->last_working_date,
                'status' => EmployeeTermination::STATUS_PENDING,
            ];

            // Adopt a legacy pending termination that predates the case link so
            // approval never leaves two open terminations for one employee.
            $termination = EmployeeTermination::query()
                ->where('user_id', $employee->id)
                ->whereNull('offboarding_case_id')
                ->where('status', EmployeeTermination::STATUS_PENDING)
                ->lockForUpdate()
                ->latest('id')
                ->first();

            if ($termination) {
                $termination->fill($terminationData + ['offboarding_case_id' => $case->id])->save();
            } else {
                $termination = EmployeeTermination::firstOrCreate(
                    ['offboarding_case_id' => $case->id],
                    $terminationData
                );
            }

            $case->update(['approval_status' => 'approved', 'approved_by' => $actorId, 'approved_at' => now()]);
            $this->createTasks($case, $employee);
            $syncJob = HrSystemSyncJob::create([
                'employee_id' => $employee->id,
                'offboarding_case_id' => $case->id,
                'operation' => 'revoke_access',
                'systems' => ['dms', 'dobs'],
            ]);
            ProcessHrSystemSyncJob::dispatch($syncJob->id)->afterCommit();
            $this->log($employee, 'offboarding_approved', $actorId, ['case_id' => $case->id, 'termination_id' => $termination->id]);

            return $case->fresh(['termination', 'tasks']);
        });
    }

    public function reject(HrOffboardingCase $case, int $actorId, string $reason): HrOffboardingCase
    {
        return DB::transaction(function () use ($case, $actorId, $reason) {
            $case = HrOffboardingCase::query()->whereKey($case->id)->lockForUpdate()->firstOrFail();

            if ($case->approval_status !== 'awaiting_approval') {
                throw ValidationException::withMessages(['offboarding' => 'This offboarding case has already been decided.']);
            }

            $case->update([
                'approval_status' => 'rejected',
                'status' => 'cancelled',
                'rejected_by' => $actorId,
                'rejected_at' => now(),
                'rejected_reason' => $reason,
            ]);

            $employee = User::withoutGlobalScope(ActiveScope::class)->findOrFail($case->employee_id);
            $this->log($employee, 'offboarding_rejected', $actorId, ['case_id' => $case->id, 'reason' => $reason]);

            return $case->fresh();
        });
    }

    /**
     * Close an approved case once the legal termination has been completed.
     * Owns only the case-workflow record; the caller owns the legal
     * termination, employee status and notice dates.
     */
    public function complete(HrOffboardingCase $case, int $actorId): HrOffboardingCase
    {
        return DB::transaction(function () use ($case, $actorId) {
            $case = HrOffboardingCase::query()->whereKey($case->id)->lockForUpdate()->firstOrFail();

            if ($case->approval_status !== 'approved' || !in_array($case->status, ['open', 'completion_pending'], true)) {
                throw ValidationException::withMessages(['offboarding' => 'Only an approved, in-progress offboarding case can be completed.']);
            }

            $case->update([
                'status' => 'completed',
                'completed_by' => $actorId,
                'completed_at' => now(),
            ]);

            $this->log($case->employee()->firstOrFail(), 'offboarding_completed', $actorId, ['case_id' => $case->id]);

            return $case->fresh();
        });
    }

    /**
     * Reverse a case whose legal termination has been reverted/rejected.
     */
    public function revert(HrOffboardingCase $case, int $actorId, ?string $reason = null): HrOffboardingCase
    {
        return DB::transaction(function () use ($case, $actorId, $reason) {
            $case = HrOffboardingCase::query()->whereKey($case->id)->lockForUpdate()->firstOrFail();

            if ($case->status === 'reverted') {
                return $case->fresh();
            }

            $case->update([
                'status' => 'reverted',
                'reverted_by' => $actorId,
                'reverted_at' => now(),
                'revert_reason' => $reason,
            ]);

            $this->log($case->employee()->firstOrFail(), 'offboarding_reverted', $actorId, ['case_id' => $case->id, 'reason' => $reason]);

            return $case->fresh();
        });
    }

    public function completeTask(HrOffboardingTask $task, int $actorId, bool $complete): HrOffboardingTask
    {
        return DB::transaction(function () use ($task, $actorId, $complete) {
            $task = HrOffboardingTask::query()->whereKey($task->id)->lockForUpdate()->firstOrFail();
            $case = HrOffboardingCase::query()->whereKey($task->case_id)->lockForUpdate()->firstOrFail();

            if ($case->approval_status !== 'approved' || $case->status !== 'open') {
                throw ValidationException::withMessages(['offboarding' => 'Only approved, open offboarding cases can have tasks updated.']);
            }

            $task->update([
                'status' => $complete ? 'completed' : 'pending',
                'completed_at' => $complete ? now() : null,
                'completed_by' => $complete ? $actorId : null,
            ]);

            $requiredTasksRemain = $case->tasks()
                ->where('is_required', true)
                ->whereNotIn('status', ['completed', 'waived'])
                ->exists();

            if (!$requiredTasksRemain) {
                $case->update(['status' => 'completion_pending']);
                $this->log($case->employee()->firstOrFail(), 'offboarding_tasks_completed', $actorId, ['case_id' => $case->id]);
            }

            return $task->fresh();
        });
    }

    private function createTasks(HrOffboardingCase $case, User $employee): void
    {
        $templates = HrOffboardingTaskTemplate::query()
            ->where('company_id', $case->company_id)
            ->where(function ($query) use ($case) {
                $query->whereNull('exit_type')->orWhere('exit_type', $case->exit_type);
            })
            ->where(function ($query) use ($employee) {
                $query->whereNull('employee_type')->orWhere('employee_type', $employee->employeeDetail?->employee_type);
            })
            ->orderBy('sort_order')
            ->get();

        $tasks = $templates->isNotEmpty()
            ? $templates->map(fn (HrOffboardingTaskTemplate $template) => [
                'title' => $template->title,
                'category' => $template->category,
                'owner_type' => $template->owner_type,
                'is_required' => $template->is_required,
            ])->all()
            : self::DEFAULT_TASKS;

        // Route the line-manager clearance to the employee's actual reporting
        // manager so they can complete it without wider employee-edit rights.
        $reportingTo = $employee->employeeDetail?->reporting_to;

        foreach ($tasks as $task) {
            HrOffboardingTask::create($task + [
                'case_id' => $case->id,
                'status' => 'pending',
                'assigned_to' => ($task['owner_type'] ?? null) === 'manager' ? $reportingTo : null,
            ]);
        }
    }

    private function log(User $employee, string $event, int $actorId, array $meta = []): void
    {
        HrLifecycleEvent::create([
            'subject_user_id' => $employee->id,
            'company_id' => $employee->company_id,
            'event' => $event,
            'actor_id' => $actorId,
            'meta' => $meta,
        ]);
    }
}
