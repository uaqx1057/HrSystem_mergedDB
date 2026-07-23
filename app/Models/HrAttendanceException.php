<?php
namespace App\Models;
class HrAttendanceException extends BaseModel { protected $table = 'hr_attendance_exceptions'; protected $guarded = ['id']; protected $casts = ['attendance_date' => 'date', 'reviewed_at' => 'datetime']; public function employee() { return $this->belongsTo(User::class, 'employee_id'); } public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); } }
