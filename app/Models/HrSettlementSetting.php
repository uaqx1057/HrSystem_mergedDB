<?php

namespace App\Models;

class HrSettlementSetting extends BaseModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'award_first_5yr_month_fraction' => 'float',
        'award_after_5yr_month_fraction' => 'float',
        'termination_gets_full_award' => 'boolean',
        'resign_under_2yr_fraction' => 'float',
        'resign_2_to_5yr_fraction' => 'float',
        'resign_5_to_10yr_fraction' => 'float',
        'resign_10yr_plus_fraction' => 'float',
        'encash_leave_on_exit' => 'boolean',
        'default_annual_leave_days' => 'integer',
        'leave_daily_wage_divisor' => 'integer',
    ];

    /**
     * Saudi Labour Law statutory positions (Articles 84 & 85). Kept in sync with
     * the column defaults in 2026_09_09_210000_create_hr_settlement_settings.
     * PROVISIONAL until HR / legal sign off per company.
     */
    public const DEFAULTS = [
        'eosb_wage_basis' => 'gross',
        'award_first_5yr_month_fraction' => 0.5,
        'award_after_5yr_month_fraction' => 1.0,
        'termination_gets_full_award' => true,
        'resign_under_2yr_fraction' => 0.0,
        'resign_2_to_5yr_fraction' => 0.3333,
        'resign_5_to_10yr_fraction' => 0.6667,
        'resign_10yr_plus_fraction' => 1.0,
        'encash_leave_on_exit' => true,
        'default_annual_leave_days' => 21,
        'leave_daily_wage_divisor' => 30,
        'policy_version' => 'provisional-2026-09-09',
    ];

    /**
     * The company's saved policy row, or an unsaved instance carrying the
     * statutory defaults - so the engine is correct even before an admin has
     * saved the policy screen (DB column defaults only apply to inserted rows,
     * not to a bare `new self()`).
     */
    public static function forCompany(int $companyId): self
    {
        return static::query()->firstWhere('company_id', $companyId)
            ?? new self(self::DEFAULTS + ['company_id' => $companyId]);
    }

    /** Article 85 fraction of the Article 84 award for a given service length. */
    public function resignationFraction(float $serviceYears): float
    {
        if ($serviceYears < 2) {
            return (float) ($this->resign_under_2yr_fraction ?? self::DEFAULTS['resign_under_2yr_fraction']);
        }
        if ($serviceYears < 5) {
            return (float) ($this->resign_2_to_5yr_fraction ?? self::DEFAULTS['resign_2_to_5yr_fraction']);
        }
        if ($serviceYears < 10) {
            return (float) ($this->resign_5_to_10yr_fraction ?? self::DEFAULTS['resign_5_to_10yr_fraction']);
        }

        return (float) ($this->resign_10yr_plus_fraction ?? self::DEFAULTS['resign_10yr_plus_fraction']);
    }
}
