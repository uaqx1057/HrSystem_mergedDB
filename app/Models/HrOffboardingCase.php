<?php

namespace App\Models;

class HrOffboardingCase extends BaseModel
{
	protected $table = 'hr_offboarding_cases';
	protected $guarded = ['id'];

	protected $casts = [
		'last_working_date' => 'date',
		'resignation_date' => 'date',
		'completed_at' => 'datetime',
	];

	public function employee()
	{
		return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScopes();
	}
}
