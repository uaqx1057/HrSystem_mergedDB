<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetRecoveryAllocation extends Model
{
    public const METHOD_PAYROLL = 'payroll';
    public const METHOD_SETTLEMENT = 'settlement';
    public const METHOD_WAIVER = 'waiver';
    public const METHOD_EXTERNAL_PAYMENT = 'external_payment';

    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function returnForm(): BelongsTo
    {
        return $this->belongsTo(AssetReturnForm::class, 'asset_return_form_id');
    }

    public function assessLoss(): BelongsTo
    {
        return $this->belongsTo(EmployeeAssessLoss::class, 'employee_assess_loss_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withoutGlobalScopes();
    }
}
