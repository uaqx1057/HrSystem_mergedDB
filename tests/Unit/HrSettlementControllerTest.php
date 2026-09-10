<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrSettlementControllerTest extends TestCase
{
    public function test_settlement_console_is_company_scoped_and_exposes_finalize_action(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrSettlementController.php'));
        $view = file_get_contents(resource_path('views/hr-settlement/edit.blade.php'));
        $employeeController = file_get_contents(app_path('Http/Controllers/EmployeeController.php'));

        $this->assertStringContainsString("user()->permission('manage_finance_clearance')", $controller);
        $this->assertStringContainsString('$termination->employee?->company_id !== user()->company_id', $controller);
        $this->assertStringContainsString("route('hr-settlement.finalize'", $view);
        $this->assertStringContainsString('A finalized finance settlement is required before completing termination.', $employeeController);
    }
}
