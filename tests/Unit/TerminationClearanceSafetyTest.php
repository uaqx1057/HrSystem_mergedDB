<?php

namespace Tests\Unit;

use App\Models\EmployeeAssessLoss;
use Tests\TestCase;

class TerminationClearanceSafetyTest extends TestCase
{
    public function test_finance_clearance_blocks_each_kind_of_outstanding_financial_obligation(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/TerminationClearanceController.php'));

        $this->assertStringContainsString('if ($pendingDues || $pendingAssetDeductions)', $controller);
        $this->assertStringNotContainsString('if ($pendingDues && $pendingAssetDeductions)', $controller);
    }

    public function test_clearance_can_only_use_a_pending_termination(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/TerminationClearanceController.php'));
        $employeeController = file_get_contents(app_path('Http/Controllers/EmployeeController.php'));

        $this->assertStringContainsString("->where('status', EmployeeTermination::STATUS_PENDING)", $controller);
        $this->assertStringContainsString("->where('status', EmployeeTermination::STATUS_PENDING)", $employeeController);
    }

    public function test_it_clearance_rechecks_assets_inside_a_transaction(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/TerminationClearanceController.php'));
        $method = substr($controller, strpos($controller, 'public function itIssueClearance'));

        $this->assertStringContainsString('DB::transaction(function () use ($employee, $termination)', $method);
        $this->assertStringContainsString('AssetAssignment::STATUS_ASSIGNED', $method);
        $this->assertStringContainsString('->lockForUpdate()', $method);
    }

    public function test_termination_authorization_has_no_hard_coded_head_office_branch(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/EmployeeController.php'));

        $this->assertStringNotContainsString('user()->branch_id == 6', $controller);
    }

    public function test_completion_uses_a_durable_after_commit_sync_job(): void
    {
        $employeeController = file_get_contents(app_path('Http/Controllers/EmployeeController.php'));
        $syncService = file_get_contents(app_path('Services/EmployeeSystemSyncService.php'));
        $syncJob = file_get_contents(app_path('Jobs/ProcessHrSystemSyncJob.php'));

        $this->assertStringContainsString('DB::transaction(function () use ($request, $user, $termination)', $employeeController);
        $this->assertStringContainsString("'operation' => 'termination_completed'", $employeeController);
        $this->assertStringContainsString('ProcessHrSystemSyncJob::dispatch($syncJob->id)->afterCommit()', $employeeController);
        $this->assertStringContainsString('SendTerminationCompletedNotifications::dispatch($termination->id)->afterCommit()', $employeeController);
        $this->assertStringContainsString('bool $throwOnFailure = false', $syncService);
        $this->assertStringContainsString('if ($throwOnFailure) {', $syncService);
        $this->assertStringContainsString('class ProcessHrSystemSyncJob implements ShouldQueue', $syncJob);
        $this->assertStringContainsString('HrSystemSyncJob::STATUS_FAILED', $syncJob);
    }

    public function test_generic_lifecycle_tasks_do_not_deactivate_or_sync_offboarding_employees(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrLifecycleController.php'));

        $method = substr($controller, strpos($controller, 'private function syncCaseCompletion'));
        $this->assertStringNotContainsString("['status' => 'deactive']", $method);
        $this->assertStringNotContainsString('syncEmployeeProfileToLinkedSystems', $method);
    }

    public function test_offboarding_task_completion_remains_pending_until_legal_exit_requirements_are_met(): void
    {
        $service = file_get_contents(app_path('Services/OffboardingService.php'));
        $controller = file_get_contents(app_path('Http/Controllers/HrLifecycleController.php'));

        $this->assertStringContainsString("->whereNotIn('status', ['completed', 'waived'])", $service);
        $this->assertStringContainsString("'status' => 'completion_pending'", $service);
        $this->assertStringContainsString('->completeTask(HrOffboardingTask::findOrFail($taskId)', $controller);
    }

    public function test_legacy_termination_writers_delegate_to_the_canonical_service(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/EmployeeController.php'));

        $this->assertSame(2, substr_count($controller, 'app(OffboardingService::class)->request('));
        $this->assertStringContainsString("'last_working_date' => 'required|date'", $controller);
    }

    public function test_asset_loss_pending_status_is_named_and_centrally_reused(): void
    {
        // Normalised to lowercase by 2026_09_09_220000_normalize_hr_status_casing;
        // every reader now goes through these constants.
        $this->assertSame('pending', EmployeeAssessLoss::STATUS_PENDING);
        $this->assertSame('settled', EmployeeAssessLoss::STATUS_SETTLED);
        $this->assertSame('pending', \App\Models\AssetAssignment::STATUS_PENDING);
        $this->assertSame('assigned', \App\Models\AssetAssignment::STATUS_ASSIGNED);

        $payroll = file_get_contents(app_path('Http/Controllers/PayrollController.php'));
        $this->assertStringNotContainsString("'status', 'Pending'", $payroll);
        $this->assertStringNotContainsString("status = 'Deducted'", $payroll);
    }
}
