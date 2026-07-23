<?php
namespace App\Models; class HrPayrollPreflightRun extends BaseModel { protected $table='hr_payroll_preflight_runs'; protected $guarded=['id']; protected $casts=['summary'=>'array','approved_at'=>'datetime']; }
