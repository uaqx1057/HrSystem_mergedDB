<?php

namespace Tests\Unit;

use Tests\TestCase;

class HrTransferAssetDecisionTest extends TestCase
{
    public function test_transfer_requires_explicit_asset_decision_and_blocks_unresolved_assets(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/HrLifecycleController.php'));
        $view = file_get_contents(resource_path('views/hr-lifecycle/show.blade.php'));
        $migration = file_get_contents(database_path('migrations/2026_09_09_190000_add_asset_decision_to_hr_employee_transfers.php'));

        $this->assertStringContainsString("'asset_decision' => (\$hasActiveAssets ? 'required|' : 'nullable|')", $controller);
        $this->assertStringContainsString("\$transfer->asset_decision !== 'retain'", $controller);
        $this->assertStringContainsString('name="asset_decision"', $view);
        $this->assertStringContainsString("asset_decision", $migration);
    }
}
