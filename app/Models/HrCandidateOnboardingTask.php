<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrCandidateOnboardingTask extends BaseModel
{
    protected $table = 'hr_candidate_onboarding_tasks';
    protected $guarded = ['id'];

    public function case(): BelongsTo
    {
        return $this->belongsTo(HrCandidateOnboardingCase::class, 'case_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
