<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeBankAccount extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'bank_name',
        'iban_number',
        'account_number',
        'swift_code',
        'is_main_account',
        'added_by',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }
}
