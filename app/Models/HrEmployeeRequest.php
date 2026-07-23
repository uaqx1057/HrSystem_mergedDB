<?php
namespace App\Models; class HrEmployeeRequest extends BaseModel { protected $table='hr_employee_requests'; protected $guarded=['id']; protected $casts=['reviewed_at'=>'datetime']; public function employee(){return $this->belongsTo(User::class,'employee_id');}}
