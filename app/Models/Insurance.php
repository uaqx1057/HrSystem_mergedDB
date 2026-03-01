<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Insurance extends Model
{
    use HasFactory;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class, 'driver_id')->withoutGlobalScopes();
    }

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
    ];
}


