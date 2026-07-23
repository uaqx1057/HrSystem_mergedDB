<?php
namespace App\Http\Controllers;
use App\Models\HrAttendanceException;
use App\Models\User;
use App\Services\HrAccess;
use Illuminate\Http\Request;
class HrAttendanceExceptionController extends AccountBaseController {
 public function index() { $this->own = HrAttendanceException::with('employee')->where('employee_id', user()->id)->latest()->get(); $this->pending = HrAttendanceException::with('employee')->where('status','pending')->latest()->get()->filter(fn($item) => $this->canReview($item)); return view('hr-attendance-exceptions.index',$this->data); }
 public function store(Request $request) { $data=$request->validate(['type'=>'required|in:missing_punch,late_arrival,overtime,field_work','attendance_date'=>'required|date','start_time'=>'nullable|date_format:H:i','end_time'=>'nullable|date_format:H:i','reason'=>'required|string|max:2000']); HrAttendanceException::create($data+['company_id'=>user()->company_id,'employee_id'=>user()->id]); return redirect()->route('hr-attendance-exceptions.index')->with('success','Attendance exception submitted.'); }
 public function review(Request $request, HrAttendanceException $exception) { $data=$request->validate(['status'=>'required|in:approved,rejected','review_note'=>'nullable|string|max:2000']); abort_403($exception->status !== 'pending' || !$this->canReview($exception)); $exception->update($data+['reviewed_by'=>user()->id,'reviewed_at'=>now()]); return redirect()->route('hr-attendance-exceptions.index')->with('success','Attendance exception reviewed.'); }
 private function canReview(HrAttendanceException $exception): bool { $permission=user()->permission('edit_attendance'); return HrAccess::canActAsLeaveManager(user(), $exception->employee) || (in_array($permission,['all','branch'],true) && HrAccess::canAccessEmployeeBranch(user(), $exception->employee, 'attendance')); }
}
