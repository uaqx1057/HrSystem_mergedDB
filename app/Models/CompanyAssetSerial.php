<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAssetSerial extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_AVAILABLE = 'available';
    public const STATUS_PENDING   = 'pending';   // reserved by a not-yet-signed assignment
    public const STATUS_ASSIGNED  = 'assigned';  // signed for, out with an employee
    public const STATUS_LOST      = 'lost';
    public const STATUS_DAMAGED   = 'damaged';
    public const STATUS_RETIRED   = 'retired';

    /** Statuses an admin may set manually from the asset detail screen. */
    public const MANUAL_STATUSES = [
        self::STATUS_AVAILABLE,
        self::STATUS_LOST,
        self::STATUS_DAMAGED,
        self::STATUS_RETIRED,
    ];

    protected $fillable = [
        'company_asset_id',
        'serial_no',
        'status',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    /**
     * The current assignment holding this unit, if any. A unit has at most one
     * live assignment because returned assignments are deleted, so a plain
     * hasOne is correct (and avoids MySQL 8-only window-function subqueries).
     */
    public function assignment(): HasOne
    {
        return $this->hasOne(AssetAssignment::class, 'company_asset_serial_id')->orderByDesc('id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(AssetAssignmentHistory::class, 'company_asset_serial_id');
    }

    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_AVAILABLE);
    }

    public function scopeInCirculation(Builder $query): Builder
    {
        return $query->whereIn('status', [self::STATUS_AVAILABLE, self::STATUS_PENDING, self::STATUS_ASSIGNED]);
    }

    public function isAvailable(): bool
    {
        return $this->status === self::STATUS_AVAILABLE;
    }
}
