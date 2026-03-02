<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAllowance extends Model
{
    use HasFactory;
    // app/Models/EmployeeAllowance.php

    protected $fillable = ['employee_id', 'name', 'amount'];

    public function employeeAllowance()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
