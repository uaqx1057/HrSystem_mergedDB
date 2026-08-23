<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Models\HrCandidateOnboardingTask;
use App\Services\CandidateOnboardingService;
use Illuminate\Http\Request;

class CandidateOnboardingController extends AccountBaseController
{
    public function updateTask(Request $request, int $task)
    {
        abort_403(!in_array(user()->permission('edit_employees'), ['all', 'branch'], true));

        $onboardingTask = HrCandidateOnboardingTask::with('case.candidate')->findOrFail($task);
        abort_403($onboardingTask->case->candidate->company_id != user()->company_id);

        $isComplete = $request->input('complete') == 1;

        $onboardingTask->update([
            'status' => $isComplete ? 'completed' : 'pending',
            'completed_at' => $isComplete ? now() : null,
        ]);

        app(CandidateOnboardingService::class)->syncCaseCompletion($onboardingTask->case_id);

        $message = 'Task updated.';

        return $request->ajax() ? Reply::success($message) : back()->with('success', $message);
    }
}