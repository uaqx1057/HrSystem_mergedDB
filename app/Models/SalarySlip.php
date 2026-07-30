<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalarySlip extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $casts = [
        'paid_on' => 'date',
        'salary_from' => 'datetime',
        'salary_to' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'user_id');
    }

    public function salaryGroup(): BelongsTo
    {
        return $this->belongsTo(SalaryGroup::class, 'salary_group_id');
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(SalaryPaymentMethod::class, 'salary_payment_method_id');
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(EmployeeBankAccount::class, 'employee_bank_account_id');
    }

    public function cycle(): BelongsTo
    {
        return $this->belongsTo(PayrollCycle::class, 'payroll_cycle_id');
    }

    public function getPayeeTypeAttribute(): string
    {
        $payeeType = $this->attributes['payee_type'] ?? null;

        if (in_array($payeeType, ['employee', 'driver'])) {
            return $payeeType;
        }

        $salaryJson = is_string($this->salary_json) ? json_decode($this->salary_json, true) : $this->salary_json;

        if (is_array($salaryJson) && isset($salaryJson['payee_type']) && in_array($salaryJson['payee_type'], ['employee', 'driver'])) {
            return $salaryJson['payee_type'];
        }

        return 'employee';
    }

    public function getPayeeNameAttribute(): ?string
    {
        if ($this->payee_type === 'driver') {
            return optional($this->driver)->name ?: ('Driver #' . $this->user_id);
        }

        return optional($this->user)->name ?: ('Employee #' . $this->user_id);
    }

    public function advanceSalaries()
    {
        return $this->belongsToMany(AdvanceSalary::class, 'salary_slip_advance_salary')
            ->withPivot('deducted_amount')
            ->withTimestamps();
    }
}
