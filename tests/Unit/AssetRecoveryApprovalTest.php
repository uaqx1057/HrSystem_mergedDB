<?php

namespace Tests\Unit;

use Tests\TestCase;

class AssetRecoveryApprovalTest extends TestCase
{
    public function test_finance_recovery_approval_is_scoped_and_capped(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/AssetRecoveryController.php'));
        $service = file_get_contents(app_path('Services/AssetRecoveryApprovalService.php'));
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString("user()->permission('manage_finance_clearance')", $controller);
        $this->assertStringContainsString("where('company_id', user()->company_id)", $controller);
        $this->assertStringContainsString('$amount > $recommended - $allocated', $service);
        $this->assertStringContainsString("'asset_return_form_id' => " . '$form->id', $service);
        $this->assertStringContainsString("asset-recovery/{id}/approve", $routes);
    }
}
