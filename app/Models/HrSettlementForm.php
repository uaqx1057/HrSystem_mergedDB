<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HrSettlementForm extends BaseModel
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINAL = 'final';

    protected $guarded = ['id'];

    protected $casts = [
        'inputs' => 'array',
        'snapshot' => 'array',
        'total_payable' => 'decimal:2',
        'total_recoverable' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'finalized_at' => 'datetime',
    ];

    public function termination(): BelongsTo
    {
        return $this->belongsTo(EmployeeTermination::class, 'employee_termination_id');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(HrSettlementLineItem::class, 'settlement_form_id');
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by')->withoutGlobalScopes();
    }

    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by')->withoutGlobalScopes();
    }
}
