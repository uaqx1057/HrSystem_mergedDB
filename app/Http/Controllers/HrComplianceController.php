<?php

namespace App\Http\Controllers;

use App\Models\HrCertification;
use App\Models\HrCertificationRule;
use App\Models\HrProbationReview;
use App\Models\HrRestrictedCase;
use App\Models\User;
use Illuminate\Http\Request;

class HrComplianceController extends AccountBaseController
{
    private function authorizeHr(){ abort_403(!in_array(user()->permission('edit_employees'), ['all'], true)); }
    public function index(){ $this->authorizeHr(); $company=user()->company_id; $this->employees=User::allEmployees(null,false,'all',$company)->load('employeeDetail'); $this->reviews=HrProbationReview::with('employee')->where('company_id',$company)->latest()->get(); $this->certifications=HrCertification::with('employee')->where('company_id',$company)->latest()->get(); $this->requiredCertificationGaps=HrCertificationRule::missingForEmployees($this->employees,HrCertificationRule::where('company_id',$company)->where('is_active',true)->get(),$this->certifications); $this->cases=HrRestrictedCase::where('company_id',$company)->latest()->get(); return view('hr-compliance.index',$this->data); }
    public function probation(Request $r){ $this->authorizeHr(); $d=$r->validate(['employee_id'=>'required|exists:users,id','review_day'=>'required|in:30,60,90','due_date'=>'required|date']); HrProbationReview::updateOrCreate(['employee_id'=>$d['employee_id'],'review_day'=>$d['review_day']],$d+['company_id'=>user()->company_id]); return back()->with('success','Probation review saved.'); }
    public function completeProbation(Request $r,HrProbationReview $review){ $this->authorizeHr(); abort_403($review->company_id!==user()->company_id); $d=$r->validate(['status'=>'required|in:confirmed,extended,completed','notes'=>'nullable|string|max:3000']); $review->update($d+['reviewed_by'=>user()->id]); return back()->with('success','Probation outcome saved.'); }
    public function certification(Request $r){ $this->authorizeHr(); $d=$r->validate(['employee_id'=>'required|exists:users,id','name'=>'required|string|max:255','expires_at'=>'nullable|date']); HrCertification::create($d+['company_id'=>user()->company_id]); return back()->with('success','Certification saved.'); }
    public function renewCertification(Request $r,HrCertification $certification){ $this->authorizeHr(); abort_403($certification->company_id!==user()->company_id); $d=$r->validate(['expires_at'=>'required|date','notes'=>'nullable|string|max:3000']); $certification->update($d+['status'=>'valid']); return back()->with('success','Certification renewed.'); }
    public function restrictedCase(Request $r){ $this->authorizeHr(); $d=$r->validate(['employee_id'=>'nullable|exists:users,id','category'=>'required|string|max:100','details'=>'required|string|max:5000']); HrRestrictedCase::create($d+['company_id'=>user()->company_id,'created_by'=>user()->id]); return back()->with('success','Restricted case created.'); }
    public function updateCase(Request $r,HrRestrictedCase $case){ $this->authorizeHr(); abort_403($case->company_id!==user()->company_id); $d=$r->validate(['status'=>'required|in:open,in_review,closed','assigned_to'=>'nullable|exists:users,id']); $case->update($d); return back()->with('success','Restricted case updated.'); }
}
