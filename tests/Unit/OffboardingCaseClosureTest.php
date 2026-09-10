<?php

namespace Tests\Unit;

use Tests\TestCase;

class OffboardingCaseClosureTest extends TestCase
{
    public function test_canonical_service_owns_case_completion_and_reversion(): void
    {
        $service = file_get_contents(app_path('Services/OffboardingService.php'));
        $controller = file_get_contents(app_path('Http/Controllers/EmployeeController.php'));

        // Completion and reversion are canonical-service operations, not left
        // stranded at completion_pending.
        $this->assertStringContainsString('public function complete(HrOffboardingCase $case', $service);
        $this->assertStringContainsString('public function revert(HrOffboardingCase $case', $service);
        $this->assertStringContainsString("'status' => 'completed'", $service);
        $this->assertStringContainsString("'completed_by' => \$actorId", $service);
        $this->assertStringContainsString("'status' => 'reverted'", $service);

        // The legal-termination controller delegates the case-record transition.
        $this->assertStringContainsString('app(OffboardingService::class)->complete($termination->offboardingCase, user()->id)', $controller);
        $this->assertStringContainsString('app(OffboardingService::class)->revert($termination->offboardingCase, user()->id', $controller);
    }

    public function test_approval_adopts_a_legacy_pending_termination_instead_of_duplicating_it(): void
    {
        $service = file_get_contents(app_path('Services/OffboardingService.php'));

        $this->assertStringContainsString("whereNull('offboarding_case_id')", $service);
        $this->assertStringContainsString('where(\'status\', EmployeeTermination::STATUS_PENDING)', $service);
        $this->assertStringContainsString("\$termination->fill(\$terminationData + ['offboarding_case_id' => \$case->id])->save()", $service);
    }
}
