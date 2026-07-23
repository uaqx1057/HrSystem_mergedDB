<?php
namespace App\Models;
class HrOffboardingCase extends BaseModel { protected $table = 'hr_offboarding_cases'; protected $guarded = ['id']; public function employee() { return $this->belongsTo(User::class, 'employee_id'); } }
