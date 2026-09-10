<?php

namespace App\Services;

use App\Models\EmployeeTermination;
use App\Models\HrSettlementForm;
use App\Models\HrSettlementSetting;
use App\Models\User;
use App\Scopes\ActiveScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class HrSettlementService
{
    public const POLICY_VERSION = 'provisional-2026-09-09';

    /** payable codes derived by the engine (the rest are manual). */
    private const AUTO_PAYABLE = ['eosb', 'leave_encashment', 'pending_salary', 'notice_payment'];
    private const AUTO_RECOVERABLE = ['advance_balance', 'asset_recovery'];

    private const LABELS = [
        'eosb' => 'End-of-service benefit (Art. 84/85)',
        'leave_encashment' => 'Unused leave encashment (Art. 111)',
        'pending_salary' => 'Pending salary to last working day',
        'notice_payment' => 'Notice period payment in lieu',
        'manual_payable' => 'Other payable (manual)',
        'advance_balance' => 'Salary advances outstanding',
        'asset_recovery' => 'Asset loss / damage recovery',
        'manual_recovery' => 'Other recovery (manual)',
    ];

    /**
     * Auto-derive every worksheet figure from source records. Finance reviews and
     * may override any value before saving. All amounts are provisional until the
     * company's EOSB policy is signed off by HR/legal.
     */
    public function suggestedInputs(EmployeeTermination $termination): array
    {
        $settings = HrSettlementSetting::forCompany((int) $termination->company_id);
        $employee = User::withoutGlobalScope(ActiveScope::class)->with('employeeDetail')->find($termination->user_id);
        $detail = $employee?->employeeDetail;

        $wage = $this->monthlyWage((int) $termination->company_id, (int) $termination->user_id, $settings->eosb_wage_basis);

        $join = $detail?->joining_date ? Carbon::parse($detail->joining_date) : null;
        $end = $termination->last_working_date
            ? Carbon::parse($termination->last_working_date)
            : ($termination->resignation_date ? Carbon::parse($termination->resignation_date) : Carbon::today());
        $serviceYears = $join ? max(0, $join->floatDiffInYears($end)) : 0.0;

        $eos = $this->computeEndOfService($wage, $serviceYears, (string) $termination->exit_type, $settings);
        $award = $eos['award_full'];
        $eosb = $eos['eosb'];

        $leaveDays = $this->leaveBalanceDays((int) $termination->user_id, $settings);
        $dailyWage = $settings->leave_daily_wage_divisor > 0 ? $wage / $settings->leave_daily_wage_divisor : 0.0;
        $leaveEncash = $settings->encash_leave_on_exit ? round($leaveDays * $dailyWage, 2) : 0.0;

        $advanceBalance = (float) DB::table('advance_salaries')
            ->where('employee_id', $termination->user_id)
            ->where('status', 'approved')
            ->whereColumn('deducted_amount', '<', 'advance_salary')
            ->sum(DB::raw('advance_salary - deducted_amount'));

        $assetRecovery = (float) DB::table('asset_recovery_allocations as a')
            ->join('asset_return_forms as f', 'f.id', '=', 'a.asset_return_form_id')
            ->where('f.employee_id', $termination->user_id)
            ->where('a.method', 'settlement')
            ->sum('a.amount');

        [$pendingSalary, $pendingFrom, $pendingDays] = $this->pendingSalary(
            (int) $termination->company_id, (int) $termination->user_id, $end, $dailyWage
        );

        $isResignation = $termination->exit_type === EmployeeTermination::EXIT_RESIGNATION;
        [$noticePayment, $noticeDays] = $this->noticePaymentInLieu($detail, $end, $dailyWage, $isResignation);

        return [
            'policy_version' => $settings->policy_version ?: self::POLICY_VERSION,
            'monthly_wage' => round($wage, 2),
            'wage_basis' => $settings->eosb_wage_basis,
            'service_years' => round($serviceYears, 2),
            'eosb_amount' => $eosb,
            'leave_balance_days' => $leaveDays,
            'leave_encashment' => $leaveEncash,
            'pending_salary' => $pendingSalary,
            'notice_payment' => $noticePayment,
            'manual_payable' => 0.0,
            'advance_balance' => round($advanceBalance, 2),
            'asset_recovery' => round($assetRecovery, 2),
            'manual_recovery' => 0.0,
            '_meta' => [
                'eosb_basis' => $eos['basis'],
                'award_full' => round($award, 2),
                'daily_wage' => round($dailyWage, 2),
                'joining_date' => $join?->toDateString(),
                'last_working_date' => $end->toDateString(),
                'pending_salary_basis' => $pendingDays > 0
                    ? sprintf('%d unpaid day(s) from %s to the last working day', $pendingDays, $pendingFrom)
                    : 'Salary paid up to the last working day',
                'notice_basis' => $noticeDays > 0
                    ? sprintf('%d unserved notice day(s) after the last working day (employer termination)', $noticeDays)
                    : 'No unserved notice payable',
                'provisional' => true,
            ],
        ];
    }

    /**
     * Salary earned but not yet paid: from the end of the last processed
     * monthly slip (or the start of the last working month) to the last day.
     *
     * @return array{0: float, 1: string, 2: int}
     */
    private function pendingSalary(int $companyId, int $userId, Carbon $end, float $dailyWage): array
    {
        $lastSlip = DB::table('salary_slips')
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->orderByRaw('year desc, month desc')
            ->first();

        $paidThrough = $lastSlip
            ? Carbon::create((int) $lastSlip->year, (int) $lastSlip->month, 1)->endOfMonth()
            : $end->copy()->startOfMonth()->subDay();

        if (!$paidThrough->lt($end)) {
            return [0.0, $paidThrough->toDateString(), 0];
        }

        $days = $paidThrough->diffInDays($end);

        return [round($dailyWage * $days, 2), $paidThrough->copy()->addDay()->toDateString(), $days];
    }

    /**
     * Payment in lieu of notice the employer did not let the employee serve
     * (notice period end date falls after the last working day).
     *
     * @return array{0: float, 1: int}
     */
    private function noticePaymentInLieu($detail, Carbon $end, float $dailyWage, bool $isResignation): array
    {
        if ($isResignation || !$detail?->notice_period_end_date) {
            return [0.0, 0];
        }

        $noticeEnd = Carbon::parse($detail->notice_period_end_date);
        if (!$noticeEnd->gt($end)) {
            return [0.0, 0];
        }

        $days = $end->diffInDays($noticeEnd);

        return [round($dailyWage * $days, 2), $days];
    }

    private function monthlyWage(int $companyId, int $userId, ?string $basis): float
    {
        $setup = DB::table('payroll_employee_setups')
            ->where('company_id', $companyId)->where('user_id', $userId)->first();

        if (!$setup) {
            return 0.0;
        }

        $basic = (float) ($setup->basic_salary ?? 0);
        $housing = (float) ($setup->housing_allowance ?? 0);
        $travel = (float) ($setup->travel_allowance ?? 0);

        return match ($basis) {
            'basic' => $basic,
            'basic_housing' => $basic + $housing,
            default => $basic + $housing + $travel,
        };
    }

    /**
     * Pure end-of-service calculation. No DB access - fully unit-testable.
     *
     * Article 84 award = wage-months per year of service (½ first 5, 1 after),
     * then Article 85 fraction for a resignation, or the full award for an
     * employer termination that is not on Article 80 grounds.
     *
     * @return array{award_full: float, eosb: float, fraction: float, basis: string}
     */
    public function computeEndOfService(float $wage, float $serviceYears, string $exitType, HrSettlementSetting $settings): array
    {
        $serviceYears = max(0.0, $serviceYears);
        $first = min($serviceYears, 5) * (float) $settings->award_first_5yr_month_fraction * $wage;
        $after = max($serviceYears - 5, 0) * (float) $settings->award_after_5yr_month_fraction * $wage;
        $awardFull = round($first + $after, 2);

        if ($exitType === EmployeeTermination::EXIT_RESIGNATION) {
            $fraction = $settings->resignationFraction($serviceYears);

            return [
                'award_full' => $awardFull,
                'eosb' => round($awardFull * $fraction, 2),
                'fraction' => $fraction,
                'basis' => sprintf('Art. 85 resignation — %.4f of the Art. 84 award for %.2f yrs', $fraction, $serviceYears),
            ];
        }

        $full = (bool) $settings->termination_gets_full_award;

        return [
            'award_full' => $awardFull,
            'eosb' => $full ? $awardFull : 0.0,
            'fraction' => $full ? 1.0 : 0.0,
            'basis' => $full
                ? 'Art. 84 full award (employer termination, not Art. 80)'
                : 'Art. 80 grounds — no award',
        ];
    }

    private function leaveBalanceDays(int $userId, HrSettlementSetting $settings): float
    {
        $quota = (float) DB::table('employee_leave_quotas')->where('user_id', $userId)->sum('no_of_leaves');
        if ($quota <= 0) {
            $quota = (float) $settings->default_annual_leave_days;
        }

        $takenThisYear = 0.0;
        try {
            $takenThisYear = (float) DB::table('leaves')
                ->where('user_id', $userId)
                ->where('status', 'approved')
                ->whereYear('leave_date', now()->year)
                ->count();
        } catch (\Throwable $e) {
            // leaves schema varies; fall back to the raw quota.
        }

        return max(0.0, round($quota - $takenThisYear, 2));
    }

    public function worksheet(EmployeeTermination $termination, int $actorId, array $inputs): HrSettlementForm
    {
        return DB::transaction(function () use ($termination, $actorId, $inputs) {
            $termination = EmployeeTermination::query()->whereKey($termination->id)->lockForUpdate()->firstOrFail();
            $form = HrSettlementForm::query()->firstOrNew(['employee_termination_id' => $termination->id]);

            if ($form->status === HrSettlementForm::STATUS_FINAL) {
                throw ValidationException::withMessages(['settlement' => 'A finalized settlement cannot be regenerated.']);
            }

            $payables = [
                'eosb' => (float) ($inputs['eosb_amount'] ?? 0),
                'leave_encashment' => (float) ($inputs['leave_encashment'] ?? 0),
                'pending_salary' => (float) ($inputs['pending_salary'] ?? 0),
                'notice_payment' => (float) ($inputs['notice_payment'] ?? 0),
                'manual_payable' => (float) ($inputs['manual_payable'] ?? 0),
            ];
            $recoverables = [
                'advance_balance' => (float) ($inputs['advance_balance'] ?? 0),
                'asset_recovery' => (float) ($inputs['asset_recovery'] ?? 0),
                'manual_recovery' => (float) ($inputs['manual_recovery'] ?? 0),
            ];

            $form->fill([
                'company_id' => $termination->company_id,
                'status' => HrSettlementForm::STATUS_DRAFT,
                'policy_version' => $inputs['policy_version'] ?? self::POLICY_VERSION,
                'inputs' => $inputs,
                'total_payable' => array_sum($payables),
                'total_recoverable' => array_sum($recoverables),
                'net_amount' => array_sum($payables) - array_sum($recoverables),
                'prepared_by' => $actorId,
            ])->save();

            $form->lineItems()->delete();
            foreach ($payables as $code => $amount) {
                if ($amount > 0) {
                    $form->lineItems()->create([
                        'code' => $code,
                        'kind' => 'payable',
                        'description' => self::LABELS[$code] ?? ucwords(str_replace('_', ' ', $code)),
                        'amount' => $amount,
                        'source_type' => in_array($code, self::AUTO_PAYABLE, true) ? 'auto' : null,
                    ]);
                }
            }
            foreach ($recoverables as $code => $amount) {
                if ($amount > 0) {
                    $form->lineItems()->create([
                        'code' => $code,
                        'kind' => 'recoverable',
                        'description' => self::LABELS[$code] ?? ucwords(str_replace('_', ' ', $code)),
                        'amount' => $amount,
                        'source_type' => in_array($code, self::AUTO_RECOVERABLE, true) ? 'auto' : null,
                    ]);
                }
            }

            return $form->fresh(['lineItems']);
        });
    }

    public function finalize(HrSettlementForm $form, int $actorId): HrSettlementForm
    {
        return DB::transaction(function () use ($form, $actorId) {
            $form = HrSettlementForm::query()->whereKey($form->id)->lockForUpdate()->firstOrFail();
            if ($form->status === HrSettlementForm::STATUS_FINAL) {
                return $form;
            }
            if (!$form->lineItems()->exists() && (float) $form->net_amount === 0.0) {
                throw ValidationException::withMessages(['settlement' => 'A settlement worksheet must contain reviewed amounts before finalization.']);
            }

            $form->update([
                'status' => HrSettlementForm::STATUS_FINAL,
                'finalized_by' => $actorId,
                'finalized_at' => now(),
                'snapshot' => [
                    'policy_version' => $form->policy_version,
                    'inputs' => $form->inputs,
                    'line_items' => $form->lineItems()->get()->toArray(),
                    'total_payable' => $form->total_payable,
                    'total_recoverable' => $form->total_recoverable,
                    'net_amount' => $form->net_amount,
                    'finalized_by' => $actorId,
                    'finalized_at' => now()->toIso8601String(),
                ],
            ]);

            return $form->fresh(['lineItems']);
        });
    }
    public function generatePdf(HrSettlementForm $settlement): HrSettlementForm
    {
        return DB::transaction(function () use ($settlement) {
            $settlement = HrSettlementForm::query()
                ->with([
                    'lineItems',
                    'termination.employee.employeeDetail.designation',
                    'termination.employee.employeeDetail.department',
                    'termination.employee.branch',
                    'preparedBy', 'finalizedBy',
                ])
                ->whereKey($settlement->id)->lockForUpdate()->firstOrFail();
            if ($settlement->status !== HrSettlementForm::STATUS_FINAL) {
                throw ValidationException::withMessages(['settlement' => 'Only finalized settlements can generate a PDF.']);
            }
            if ($settlement->pdf_path && $settlement->document_hash) {
                return $settlement;
            }
            $bytes = Pdf::loadView('hr-settlement.pdf', [
                'form' => $settlement,
                'company' => function_exists('company') ? company() : null,
            ])->setPaper('letter')->output();
            $path = 'hr-settlements/' . $settlement->company_id . '/' . $settlement->id . '.pdf';
            Storage::put($path, $bytes);
            $settlement->update(['pdf_path' => $path, 'document_hash' => hash('sha256', $bytes)]);
            return $settlement->fresh();
        });
    }
}
