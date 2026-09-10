<?php

namespace Tests\Unit;

use Tests\TestCase;

class AssetRecoveryAllocationStateTest extends TestCase
{
    public function test_partial_approval_stays_visible_and_legacy_loss_state_is_synchronized(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/AssetRecoveryController.php'));
        $approval = file_get_contents(app_path('Services/AssetRecoveryApprovalService.php'));
        $waiver = file_get_contents(app_path('Services/AssetRecoveryWaiverService.php'));

        $this->assertStringContainsString("whereIn('recovery_status', ['not_required', 'partially_approved'])", $controller);
        $this->assertStringContainsString("'recovery_status' => \$remainingAfter > 0 ? 'partially_approved' : 'approved'", $approval);
        $this->assertStringContainsString('EmployeeAssessLoss::STATUS_SETTLED', $approval);
        $this->assertStringContainsString('AssetRecoveryAllocation::METHOD_WAIVER', $waiver);
        $this->assertStringContainsString('EmployeeAssessLoss::STATUS_SETTLED', $waiver);

        // The allocation ledger is the single source of truth. Neither service
        // may touch deducted_amount - that only moves when payroll or the final
        // settlement actually consumes the obligation, so it cannot be recovered
        // twice. The loss is only resolved once fully covered / waived.
        $this->assertStringNotContainsString("'deducted_amount' =>", $approval);
        $this->assertStringNotContainsString("'deducted_amount' =>", $waiver);
        $this->assertStringNotContainsString("DB::raw('loss_amount')", $waiver);
        $this->assertStringContainsString('$lossId && $remainingAfter <= 0', $approval);
        $this->assertStringContainsString("->where('status', EmployeeAssessLoss::STATUS_PENDING)", $approval);
        $this->assertStringContainsString("->where('status', EmployeeAssessLoss::STATUS_PENDING)", $waiver);
    }
}
