<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HrSettlementLineItem extends BaseModel
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(HrSettlementForm::class, 'settlement_form_id');
    }
}
