<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CoordinatorReportFieldValue extends BaseModel
{
    use HasFactory;

    public function field(): BelongsTo
    {
        return $this->belongsTo(BusinessField::class, 'field_id', 'id');
    }
}
