<?php

namespace Tests\Unit;

use Tests\TestCase;

class CompanyAssetReturnAuthorizationTest extends TestCase
{
    public function test_return_and_signature_actions_are_route_scoped_and_authorized(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/CompanyAssetController.php'));

        $this->assertSame(8, substr_count($controller, 'authorizeAssignmentManagement('));
        $this->assertSame(3, substr_count($controller, "abort_404(\$request->filled('id') && (int) \$request->id !== (int) \$id)"));
        $this->assertStringContainsString("AssetAssignment::with(['serial', 'employee', 'asset'])->findOrFail(\$id)", $controller);
        $this->assertStringContainsString("AssetAssignment::with(['employee', 'asset', 'serial'])->findOrFail(\$id)", $controller);
        $this->assertStringContainsString("user()->permission('assign_company_asset_to_employee')", $controller);
        $this->assertStringContainsString("'status'                       => EmployeeAssessLoss::STATUS_PENDING", $controller);
    }
}
