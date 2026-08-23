<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrJobOpening extends BaseModel
{
    protected $table = 'hr_job_openings';
    protected $guarded = ['id'];
    protected $casts = [
        'closes_at' => 'date',
    ];

    const STATUS_OPEN = 'open';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUS_CLOSED = 'closed';

    public function department(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'department_id');
    }

    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'designation_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(HrCandidate::class, 'job_opening_id');
    }
}
