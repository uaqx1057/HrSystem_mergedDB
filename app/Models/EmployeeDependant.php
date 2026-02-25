<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeDependant extends Model
{
    protected $fillable = [
        'employee_id',
        'name',
        'iqama_no',
        'relation',
        'date_of_birth',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}