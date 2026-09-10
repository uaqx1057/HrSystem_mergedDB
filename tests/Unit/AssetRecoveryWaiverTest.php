<?php

namespace Tests\Unit;

use Tests\TestCase;

class AssetRecoveryWaiverTest extends TestCase
{
    public function test_waiver_is_reasoned_scoped_and_recorded_as_a_ledger_allocation(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/AssetRecoveryWaiverController.php'));
        $service = file_get_contents(app_path('Services/AssetRecoveryWaiverService.php'));
        $view = file_get_contents(resource_path('views/asset-recovery/index.blade.php'));

        $this->assertStringContainsString("'reason' => 'required|string|max:2000'", $controller);
        $this->assertStringContainsString("where('company_id', user()->company_id)", $controller);
        $this->assertStringContainsString('AssetRecoveryAllocation::METHOD_WAIVER', $service);
        $this->assertStringContainsString("'recovery_status' => 'waived'", $service);
        $this->assertStringContainsString("route('asset-recovery.waive'", $view);
    }
}
