<?php
namespace App\Models;
class HrOnboardingCase extends BaseModel { protected $table = 'hr_onboarding_cases'; protected $guarded = ['id']; public function employee() { return $this->belongsTo(User::class, 'employee_id'); } }
