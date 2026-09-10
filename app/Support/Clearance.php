<?php

namespace App\Support;

use App\Models\AssetAssignment;
use App\Models\EmployeeTermination;
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
}
