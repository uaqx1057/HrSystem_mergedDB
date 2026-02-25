<?php

namespace App\Models;

use App\Traits\HasCompany;

class SalaryGroupComponent extends BaseModel
{
    use HasCompany;

    protected $guarded = ['id'];

    protected $table = 'salary_group_components';
}
