<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdvanceSalary extends Model
{
    use HasFactory;

    protected $casts = [
        'date' => 'date',
        'advance_salary' => 'double',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }
}
