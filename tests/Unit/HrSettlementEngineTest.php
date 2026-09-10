<?php

namespace Tests\Unit;

use App\Models\HrSettlementSetting;
use App\Services\HrSettlementService;
use Tests\TestCase;

class HrSettlementEngineTest extends TestCase
{
    private function statutorySettings(bool $terminationFullAward = true): HrSettlementSetting
    {
        return new HrSettlementSetting([
            'company_id' => 1,
            'eosb_wage_basis' => 'gross',
            'award_first_5yr_month_fraction' => 0.5,
            'award_after_5yr_month_fraction' => 1.0,
            'termination_gets_full_award' => $terminationFullAward,
            'resign_under_2yr_fraction' => 0.0,
            'resign_2_to_5yr_fraction' => 0.3333,
            'resign_5_to_10yr_fraction' => 0.6667,
            'resign_10yr_plus_fraction' => 1.0,
            'encash_leave_on_exit' => true,
            'default_annual_leave_days' => 21,
            'leave_daily_wage_divisor' => 30,
        ]);
    }

    public function test_article_84_award_uses_half_month_first_5_years_then_full_month(): void
    {
        $svc = new HrSettlementService();
        $s = $this->statutorySettings();

        // 3 yrs: 3 * 0.5 * 10000 = 15,000
        $this->assertEqualsWithDelta(15000.0, $svc->computeEndOfService(10000, 3, 'termination', $s)['award_full'], 0.01);

        // 8 yrs: (5 * 0.5 + 3 * 1.0) * 10000 = 55,000
        $this->assertEqualsWithDelta(55000.0, $svc->computeEndOfService(10000, 8, 'termination', $s)['award_full'], 0.01);
    }

    public function test_employer_termination_pays_the_full_award_unless_article_80(): void
    {
        $svc = new HrSettlementService();

        $full = $svc->computeEndOfService(10000, 8, 'termination', $this->statutorySettings(true));
        $this->assertEqualsWithDelta(55000.0, $full['eosb'], 0.01);

        $art80 = $svc->computeEndOfService(10000, 8, 'termination', $this->statutorySettings(false));
        $this->assertSame(0.0, $art80['eosb']);
    }

    public function test_resignation_applies_the_article_85_fraction_by_service_length(): void
    {
        $svc = new HrSettlementService();
        $s = $this->statutorySettings();

        // < 2 yrs -> nothing
        $this->assertSame(0.0, $svc->computeEndOfService(10000, 1.5, 'resignation', $s)['eosb']);

        // 3 yrs -> award 15,000 * 1/3
        $this->assertEqualsWithDelta(4999.5, $svc->computeEndOfService(10000, 3, 'resignation', $s)['eosb'], 0.5);

        // 7 yrs -> award 45,000 * 2/3
        $this->assertEqualsWithDelta(30001.5, $svc->computeEndOfService(10000, 7, 'resignation', $s)['eosb'], 0.5);

        // 12 yrs -> full award 95,000
        $this->assertEqualsWithDelta(95000.0, $svc->computeEndOfService(10000, 12, 'resignation', $s)['eosb'], 0.5);
    }

    public function test_resignation_fraction_boundaries(): void
    {
        $s = $this->statutorySettings();

        $this->assertSame(0.0, $s->resignationFraction(1.99));
        $this->assertSame(0.3333, $s->resignationFraction(2.0));
        $this->assertSame(0.3333, $s->resignationFraction(4.99));
        $this->assertSame(0.6667, $s->resignationFraction(5.0));
        $this->assertSame(0.6667, $s->resignationFraction(9.99));
        $this->assertSame(1.0, $s->resignationFraction(10.0));
    }

    public function test_default_settings_produce_a_real_award_without_a_saved_row(): void
    {
        // forCompany() falls back to an unsaved instance; it must carry the
        // statutory numbers, not null-cast-to-zero.
        $s = new HrSettlementSetting(HrSettlementSetting::DEFAULTS);
        $svc = new HrSettlementService();

        $this->assertSame(0.5, $s->award_first_5yr_month_fraction);
        $this->assertSame(1.0, $s->award_after_5yr_month_fraction);
        $this->assertTrue($s->termination_gets_full_award);
        $this->assertSame(30, $s->leave_daily_wage_divisor);

        // 3 yrs termination on defaults -> 3 * 0.5 * 10000 = 15,000 (not 0)
        $this->assertEqualsWithDelta(15000.0, $svc->computeEndOfService(10000, 3, 'termination', $s)['eosb'], 0.01);
    }
}
