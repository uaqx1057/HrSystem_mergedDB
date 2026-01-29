<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoordinatorReport extends BaseModel
{
    use HasFactory;

    protected $guarded = ['id', '_token', '_method'];

    protected $casts = [
        'report_date' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function field_values(): HasMany
    {
        return $this->hasMany(CoordinatorReportFieldValue::class, 'coordinator_report_id', 'id');
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
