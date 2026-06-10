<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeSystemAccess extends Model
{
    protected $table = 'employee_system_access';

    protected $fillable = [
        'employee_id', 'system', 'system_user_id',
        'role', 'is_active', 'provisioned_at',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}