<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryPaymentMethod extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $table = 'salary_payment_methods';

    public function slips(): HasMany
    {
        return $this->hasMany(SalarySlip::class, 'salary_payment_method_id');
    }
}
