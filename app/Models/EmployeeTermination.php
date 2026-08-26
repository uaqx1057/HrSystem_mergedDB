<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeTermination extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';

    const CLEARANCE_PENDING = 'pending';
    const CLEARANCE_ISSUED = 'issued';

    const STATUS_REVERTED = 'reverted';   // add alongside existing STATUS_PENDING / STATUS_COMPLETED
    const STATUS_REJECTED = 'rejected';
    const EXIT_TERMINATION = 'termination';
    const EXIT_RESIGNATION = 'resignation';

    protected $fillable = [
        'user_id',
        'exit_type',
        'company_id',
        'initiated_by',
        'reason',
        'status',
        'it_clearance_status',
        'it_clearance_issued_by',
        'it_clearance_issued_at',
        'it_reminder_sent_at',
        'finance_clearance_status',
        'finance_clearance_issued_by',
        'finance_clearance_issued_at',
        'finance_reminder_sent_at',
        'completed_by',
        'completed_at',
        'reverted_by',
        'reverted_at',
        'revert_reason',
        'terminate_reason',
        'resignation_date',
        'last_working_date',
    ];

    protected $casts = [
        'it_clearance_issued_at' => 'datetime',
        'it_reminder_sent_at' => 'datetime',
        'finance_clearance_issued_at' => 'datetime',
        'finance_reminder_sent_at' => 'datetime',
        'completed_at' => 'datetime',
        'reverted_at' => 'datetime',
        'resignation_date' => 'date',
        'last_working_date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function itClearanceIssuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'it_clearance_issued_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function financeClearanceIssuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_clearance_issued_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function isFullyCleared(): bool
    {
        return $this->it_clearance_status === self::CLEARANCE_ISSUED
            && $this->finance_clearance_status === self::CLEARANCE_ISSUED;
    }

    public function revertedBy()
    {
        return $this->belongsTo(User::class, 'reverted_by');
    }

    public function isReverted()
    {
        return $this->status === self::STATUS_REVERTED;
    }
}
