<?php
namespace App\Models;
class HrEmployeeTransfer extends BaseModel { protected $table = 'hr_employee_transfers'; protected $guarded = ['id']; protected $casts = ['effective_date' => 'date', 'applied_at' => 'datetime']; }
