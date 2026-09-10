<?php

namespace Tests\Unit;

use Tests\TestCase;

class AssetCustodyCertificationTest extends TestCase
{
    public function test_employee_return_requires_it_certification_before_rt_finalization(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrAssetCustodyCertificationController.php'));
        $model = file_get_contents(app_path('Models/HrAssetCustodyRecord.php'));
        $routes = file_get_contents(base_path('routes/web.php'));

        $this->assertStringContainsString("whereNotNull('returned_at')", $controller);
        $this->assertStringContainsString("whereNull('certified_at')", $controller);
        $this->assertStringContainsString("user()->permission('manage_it_clearance')", $controller);
        $this->assertStringContainsString('AssetReturnService::class)->certifyReturn', $controller);
        $this->assertStringContainsString('function assignment(): BelongsTo', $model);
        $this->assertStringContainsString('hr-asset-custody/{assignment}/certify', $routes);
    }
}
