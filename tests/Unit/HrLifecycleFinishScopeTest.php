<?php

namespace Tests\Unit;

use Tests\TestCase;

/**
 * Guards the "finish the implementation" deliverables: the three legal PDFs on
 * the Speed Logi letterhead, the settlement policy engine, the offboarding
 * approvals inbox, the HR-0111 clearance document, and the backfill migration.
 */
class HrLifecycleFinishScopeTest extends TestCase
{
    public function test_legal_pdfs_render_on_the_speed_logi_letterhead_with_cr(): void
    {
        $rt = file_get_contents(resource_path('views/company-assets/rt-final-pdf.blade.php'));
        $fc = file_get_contents(resource_path('views/hr-settlement/pdf.blade.php'));
        $hr = file_get_contents(resource_path('views/hr-lifecycle/clearance-pdf.blade.php'));
        $handover = file_get_contents(resource_path('views/company-assets/pdf.blade.php'));

        foreach (['rt' => $rt, 'fc' => $fc, 'hr' => $hr] as $name => $view) {
            $this->assertStringContainsString('speedlogi-letterhead.png', $view, "$name PDF missing letterhead");
            $this->assertStringContainsString('CR 7038950171', $view, "$name PDF missing CR number");
            $this->assertStringContainsString('#5b2a86', $view, "$name PDF missing Speed Logi purple");
        }

        // Handover carries the numbered recovery clause the RT form references.
        $this->assertStringContainsString('[Clause&nbsp;6]', $handover);
        $this->assertStringContainsString('Clause&nbsp;6 of the Company Asset Handover', $rt);
    }

    public function test_settlement_engine_and_policy_are_configurable(): void
    {
        $service = file_get_contents(app_path('Services/HrSettlementService.php'));

        $this->assertStringContainsString('public function computeEndOfService(', $service);
        $this->assertStringContainsString('public function suggestedInputs(', $service);
        $this->assertStringContainsString('HrSettlementSetting::forCompany', $service);
        $this->assertStringContainsString("DB::table('advance_salaries')", $service);
        $this->assertStringContainsString("DB::table('asset_recovery_allocations as a')", $service);

        $this->assertTrue(is_file(database_path('migrations/2026_09_09_210000_create_hr_settlement_settings.php')));
        $this->assertStringContainsString('resign_2_to_5yr_fraction', file_get_contents(app_path('Models/HrSettlementSetting.php')));
    }

    public function test_offboarding_approvals_and_hr0111_are_wired(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        $service = file_get_contents(app_path('Services/OffboardingService.php'));

        $this->assertStringContainsString("->name('hr-lifecycle.approvals')", $routes);
        $this->assertStringContainsString("->name('hr-lifecycle.offboarding.clearance-pdf')", $routes);
        $this->assertStringContainsString("->name('hr-settlement.settings')", $routes);

        // DEFAULT_TASKS is the HR-0111 departmental clearance grid.
        $this->assertStringContainsString('Line manager', $service);
        $this->assertStringContainsString('Admin / Government Relations', $service);
        $this->assertStringContainsString("'is_required' => false", $service);
    }

    public function test_backfill_migration_is_unambiguous_and_reports_exceptions(): void
    {
        $file = database_path('migrations/2026_09_09_200000_backfill_offboarding_case_links.php');
        $this->assertTrue(is_file($file));

        $body = file_get_contents($file);
        $this->assertStringContainsString("whereNull('offboarding_case_id')", $body);
        $this->assertStringContainsString('offboarding_backfill_exception', $body);
        $this->assertStringContainsString('$openCases->count() > 1', $body);
    }

    public function test_settlement_engine_auto_derives_pending_salary_and_notice(): void
    {
        $service = file_get_contents(app_path('Services/HrSettlementService.php'));

        $this->assertStringContainsString('private function pendingSalary(', $service);
        $this->assertStringContainsString('private function noticePaymentInLieu(', $service);
        $this->assertStringContainsString("DB::table('salary_slips')", $service);
        $this->assertStringContainsString('notice_period_end_date', $service);
    }

    public function test_rt_inspection_sections_are_structured_rows(): void
    {
        $this->assertTrue(is_file(database_path('migrations/2026_09_09_230000_create_asset_return_form_lines.php')));
        $this->assertTrue(is_file(app_path('Models/AssetReturnFormLine.php')));

        $service = file_get_contents(app_path('Services/AssetReturnService.php'));
        $this->assertStringContainsString('public const CHECKLIST', $service);
        $this->assertStringContainsString('private function persistLines(', $service);

        $pdf = file_get_contents(resource_path('views/company-assets/rt-final-pdf.blade.php'));
        $this->assertStringContainsString('$bySection', $pdf);
    }

    public function test_status_casing_is_normalised_and_readers_use_constants(): void
    {
        $this->assertSame('pending', \App\Models\AssetAssignment::STATUS_PENDING);
        $this->assertSame('assigned', \App\Models\AssetAssignment::STATUS_ASSIGNED);
        $this->assertSame('settled', \App\Models\EmployeeAssessLoss::STATUS_SETTLED);

        $migration = database_path('migrations/2026_09_09_220000_normalize_hr_status_casing.php');
        $this->assertTrue(is_file($migration));
        $body = file_get_contents($migration);
        $this->assertStringContainsString("MODIFY status VARCHAR", $body);

        $tcc = file_get_contents(app_path('Http/Controllers/TerminationClearanceController.php'));
        $this->assertStringNotContainsString("'status', 'Assigned'", $tcc);
    }

    public function test_role_console_screens_exist(): void
    {
        $routes = file_get_contents(base_path('routes/web.php'));
        foreach ([
            'hr-lifecycle.my-resignation', 'hr-lifecycle.it-worklist',
            'hr-lifecycle.finance-worklist', 'hr-lifecycle.offboarding.console',
        ] as $name) {
            $this->assertStringContainsString("->name('$name')", $routes);
        }

        foreach (['my-resignation', 'offboarding-console', 'it-worklist', 'finance-worklist'] as $view) {
            $this->assertTrue(is_file(resource_path("views/hr-lifecycle/$view.blade.php")), "missing $view view");
        }
    }
}
