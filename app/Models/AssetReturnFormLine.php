<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetReturnFormLine extends Model
{
    public const SECTION_ACCESSORIES = 'accessories';
    public const SECTION_TECHNICAL = 'technical';
    public const SECTION_DATA = 'data';

    protected $guarded = ['id'];

    public function form(): BelongsTo
    {
        return $this->belongsTo(AssetReturnForm::class, 'asset_return_form_id');
    }
}
