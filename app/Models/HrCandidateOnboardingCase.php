<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrCandidateOnboardingCase extends BaseModel
{
    protected $table = 'hr_candidate_onboarding_cases';
    protected $guarded = ['id'];
    protected $casts = [
        'documents_verified' => 'boolean',
        'compensation_confirmed' => 'boolean',
        'bank_details_collected' => 'boolean',
        'contract_signed' => 'boolean',
        'manager_signoff' => 'boolean',
        'convert_to_employee' => 'boolean',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(HrCandidate::class, 'candidate_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(HrCandidateOnboardingTask::class, 'case_id');
    }
}
