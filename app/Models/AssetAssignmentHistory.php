<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAssignmentHistory extends Model
{
    use HasFactory;

    public const ACTION_ASSIGNED = 'Assigned';
    public const ACTION_RETURNED = 'Returned';
    public const ACTION_WRITTEN_OFF = 'Written Off';

    protected $table = 'asset_assignment_history';

    protected $fillable = [
        'company_asset_id',
        'company_asset_serial_id',
        'employee_id',
        'action_type',
        'qty',
        'action_at',
        'signed_document',
        'asset_assignment_id',
        'added_by',
        'serial_no',
    ];

    protected $casts = [
        'action_at' => 'datetime',
        'qty'       => 'integer',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(CompanyAssetSerial::class, 'company_asset_serial_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function addedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }
}
