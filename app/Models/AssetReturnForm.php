<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetReturnForm extends Model
{
    public const OUTCOME_RETURNED = 'returned';
    public const OUTCOME_LOST = 'lost';
    public const OUTCOME_DAMAGED = 'damaged';
    public const OUTCOME_RETIRED = 'retired';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINAL = 'final';

    protected $guarded = ['id'];

    protected $casts = [
        'snapshot' => 'array',
        'certified_at' => 'datetime',
        'recommended_recovery_amount' => 'decimal:2',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(CompanyAssetSerial::class, 'company_asset_serial_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScopes();
    }

    public function certifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by')->withoutGlobalScopes();
    }

    public function recoveryApprovedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recovery_approved_by')->withoutGlobalScopes();
    }

    public function history(): BelongsTo
    {
        return $this->belongsTo(AssetAssignmentHistory::class, 'asset_assignment_history_id');
    }

    public function allocations(): HasMany
    {
        return $this->hasMany(AssetRecoveryAllocation::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(AssetReturnFormLine::class)->orderBy('sort_order');
    }

    public function allocatedAmount(): float
    {
        return (float) $this->allocations()->sum('amount');
    }

    public function remainingRecoveryAmount(): float
    {
        return max(0.0, (float) $this->recommended_recovery_amount - $this->allocatedAmount());
    }
}
