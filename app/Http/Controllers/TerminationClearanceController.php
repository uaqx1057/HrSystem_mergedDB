<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Helper\Reply;
use App\Mail\TerminationIssueClearedMail;
use App\Mail\TerminationReminderMail;
use App\Models\AdvanceSalary;
use App\Models\AssetAssignment;
use App\Models\AssetReturnForm;
use App\Models\EmployeeAssessLoss;
use App\Models\EmployeeTermination;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Services\AssetReturnService;
use App\Support\Clearance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TerminationClearanceController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.pendingTermination';
    }

    private function findTermination($id)
    {
        $employee = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);

        $termination = EmployeeTermination::where('user_id', $id)
            ->where('status', EmployeeTermination::STATUS_PENDING)
            ->latest('id')
            ->first();

        if (!$termination) {
            abort(404, 'No open offboarding exists for this employee.');
        }

        return [$employee, $termination];
    }

    private function checkPermission($permissionName, User $employee)
    {
        $permission = user()->permission($permissionName);

        abort_403(!(
            $permission == 'all'
            || ($permission == 'branch' && !is_null(user()->branch_id) && $employee->branch_id == user()->branch_id)
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | IT Department
    |--------------------------------------------------------------------------
    */

    public function itView($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_it_clearance', $employee);

        $this->employee = $employee;
        $this->termination = $termination;
        $this->assignedAssets = AssetAssignment::with(['asset', 'serial'])
            ->where('employee_id', $employee->id)
            ->where('status', AssetAssignment::STATUS_ASSIGNED)
            ->get();
        $this->returnedForms = AssetReturnForm::with(['asset', 'serial'])
            ->where('employee_id', $employee->id)
            ->where('status', AssetReturnForm::STATUS_FINAL)
            ->latest('id')
            ->get();

        $this->checklist = AssetReturnService::CHECKLIST;
        $this->resultOptions = AssetReturnService::RESULT_OPTIONS;
        $this->itDataSecurity = Clearance::IT_DATA_SECURITY;
        $this->itDecisions = Clearance::IT_DECISIONS;
        $this->clearanceData = $termination->it_clearance_data ?? [];

        if (request()->ajax()) {
            $html = view('employees.ajax.it-clearance', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'IT Clearance']);
        }

        $this->view = 'employees.ajax.it-clearance';
        return view('employees.create', $this->data);
    }

    /**
     * Capture one asset's return + inspection from the IT clearance screen and
     * finalise its RT record through the shared asset-return service.
     */
    public function itReturnAsset(Request $request, $id, $assignmentId)
    {
        [$employee] = $this->findTermination($id);
        $this->checkPermission('manage_it_clearance', $employee);

        $assignment = AssetAssignment::with(['asset', 'serial'])
            ->where('employee_id', $employee->id)
            ->where('status', AssetAssignment::STATUS_ASSIGNED)
            ->findOrFail($assignmentId);

        $request->validate([
            'return_document'            => 'nullable|file|max:10240',
            'overall_grade'              => 'required|in:A,B,C,D',
            'damage_assessment'          => 'required|string|max:120',
            'disposition'                => 'nullable|string|max:120',
            'estimated_repair_cost'      => 'nullable|numeric|min:0',
            'replacement_value'          => 'nullable|numeric|min:0',
            'recommended_recovery_amount' => 'nullable|numeric|min:0',
            'recovery_reason'            => 'nullable|string|max:500',
            'deduct_from_settlement'     => 'nullable|in:yes,no',
            'inspector_notes'            => 'nullable|string|max:1000',
        ]);

        $documentPath = $request->hasFile('return_document')
            ? Files::uploadLocalOrS3($request->return_document, 'asset')
            : null;

        $dispositionSummary = implode(' | ', array_filter([
            'Overall grade: ' . $request->overall_grade,
            'Damage: ' . $request->damage_assessment,
            $request->filled('disposition') ? 'Disposition: ' . $request->disposition : null,
            $request->filled('estimated_repair_cost') ? 'Est. repair SAR ' . number_format((float) $request->estimated_repair_cost, 2) : null,
            $request->filled('replacement_value') ? 'Replacement value SAR ' . number_format((float) $request->replacement_value, 2) : null,
            $request->filled('inspector_notes') ? 'Notes: ' . $request->inspector_notes : null,
        ]));

        $recovery = $request->filled('recommended_recovery_amount') && (float) $request->recommended_recovery_amount > 0
            ? (float) $request->recommended_recovery_amount
            : null;

        $form = app(AssetReturnService::class)->certifyReturn($assignment, user()->id, [
            'return_document'             => $documentPath,
            'lines'                      => [
                'accessories' => $request->input('accessories', []),
                'technical'   => $request->input('technical', []),
                'data'        => $request->input('data', []),
            ],
            'disposition_notes'          => $dispositionSummary ?: null,
            'recommended_recovery_amount' => $recovery,
            'recovery_reason'            => $request->recovery_reason,
        ]);

        // Route the recovery to Finance so it lands on the settlement worksheet.
        if ($recovery && $request->deduct_from_settlement === 'yes') {
            $assessLoss = EmployeeAssessLoss::create([
                'company_asset_id'            => $form->company_asset_id,
                'employee_id'                 => $employee->id,
                'asset_assignment_history_id' => $form->asset_assignment_history_id,
                'loss_amount'                 => $recovery,
                'status'                      => EmployeeAssessLoss::STATUS_PENDING,
            ]);

            $financeUsers = User::usersWithPermission('manage_finance_clearance', $employee->company_id);
            foreach ($financeUsers as $financeUser) {
                if (!empty($financeUser->email)) {
                    try {
                        Mail::to($financeUser->email)->send(new \App\Mail\AssetLossDeductionMail($assessLoss, $form->asset, $assignment));
                    } catch (\Exception $e) {
                        Log::error('Failed to send asset loss deduction email: ' . $e->getMessage());
                    }
                }
            }
        }

        return Reply::successWithData('Asset return recorded (' . $form->reference . ').', [
            'redirectUrl' => route('employees.it-clearance', $employee->id),
        ]);
    }

    public function itSendReminder($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_it_clearance', $employee);

        $pendingAssets = AssetAssignment::with('asset')
            ->where('employee_id', $employee->id)
            ->where('status', AssetAssignment::STATUS_ASSIGNED)
            ->get();

        if ($pendingAssets->isEmpty()) {
            return Reply::error('All assets have already been returned.');
        }

        $assetNames = $pendingAssets->map(function ($assignment) {
            return $assignment->asset->name ?? 'Asset';
        })->implode(', ');

        $reasonMessage = 'You still have the following company asset(s) pending return: ' . $assetNames . '. Please return them at the earliest to proceed with your IT clearance.';

        $recipients = collect([$employee])
            ->merge(User::allAdmins($employee->company_id))
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationReminderMail($termination, 'IT', $reasonMessage));
            } catch (\Exception $e) {
                Log::error('Failed to send IT clearance reminder email: ' . $e->getMessage());
            }
        }

        $termination->it_reminder_sent_at = now();
        $termination->save();

        return Reply::success('Reminder sent successfully.');
    }

    public function itIssueClearance(Request $request, $id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_it_clearance', $employee);

        $decision = (string) $request->input('it_clearance_decision');
        $payload = [
            'confirm' => collect(array_keys(Clearance::IT_DATA_SECURITY))
                ->mapWithKeys(fn ($key) => [$key => $request->boolean('confirm.' . $key)])
                ->all(),
            'inspector_name' => (string) $request->input('inspector_name'),
            'it_remarks'     => (string) $request->input('it_remarks'),
        ];

        if ($error = Clearance::itIssueError($employee->id, $payload, $decision)) {
            return Reply::error($error);
        }

        $issued = DB::transaction(function () use ($employee, $termination, $payload, $decision) {
            $termination = EmployeeTermination::query()->whereKey($termination->id)->lockForUpdate()->firstOrFail();

            $pendingAssets = AssetAssignment::where('employee_id', $employee->id)
                ->where('status', AssetAssignment::STATUS_ASSIGNED)
                ->lockForUpdate()
                ->exists();

            if ($pendingAssets) {
                return false;
            }

            $termination->update([
                'it_clearance_status'    => EmployeeTermination::CLEARANCE_ISSUED,
                'it_clearance_issued_by' => user()->id,
                'it_clearance_issued_at' => now(),
                'it_clearance_data'      => $payload,
                'it_clearance_decision'  => $decision,
            ]);

            return true;
        });

        if (!$issued) {
            return Reply::error('Asset return is pending.');
        }

        $termination->refresh();

        $recipients = collect(User::usersWithPermission('manage_termination_employees', $employee->company_id))
            ->whereNotNull('email')
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationIssueClearedMail($termination, 'IT'));
            } catch (\Exception $e) {
                Log::error('Failed to send IT issue cleared email: ' . $e->getMessage());
            }
        }

        return Reply::successWithData('IT clearance issued.', [
            'redirectUrl' => route('employees.it-clearance', $employee->id),
        ]);
    }

    public function itClearanceLetterPdf($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_it_clearance', $employee);

        if ($termination->it_clearance_status !== EmployeeTermination::CLEARANCE_ISSUED) {
            return redirect()->back()->with('error', 'Asset return is pending.');
        }

        $employee->loadMissing('employeeDetail.designation', 'employeeDetail.department', 'branch');
        $returnedForms = AssetReturnForm::with(['asset', 'serial'])
            ->where('employee_id', $employee->id)
            ->where('status', AssetReturnForm::STATUS_FINAL)
            ->orderBy('id')
            ->get();

        $pdf = Pdf::loadView('employees.pdf.it-clearance-letter', [
            'employee'      => $employee,
            'termination'   => $termination,
            'returnedForms' => $returnedForms,
            'clearanceData' => $termination->it_clearance_data ?? [],
            'company'       => company(),
        ])->setPaper('letter');

        return $pdf->download('it-clearance-' . $employee->id . '.pdf');
    }

    /*
    |--------------------------------------------------------------------------
    | Finance Department
    |--------------------------------------------------------------------------
    */

    public function financeView($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_finance_clearance', $employee);

        $this->employee = $employee;
        $this->termination = $termination;
        $this->settlement = Clearance::finalSettlement($termination);
        $this->settlement?->loadMissing('lineItems');

        $this->pendingAdvances = AdvanceSalary::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereColumn('deducted_amount', '<', 'advance_salary')
            ->get();

        $this->assetDeductions = EmployeeAssessLoss::with(['companyAsset', 'employee', 'assetLoss'])->where('employee_id', $id)
            ->where('status', EmployeeAssessLoss::STATUS_PENDING)
            ->whereColumn('deducted_amount', '<', 'loss_amount')
            ->get();

        $this->financeChecklist = Clearance::FINANCE_VERIFICATION;
        $this->financeDecisions = Clearance::FINANCE_DECISIONS;
        $this->paymentMethods = Clearance::PAYMENT_METHODS;
        $this->clearanceData = $termination->finance_clearance_data ?? [];

        if (request()->ajax()) {
            $html = view('employees.ajax.finance-clearance', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => 'Finance Clearance']);
        }

        $this->view = 'employees.ajax.finance-clearance';
        return view('employees.create', $this->data);
    }

    public function financeSendReminder($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_finance_clearance', $employee);

        $pendingAdvances = AdvanceSalary::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereColumn('deducted_amount', '<', 'advance_salary')
            ->get();

        $pendingAssetDeductions = EmployeeAssessLoss::with(['companyAsset', 'employee', 'assetLoss'])->where('employee_id', $employee->id)
            ->where('status', EmployeeAssessLoss::STATUS_PENDING)
            ->whereColumn('deducted_amount', '<', 'loss_amount')
            ->get();

        if ($pendingAdvances->isEmpty() && $pendingAssetDeductions->isEmpty()) {
            return Reply::error('No pending dues found.');
        }

        $totalAdvance = $pendingAdvances->sum('advance_salary');
        $totalDeducted = $pendingAdvances->sum('deducted_amount');
        $totalAssetIssue = $pendingAssetDeductions->sum('loss_amount');
        $totalAssetDeducted = $pendingAssetDeductions->sum('deducted_amount');
        $totalDue = ($totalAdvance + $totalAssetIssue) - ($totalDeducted + $totalAssetDeducted);

        $reasonMessage = 'You have an outstanding advance salary / due amount of ' . number_format($totalDue, 2) . '. Please clear the pending dues at the earliest to proceed with your Finance clearance.';

        $recipients = collect([$employee])
            ->merge(User::allAdmins($employee->company_id))
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationReminderMail($termination, 'Finance', $reasonMessage));
            } catch (\Exception $e) {
                Log::error('Failed to send Finance clearance reminder email: ' . $e->getMessage());
            }
        }

        $termination->finance_reminder_sent_at = now();
        $termination->save();

        return Reply::success('Reminder sent successfully.');
    }

    public function financeIssueClearance(Request $request, $id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_finance_clearance', $employee);

        $decision = (string) $request->input('finance_clearance_decision');

        $payload = [
            'checklist' => collect(Clearance::FINANCE_VERIFICATION)->map(function ($label, $i) use ($request) {
                return [
                    'label'   => $label,
                    'answer'  => (string) $request->input('checklist.' . $i . '.answer'),
                    'remarks' => (string) $request->input('checklist.' . $i . '.remarks'),
                ];
            })->all(),
            'payment_method'       => (string) $request->input('payment_method'),
            'payment_method_other' => (string) $request->input('payment_method_other'),
            'bank_name'            => (string) $request->input('bank_name'),
            'iban'                 => (string) $request->input('iban'),
            'prepared_by'          => (string) $request->input('prepared_by'),
            'verified_by'          => (string) $request->input('verified_by'),
            'finance_remarks'      => (string) $request->input('finance_remarks'),
        ];

        if ($error = Clearance::financeIssueError($termination, $payload, $decision)) {
            return Reply::error($error);
        }

        // A plain "cleared" decision still requires no undeducted advances / losses;
        // "cleared with recovery" is allowed to proceed with the recovery recorded.
        if ($decision === 'cleared') {
            $pendingDues = AdvanceSalary::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereColumn('deducted_amount', '<', 'advance_salary')
                ->exists();

            $pendingAssetDeductions = EmployeeAssessLoss::where('employee_id', $employee->id)
                ->where('status', EmployeeAssessLoss::STATUS_PENDING)
                ->whereColumn('deducted_amount', '<', 'loss_amount')
                ->exists();

            if ($pendingDues || $pendingAssetDeductions) {
                return Reply::error('Outstanding advances / asset recoveries remain - use "Cleared with recovery" or settle them first.');
            }
        }

        DB::transaction(function () use ($termination, $payload, $decision) {
            $termination->forceFill([
                'finance_clearance_status'    => EmployeeTermination::CLEARANCE_ISSUED,
                'finance_clearance_issued_by' => user()->id,
                'finance_clearance_issued_at' => now(),
                'finance_clearance_data'      => $payload,
                'finance_clearance_decision'  => $decision,
            ])->save();
        });

        $recipients = collect(User::usersWithPermission('manage_termination_employees', $employee->company_id))
            ->whereNotNull('email')
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationIssueClearedMail($termination, 'Finance'));
            } catch (\Exception $e) {
                Log::error('Failed to send Finance issue cleared email: ' . $e->getMessage());
            }
        }

        return Reply::successWithData('Finance clearance issued.', [
            'redirectUrl' => route('employees.finance-clearance', $employee->id),
        ]);
    }

    public function financeClearanceLetterPdf($id)
    {
        [$employee, $termination] = $this->findTermination($id);

        $this->checkPermission('manage_finance_clearance', $employee);

        if ($termination->finance_clearance_status !== EmployeeTermination::CLEARANCE_ISSUED) {
            return redirect()->back()->with('error', 'Financial clearance is pending.');
        }

        $employee->loadMissing('employeeDetail.designation', 'employeeDetail.department', 'branch');
        $settlement = Clearance::finalSettlement($termination);
        $settlement?->loadMissing('lineItems', 'preparedBy', 'finalizedBy');

        $pdf = Pdf::loadView('employees.pdf.finance-clearance-letter', [
            'employee'      => $employee,
            'termination'   => $termination,
            'settlement'    => $settlement,
            'clearanceData' => $termination->finance_clearance_data ?? [],
            'company'       => company(),
        ])->setPaper('letter');

        return $pdf->download('finance-clearance-' . $employee->id . '.pdf');
    }
}
