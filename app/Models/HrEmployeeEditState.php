<?php

namespace App\Models;

class HrEmployeeEditState extends BaseModel
{
    protected $table = 'hr_employee_edit_states';

    protected $fillable = ['company_id', 'employee_id', 'last_saved_step', 'version', 'last_saved_by', 'last_saved_at'];

    protected $casts = ['last_saved_at' => 'datetime'];
}
