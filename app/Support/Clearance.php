<?php

namespace App\Support;

use App\Models\AssetAssignment;
use App\Models\AssetReturnForm;
use App\Models\Company;
use App\Models\EmployeeTermination;
use App\Models\HrOffboardingCase;
use App\Models\HrSettlementForm;

/**
 * Canonical labels and gate logic shared by the IT / Finance clearance screens,
 * their controller issue actions, the offboarding hub and the branded PDFs.
 */
class Clearance
{
    /** Finance verification checklist (FC form section 5) - answered Yes / No / N/A. */
    public const FINANCE_VERIFICATION = [
        'Employment record and last working day confirmed',
        'Basic salary / wage basis verified against payroll setup',
        'End-of-service benefit calculated per the stated policy version',
        'Leave balance reconciled with approved leave records',
        'All salary advances and staff loans accounted for',
        'Asset return / recovery forms received and reconciled',
        'Payroll stop instruction issued for the final period',
        'GOSI / statutory contributions updated',
        'Bank account details for the net payment confirmed',
    ];

    /** IT clearance-level data & security confirmation - each must be ticked. */
    public const IT_DATA_SECURITY = [
        'company_data_secured'   => 'Company data on all devices backed up / transferred',
        'accounts_disabled'      => 'Email, VPN and system accounts disabled or delegated',
        'licences_reclaimed'     => 'Software licences reclaimed / reassigned',
        'access_revoked'         => 'Building / VPN / application access revoked',
        'inventory_updated'      => 'Asset register updated for every returned item',
    ];

    public const FINANCE_DECISIONS = [
        'cleared'               => 'Cleared - no outstanding dues',
        'cleared_with_recovery' => 'Cleared with recovery - deducted from final settlement',
        'not_cleared'           => 'Not cleared - balance outstanding',
    ];

    public const IT_DECISIONS = [
        'granted'          => 'Asset clearance granted',
        'pending_recovery' => 'Granted with recovery outstanding',
    ];

    public const PAYMENT_METHODS = ['Bank transfer', 'Cheque', 'Cash', 'Payroll (WPS)', 'Other'];

    /** HR-0111 section 3 - handover of duties, documents & company property. */
    public const HR_HANDOVER = [
        'Job handover note completed and accepted',
        'Pending tasks, projects and deadlines transferred',
        'Files, records and customer contacts handed over',
        'Email and system access delegated / disabled',
        'Company ID badge returned',
        'Access cards, keys and locker cleared',
        'Uniform, PPE, tools and equipment returned',
        'Company SIM / mobile line closed or transferred',
        'Company vehicle and fuel card returned',
        'Company accommodation vacated',
        'Authorisations / powers of attorney revoked',
        'Exit interview conducted',
    ];

    /** HR-0111 section 4 - statutory & government actions (KSA). */
    public const HR_STATUTORY = [
        'Qiwa contract termination registered',
        'GOSI deregistration submitted',
        'Muqeem / Absher records updated',
        'Iqama cancelled or sponsorship transferred',
        'Final exit visa issued / exit-re-entry cancelled',
        'Work permit closed with MHRSD',
        'Medical insurance (CCHI) cancelled',
        "Dependants' visas / iqamas addressed",
        'Final payment processed through WPS / Mudad',
        'Traffic / Absher violations checked and cleared',
        'Personnel file archived per retention policy',
    ];

    /** HR-0111 section 5 - leave, entitlements & documents due to the employee. */
    public const HR_ENTITLEMENTS = [
        'leave_entitled'            => ['Annual leave entitled (days)', 'number'],
        'leave_availed'             => ['Leave availed (days)', 'number'],
        'leave_balance'            => ['Leave balance (days)', 'number'],
        'leave_encashment_due'      => ['Leave encashment due', 'yesno'],
        'unauthorised_absence_days' => ['Unauthorised absence (days)', 'number'],
        'eosb_eligibility'          => ['EOSB eligibility', 'eosb'],
        'repatriation_ticket_due'   => ['Repatriation ticket due', 'yesno'],
        'service_certificate'       => ['Service certificate', 'issuedna'],
        'experience_letter'         => ['Experience letter', 'issuedna'],
    ];

    public const HR_DECISIONS = [
        'cleared'                 => 'Cleared - all departments signed, no outstanding items',
        'cleared_with_conditions' => 'Cleared with conditions - see remarks',
        'not_cleared'             => 'Not cleared - items outstanding',
    ];

    /** Finalised settlement for this termination, or null. */
    public static function finalSettlement(EmployeeTermination $termination): ?HrSettlementForm
    {
        return HrSettlementForm::query()
            ->where('employee_termination_id', $termination->id)
            ->where('status', HrSettlementForm::STATUS_FINAL)
            ->latest('id')
            ->first();
    }

    /**
     * The blade + data for a clearance-document PDF. Shared by the controller
     * download actions and the "email to the employee" job so they never drift.
     *
     * @param  'finance'|'it'|'hr'  $kind
     * @return array{0:string,1:array<string,mixed>,2:string}  [view, data, referencePrefix]
     */
    public static function letterView(string $kind, EmployeeTermination $termination): array
    {
        $employee = $termination->employee()->with([
            'employeeDetail.designation', 'employeeDetail.department', 'branch',
        ])->first();
        $company = $employee ? Company::find($employee->company_id) : null;

        if ($kind === 'it') {
            $returnedForms = AssetReturnForm::with(['asset', 'serial'])
                ->where('employee_id', $termination->user_id)
                ->where('status', AssetReturnForm::STATUS_FINAL)
                ->orderBy('id')->get();

            return ['employees.pdf.it-clearance-letter', [
                'employee' => $employee,
                'termination' => $termination,
                'returnedForms' => $returnedForms,
                'clearanceData' => $termination->it_clearance_data ?? [],
                'company' => $company,
            ], 'ITC-' . str_pad((string) $termination->id, 4, '0', STR_PAD_LEFT)];
        }

        if ($kind === 'hr') {
            $case = $termination->offboardingCase;
            $case?->load(['tasks', 'employee.employeeDetail.designation', 'employee.employeeDetail.department', 'employee.branch']);
            $settlement = self::finalSettlement($termination)
                ?? HrSettlementForm::where('employee_termination_id', $termination->id)->first();

            return ['hr-lifecycle.clearance-pdf', [
                'case' => $case,
                'employee' => $employee,
                'settlement' => $settlement,
                'company' => $company,
            ], 'HR-' . str_pad((string) ($case?->id ?? $termination->id), 4, '0', STR_PAD_LEFT)];
        }

        $settlement = self::finalSettlement($termination);
        $settlement?->loadMissing('lineItems', 'preparedBy', 'finalizedBy');

        return ['employees.pdf.finance-clearance-letter', [
            'employee' => $employee,
            'termination' => $termination,
            'settlement' => $settlement,
            'clearanceData' => $termination->finance_clearance_data ?? [],
            'company' => $company,
        ], 'FC-' . str_pad((string) $termination->id, 4, '0', STR_PAD_LEFT)];
    }

    /**
     * Pre-issue hint for the offboarding hub / screens.
     *
     * @return array{ready: bool, blockers: array<int, string>}
     */
    public static function financeHint(EmployeeTermination $termination): array
    {
        $blockers = [];

        if (!self::finalSettlement($termination)) {
            $blockers[] = 'Finance settlement is not finalised yet.';
        }

        if (($termination->finance_clearance_data['checklist'] ?? null) === null) {
            $blockers[] = 'Verification checklist not completed on the Finance Clearance screen.';
        }

        return ['ready' => $blockers === [], 'blockers' => $blockers];
    }

    /**
     * @return array{ready: bool, blockers: array<int, string>}
     */
    public static function itHint(int $employeeId): array
    {
        $pending = AssetAssignment::where('employee_id', $employeeId)
            ->where('status', AssetAssignment::STATUS_ASSIGNED)
            ->count();

        $blockers = [];
        if ($pending > 0) {
            $blockers[] = $pending . ' company asset(s) still to be returned & inspected.';
        }

        return ['ready' => $blockers === [], 'blockers' => $blockers];
    }

    /**
     * Issue-time validation for Finance clearance. Returns an error string, or
     * null when the clearance may be issued.
     *
     * @param  array<string, mixed>  $payload  normalised finance_clearance_data
     */
    public static function financeIssueError(EmployeeTermination $termination, array $payload, string $decision): ?string
    {
        if (!self::finalSettlement($termination)) {
            return 'A finalised finance settlement is required before issuing Finance clearance.';
        }

        $checklist = $payload['checklist'] ?? [];
        foreach (self::FINANCE_VERIFICATION as $i => $label) {
            $answer = $checklist[$i]['answer'] ?? null;
            if (!in_array($answer, ['yes', 'no', 'na'], true)) {
                return 'Answer every row of the finance verification checklist before issuing.';
            }
        }

        if (!array_key_exists($decision, self::FINANCE_DECISIONS)) {
            return 'Select a clearance decision.';
        }

        if ($decision === 'not_cleared') {
            return 'Resolve the outstanding balance and change the decision before issuing.';
        }

        if (empty($payload['payment_method']) || empty($payload['bank_name']) || empty($payload['iban'])) {
            return 'Payment method, bank name and IBAN are required to issue Finance clearance.';
        }

        return null;
    }

    /**
     * Issue-time validation for IT clearance.
     *
     * @param  array<string, mixed>  $payload  normalised it_clearance_data
     */
    public static function itIssueError(int $employeeId, array $payload, string $decision): ?string
    {
        $pending = AssetAssignment::where('employee_id', $employeeId)
            ->where('status', AssetAssignment::STATUS_ASSIGNED)
            ->exists();

        if ($pending) {
            return 'Return and inspect every assigned asset before issuing IT clearance.';
        }

        foreach (array_keys(self::IT_DATA_SECURITY) as $key) {
            if (empty($payload['confirm'][$key])) {
                return 'Confirm every IT data & security point before issuing.';
            }
        }

        if (!array_key_exists($decision, self::IT_DECISIONS)) {
            return 'Select an IT clearance decision.';
        }

        return null;
    }

    /**
     * Pre-issue hint for the HR clearance stage.
     *
     * @return array{ready: bool, blockers: array<int, string>}
     */
    public static function hrHint(HrOffboardingCase $case): array
    {
        $blockers = [];
        if (($case->hr_clearance_status ?? 'pending') !== 'issued') {
            $blockers[] = 'HR clearance form not completed / issued.';
        }
        return ['ready' => $blockers === [], 'blockers' => $blockers];
    }

    /**
     * Issue-time validation for the HR clearance form.
     *
     * @param  array<string, mixed>  $payload  normalised hr_clearance_data
     */
    public static function hrIssueError(array $payload, string $decision): ?string
    {
        foreach (self::HR_HANDOVER as $i => $label) {
            if (!in_array($payload['handover'][$i]['result'] ?? null, ['done', 'na'], true)) {
                return 'Answer every handover checklist row before issuing.';
            }
        }

        foreach (self::HR_STATUTORY as $i => $label) {
            $row = $payload['statutory'][$i] ?? [];
            if (!in_array($row['result'] ?? null, ['done', 'na'], true)) {
                return 'Answer every statutory / government action row before issuing.';
            }
            if (($row['result'] ?? null) === 'done' && trim((string) ($row['reference'] ?? '')) === '') {
                return 'Add a reference number for each completed statutory action.';
            }
        }

        if (empty($payload['declaration_acknowledged'])) {
            return 'The employee declaration must be acknowledged.';
        }

        $email = trim((string) ($payload['personal_email'] ?? ''));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'A valid personal email is required (the clearance documents are sent there).';
        }

        if (!array_key_exists($decision, self::HR_DECISIONS)) {
            return 'Select an HR clearance decision.';
        }
        if ($decision === 'not_cleared') {
            return 'Resolve the outstanding items and change the decision before issuing.';
        }

        return null;
    }
}
