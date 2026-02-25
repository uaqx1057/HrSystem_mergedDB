<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalaryGroup extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    public function components(): BelongsToMany
    {
        return $this->belongsToMany(SalaryComponent::class, 'salary_group_components', 'salary_group_id', 'salary_component_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'employee_salary_groups', 'salary_group_id', 'user_id');
    }

    public function slips(): HasMany
    {
        return $this->hasMany(SalarySlip::class, 'salary_group_id');
    }
}
