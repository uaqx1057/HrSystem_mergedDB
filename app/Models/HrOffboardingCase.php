<?php

namespace App\Models;

class HrOffboardingCase extends BaseModel
{
	protected $table = 'hr_offboarding_cases';
	protected $guarded = ['id'];

	protected $casts = [
		'last_working_date' => 'date',
		'resignation_date' => 'date',
		'notice_start_date' => 'date',
		'completed_at' => 'datetime',
		'approved_at' => 'datetime',
		'rejected_at' => 'datetime',
		'access_revoked_at' => 'datetime',
		'reverted_at' => 'datetime',
		'hr_cleared_at' => 'datetime',
		'hr_clearance_data' => 'array',
		'settlement_amount' => 'decimal:2',
	];

	public function employee()
	{
		return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScopes();
	}

	public function tasks()
	{
		return $this->hasMany(HrOffboardingTask::class, 'case_id');
	}

	public function termination()
	{
		return $this->hasOne(EmployeeTermination::class, 'offboarding_case_id');
	}

	public function hrClearedBy()
	{
		return $this->belongsTo(User::class, 'hr_cleared_by')->withoutGlobalScopes();
	}
}
