<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrOffboardingTask extends BaseModel
{
    protected $table = 'hr_offboarding_tasks';

    protected $guarded = ['id'];

    protected $casts = [
        'is_required' => 'boolean',
        'due_date' => 'date',
        'completed_at' => 'datetime',
    ];

    public function case(): BelongsTo
    {
        return $this->belongsTo(HrOffboardingCase::class, 'case_id');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by')->withoutGlobalScopes();
    }
}
