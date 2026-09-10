<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrWorklistBlockersTest extends TestCase
{
    public function test_worklist_surfaces_operational_blockers(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrWorklistController.php'));
        $view = file_get_contents(resource_path('views/hr-worklist/index.blade.php'));

        $this->assertStringContainsString('AssetReturnForm::where', $controller);
        $this->assertStringContainsString('HrAssetCustodyRecord::whereNotNull', $controller);
        $this->assertStringContainsString('pendingItCertificationCount', $controller);
        $this->assertStringContainsString("user()->permission('manage_it_clearance') === 'branch'", $controller);
        $this->assertStringContainsString('HrSystemSyncJob::where', $controller);
        $this->assertStringContainsString('HrDocumentExpiry::forEmployees', $controller);
        $this->assertStringContainsString('Documents expired or expiring in 90 days', $controller);
        $this->assertStringNotContainsString('client_id', $controller);
        $this->assertStringContainsString("route('asset-recovery.index'", $view);
        $this->assertStringContainsString("route('hr-asset-custody.certifications'", $view);
    }
}
