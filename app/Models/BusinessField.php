<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessField extends BaseModel
{
    use HasFactory;

    public $timestamps = false;

    protected $guarded = ['id', '_token', '_method'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}
