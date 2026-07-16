<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
