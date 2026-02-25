<?php

namespace App\Models;

use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SalaryComponent extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(SalaryGroup::class, 'salary_group_components', 'salary_component_id', 'salary_group_id');
    }
}
