<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrSystemSyncJob extends BaseModel
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    protected $guarded = ['id'];

    protected $casts = [
        'systems' => 'array',
        'processed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScopes();
    }

    public function offboardingCase(): BelongsTo
    {
        return $this->belongsTo(HrOffboardingCase::class, 'offboarding_case_id');
    }
}
