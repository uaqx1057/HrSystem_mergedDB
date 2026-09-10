<?php

namespace Tests\Unit;

use App\Models\HrSettlementForm;
use App\Services\HrSettlementService;
use Tests\TestCase;

class HrSettlementServiceTest extends TestCase
{
    public function test_settlement_service_uses_versioned_inputs_and_finalization_lock(): void
    {
        $service = file_get_contents(app_path('Services/HrSettlementService.php'));
        $migration = file_get_contents(database_path('migrations/2026_09_09_170000_create_hr_settlement_tables.php'));

        $this->assertStringContainsString("public const POLICY_VERSION = 'provisional-2026-09-09'", $service);
        $this->assertStringContainsString('\'inputs\' => $inputs', $service);
        $this->assertStringContainsString("->lockForUpdate()", $service);
        $this->assertStringContainsString("'status' => HrSettlementForm::STATUS_FINAL", $service);
        $this->assertStringContainsString('$table->string(\'policy_version\')', $migration);
        $this->assertStringContainsString('$table->decimal(\'net_amount\', 15, 2)', $migration);
    }
}
