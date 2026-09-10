<?php

namespace App\Http\Controllers;

use App\Models\EmployeeTermination;
use App\Models\HrSettlementForm;
use App\Models\HrSettlementSetting;
use App\Models\User;
use App\Services\HrSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class HrSettlementController extends AccountBaseController
{
    public function settings()
    {
        $this->authorizePolicy();
        $this->settings = HrSettlementSetting::forCompany((int) user()->company_id);

        return view('hr-settlement.settings', $this->data);
    }

    public function saveSettings(Request $request)
    {
        $this->authorizePolicy();
        $data = $request->validate([
            'eosb_wage_basis' => 'required|in:basic,basic_housing,gross',
            'award_first_5yr_month_fraction' => 'required|numeric|min:0|max:12',
            'award_after_5yr_month_fraction' => 'required|numeric|min:0|max:12',
            'termination_gets_full_award' => 'nullable|boolean',
            'resign_under_2yr_fraction' => 'required|numeric|min:0|max:1',
            'resign_2_to_5yr_fraction' => 'required|numeric|min:0|max:1',
            'resign_5_to_10yr_fraction' => 'required|numeric|min:0|max:1',
            'resign_10yr_plus_fraction' => 'required|numeric|min:0|max:1',
            'encash_leave_on_exit' => 'nullable|boolean',
            'default_annual_leave_days' => 'required|integer|min:0|max:60',
            'leave_daily_wage_divisor' => 'required|integer|min:1|max:31',
            'policy_version' => 'required|string|max:100',
            'notes' => 'nullable|string|max:5000',
        ]);
        $data['termination_gets_full_award'] = $request->boolean('termination_gets_full_award');
        $data['encash_leave_on_exit'] = $request->boolean('encash_leave_on_exit');

        HrSettlementSetting::updateOrCreate(['company_id' => user()->company_id], $data);

        return back()->with('success', 'Settlement policy saved.');
    }

    public function edit($terminationId)
    {
        $this->authorizeFinance();
        $this->termination = EmployeeTermination::with('employee')->findOrFail($terminationId);
        abort_403($this->termination->employee?->company_id !== user()->company_id);
        $this->settlement = HrSettlementForm::query()->where('employee_termination_id', $this->termination->id)->with('lineItems')->first();
        $this->suggested = app(HrSettlementService::class)->suggestedInputs($this->termination);

        return view('hr-settlement.edit', $this->data);
    }

    public function worksheet(Request $request, $terminationId)
    {
        $this->authorizeFinance();
        $termination = EmployeeTermination::with('employee')->findOrFail($terminationId);
        abort_403($termination->employee?->company_id !== user()->company_id);
        $data = $request->validate([
            'policy_version' => 'required|string|max:100',
            'monthly_wage' => 'nullable|numeric|min:0',
            'wage_basis' => 'nullable|string|max:30',
            'service_years' => 'nullable|numeric|min:0',
            'leave_balance_days' => 'nullable|numeric|min:0',
            'eosb_amount' => 'nullable|numeric|min:0',
            'leave_encashment' => 'nullable|numeric|min:0',
            'pending_salary' => 'nullable|numeric|min:0',
            'notice_payment' => 'nullable|numeric|min:0',
            'manual_payable' => 'nullable|numeric|min:0',
            'advance_balance' => 'nullable|numeric|min:0',
            'asset_recovery' => 'nullable|numeric|min:0',
            'manual_recovery' => 'nullable|numeric|min:0',
        ]);

        app(HrSettlementService::class)->worksheet($termination, user()->id, $data);

        return back()->with('success', 'Settlement worksheet saved.');
    }

    public function pdf($terminationId)
    {
        $this->authorizeFinance();
        $termination = EmployeeTermination::with('employee')->findOrFail($terminationId);
        abort_403($termination->employee?->company_id !== user()->company_id);
        $settlement = HrSettlementForm::query()->where('employee_termination_id', $termination->id)->firstOrFail();
        $settlement = app(HrSettlementService::class)->generatePdf($settlement);
        return Storage::download($settlement->pdf_path, 'settlement-' . $settlement->id . '.pdf', ['Content-Type' => 'application/pdf']);
    }
    public function finalize($terminationId)
    {
        $this->authorizeFinance();
        $termination = EmployeeTermination::with('employee')->findOrFail($terminationId);
        abort_403($termination->employee?->company_id !== user()->company_id);
        $settlement = HrSettlementForm::query()->where('employee_termination_id', $termination->id)->firstOrFail();
        app(HrSettlementService::class)->finalize($settlement, user()->id);

        return back()->with('success', 'Settlement finalized.');
    }

    private function authorizeFinance(): void
    {
        abort_403(user()->permission('manage_finance_clearance') === 'none');
    }

    /**
     * Editing the EOSB / settlement policy is the "sign-off" gate: Administrator
     * role only. Give the CEO / MD the Administrator role, or widen this check
     * later. Preparing and finalising individual settlements stays at Finance
     * Manager level (authorizeFinance).
     */
    private function authorizePolicy(): void
    {
        abort_403(!in_array('admin', user_roles()));
    }
}
