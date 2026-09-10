<?php

namespace App\Http\Controllers;

use App\Models\HrAttendanceException;
use App\Models\HrCertification;
use App\Models\HrCertificationRule;
use App\Models\HrEmployeeRequest;
use App\Models\HrOffboardingCase;
use App\Models\HrOnboardingCase;
use App\Models\HrProbationReview;
use App\Models\AssetReturnForm;
use App\Models\HrAssetCustodyRecord;
use App\Models\HrSystemSyncJob;
use App\Models\User;
use App\Support\HrDocumentExpiry;
use App\Traits\AuthorizesHrAccess;

class HrWorklistController extends AccountBaseController
{
    use AuthorizesHrAccess;

    public function index()
    {
        $this->pageTitle = 'HR worklist';
        $this->authorizeHrPermission('edit_employees', ['all', 'branch']);

        $company = user()->company_id;
        $employees = User::allEmployees(null, false, 'all', $company)->load('employeeDetail', 'visa');
        $missingCertificationCount = HrCertificationRule::missingForEmployees(
            $employees,
            HrCertificationRule::where('company_id', $company)->where('is_active', true)->get(),
            HrCertification::where('company_id', $company)->get()
        )->count();
        $expiryCount = HrDocumentExpiry::forEmployees($employees, $company)->count();

        $this->items = collect();
        foreach ([
            ['Onboarding', HrOnboardingCase::where('company_id', $company)->where('status', 'open')->count()],
            ['Offboarding', HrOffboardingCase::where('company_id', $company)->where('status', 'open')->count()],
            ['Offboarding awaiting approval', HrOffboardingCase::where('company_id', $company)->where('approval_status', 'awaiting_approval')->whereIn('status', ['open', 'completion_pending'])->count()],
            ['Attendance exceptions', HrAttendanceException::where('company_id', $company)->where('status', 'pending')->count()],
            ['Employee requests', HrEmployeeRequest::where('company_id', $company)->where('status', 'pending')->count()],
            ['Probation reviews', HrProbationReview::where('company_id', $company)->where('status', 'pending')->count()],
            ['Expired certifications', HrCertification::where('company_id', $company)->whereDate('expires_at', '<', today())->count()],
            ['Missing required certifications', $missingCertificationCount],
            ['Pending asset recovery', AssetReturnForm::where('company_id', $company)->whereIn('recovery_status', ['not_required', 'partially_approved'])->where('recommended_recovery_amount', '>', 0)->count()],
            ['IT return certifications', $this->pendingItCertificationCount()],
            ['Failed linked-system sync', HrSystemSyncJob::where('status', HrSystemSyncJob::STATUS_FAILED)->whereHas('employee', fn ($query) => $query->where('company_id', $company))->count()],
            ['Documents expired or expiring in 90 days', $expiryCount],
        ] as [$label, $count]) {
            $this->items->push(compact('label', 'count'));
        }

        return view('hr-worklist.index', $this->data);
    }

    private function pendingItCertificationCount(): int
    {
        $query = HrAssetCustodyRecord::whereNotNull('returned_at')->whereNull('certified_at');
        if (user()->permission('manage_it_clearance') === 'branch') {
            $query->whereHas('assignment.asset', fn ($assetQuery) => $assetQuery->where('branch_id', user()->branch_id));
        }

        return $query->count();
    }
}
