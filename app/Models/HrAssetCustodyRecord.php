<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrAssetCustodyRecord extends BaseModel
{
    protected $table = 'hr_asset_custody_records';

    protected $guarded = ['id'];

    protected $casts = [
        'accepted_at' => 'datetime',
        'returned_at' => 'datetime',
        'certified_at' => 'datetime',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(AssetAssignment::class, 'asset_assignment_id');
    }

    public function returnForm(): BelongsTo
    {
        return $this->belongsTo(AssetReturnForm::class, 'asset_return_form_id');
    }

    public function certifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'certified_by')->withoutGlobalScopes();
    }
}