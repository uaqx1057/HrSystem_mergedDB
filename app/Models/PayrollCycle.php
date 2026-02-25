<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollCycle extends BaseModel
{
    protected $guarded = ['id'];

    public function slips(): HasMany
    {
        return $this->hasMany(SalarySlip::class, 'payroll_cycle_id');
    }
}
