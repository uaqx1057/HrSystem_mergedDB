<?php

namespace App\Models;

class HrOffboardingTaskTemplate extends BaseModel
{
    protected $table = 'hr_offboarding_task_templates';

    protected $guarded = ['id'];

    protected $casts = ['is_required' => 'boolean'];
}
