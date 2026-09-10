<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompanyAsset extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_AVAILABLE           = 'available';
    public const STATUS_PARTIALLY_ASSIGNED  = 'partially_assigned';
    public const STATUS_ASSIGNED            = 'assigned';

    protected $fillable = [
        'catalog',
        'sku_no',
        'name',
        'type',
        'brand',
        'department_id',
        'branch_id',
        'qty',
        'available_qty',
        'status',
        'added_by',
    ];

    protected $casts = [
        'qty'           => 'integer',
        'available_qty' => 'integer',
    ];

    public function serials()
    {
        return $this->hasMany(CompanyAssetSerial::class);
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    /** Assignments already approved / signed (unit is out with an employee). */
    public function assignedAssignments()
    {
        return $this->hasMany(AssetAssignment::class)->where('status', AssetAssignment::STATUS_ASSIGNED);
    }

    public function history()
    {
        return $this->hasMany(AssetAssignmentHistory::class, 'company_asset_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    /**
     * Recompute the cached qty / available_qty / status columns from the real
     * serial rows. This is the single source of truth — call it after every
     * mutation instead of doing +/- arithmetic by hand.
     */
    public function syncAvailability(): void
    {
        $total     = $this->serials()->count();
        $available = $this->serials()->where('status', CompanyAssetSerial::STATUS_AVAILABLE)->count();

        $this->qty           = $total;
        $this->available_qty = $available;
        $this->status        = $available === 0
            ? self::STATUS_ASSIGNED
            : ($available < $total ? self::STATUS_PARTIALLY_ASSIGNED : self::STATUS_AVAILABLE);

        $this->saveQuietly();
    }

    public function hasActiveAssignments(): bool
    {
        return $this->assignments()->exists();
    }
}
