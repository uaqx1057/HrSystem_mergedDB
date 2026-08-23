<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrCandidate extends BaseModel
{
    protected $table = 'hr_candidates';
    protected $guarded = ['id'];
    protected $casts = [
        'iqama_expiry_date' => 'date',
        'national_id_expiry_date' => 'date',
        'passport_expiry_date' => 'date',
    ];
    // Pipeline: applied -> screening -> interview_scheduled -> interviewed -> approved|rejected -> onboarding -> converted
    const STATUS_NEW = 'new';
    const STATUS_APPLIED = 'applied';
    const STATUS_SCREENING = 'screening';
    const STATUS_INTERVIEW_SCHEDULED = 'interview_scheduled';
    const STATUS_INTERVIEWED = 'interviewed';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';
    const STATUS_ONBOARDING = 'onboarding';
    const STATUS_HANDOFF = 'handoff';
    const STATUS_CONVERTED = 'converted';

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(HrJobOpening::class, 'job_opening_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(HrCandidateDocument::class, 'candidate_id');
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(HrInterviewSchedule::class, 'candidate_id');
    }

    public function onboardingCase()
    {
        return $this->hasOne(HrCandidateOnboardingCase::class, 'candidate_id')->latestOfMany();
    }

    public function convertedEmployee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'converted_employee_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

