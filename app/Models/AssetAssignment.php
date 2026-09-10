<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignment extends Model
{
    use HasFactory;

    // Kept in their original casing on purpose: asset_assignments.status is also
    // read by TerminationClearanceController / HrAssetCustodyController / EmployeeController.
    // Normalised to lowercase by 2026_09_09_220000_normalize_hr_status_casing.
    public const STATUS_PENDING  = 'pending';
    public const STATUS_ASSIGNED = 'assigned';

    protected $fillable = [
        'employee_id',
        'company_asset_id',
        'company_asset_serial_id',
        'document_path',
        'signed_document',
        'status',
        'branch_id',
        'qty',
        'added_by',
        'serial_no',
    ];

    protected $casts = [
        'qty' => 'integer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(CompanyAssetSerial::class, 'company_asset_serial_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isAssigned(): bool
    {
        return $this->status === self::STATUS_ASSIGNED;
    }

    /** Best-effort serial label: the live serial number, falling back to the stored string. */
    public function serialLabel(): ?string
    {
        return $this->serial?->serial_no ?: $this->serial_no;
    }
}
