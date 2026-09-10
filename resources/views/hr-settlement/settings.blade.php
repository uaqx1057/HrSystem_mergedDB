@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-0">End-of-service &amp; settlement policy</h4>
    </div>

    <div class="alert alert-warning py-2">
        <i class="fa fa-shield mr-1"></i>
        <strong>Administrator access only.</strong> This drives every future auto-calculated settlement.
        Confirm each figure with HR / legal and validate against a few already-paid settlements before relying
        on finalised settlements.
    </div>

    <form method="POST" action="{{ route('hr-settlement.settings.save') }}">
        @csrf

        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted">General</h6>
            <div class="form-row">
                <div class="col-md-4 mb-2">
                    <label class="small text-muted mb-1">Wage basis for EOSB &amp; leave</label>
                    <select class="form-control form-control-sm" name="eosb_wage_basis">
                        @foreach(['basic' => 'Basic salary only', 'basic_housing' => 'Basic + housing', 'gross' => 'Basic + housing + travel (gross)'] as $k => $lbl)
                            <option value="{{ $k }}" @selected($settings->eosb_wage_basis === $k)>{{ $lbl }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small text-muted mb-1">Policy version</label>
                    <input class="form-control form-control-sm" name="policy_version" value="{{ $settings->policy_version }}" required>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small text-muted mb-1">Leave daily-wage divisor</label>
                    <input class="form-control form-control-sm" type="number" name="leave_daily_wage_divisor" value="{{ $settings->leave_daily_wage_divisor }}" min="1" max="31" required>
                </div>
            </div>
        </div></div>

        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted">Article 84 &mdash; award per year of service (month-fractions of wage)</h6>
            <div class="form-row">
                <div class="col-md-4 mb-2">
                    <label class="small text-muted mb-1">First 5 years <span class="text-muted">(statutory 0.5)</span></label>
                    <input class="form-control form-control-sm" type="number" step="0.0001" name="award_first_5yr_month_fraction" value="{{ $settings->award_first_5yr_month_fraction }}" required>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small text-muted mb-1">After 5 years <span class="text-muted">(statutory 1.0)</span></label>
                    <input class="form-control form-control-sm" type="number" step="0.0001" name="award_after_5yr_month_fraction" value="{{ $settings->award_after_5yr_month_fraction }}" required>
                </div>
                <div class="col-md-4 mb-2 d-flex align-items-center">
                    <label class="mb-0 small"><input type="checkbox" name="termination_gets_full_award" value="1" @checked($settings->termination_gets_full_award)>
                        Employer termination pays the full award <span class="text-muted d-block">(uncheck only for Article 80 dismissals)</span></label>
                </div>
            </div>
        </div></div>

        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted">Article 85 &mdash; resignation entitlement (fraction of the Article 84 award)</h6>
            <div class="form-row">
                @foreach([
                    'resign_under_2yr_fraction' => ['Under 2 years', '0'],
                    'resign_2_to_5yr_fraction'  => ['2 to under 5 years', '1/3'],
                    'resign_5_to_10yr_fraction' => ['5 to under 10 years', '2/3'],
                    'resign_10yr_plus_fraction' => ['10 years and over', 'full'],
                ] as $field => [$lbl, $statutory])
                    <div class="col-md-3 mb-2">
                        <label class="small text-muted mb-1">{{ $lbl }} <span class="text-muted">(statutory {{ $statutory }})</span></label>
                        <input class="form-control form-control-sm" type="number" step="0.0001" min="0" max="1" name="{{ $field }}" value="{{ $settings->$field }}" required>
                    </div>
                @endforeach
            </div>
        </div></div>

        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted">Leave encashment</h6>
            <div class="form-row">
                <div class="col-md-6 mb-2 d-flex align-items-center">
                    <label class="mb-0 small"><input type="checkbox" name="encash_leave_on_exit" value="1" @checked($settings->encash_leave_on_exit)>
                        Encash unused leave on exit (Article 111)</label>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="small text-muted mb-1">Default annual leave days (when no quota is set)</label>
                    <input class="form-control form-control-sm" type="number" name="default_annual_leave_days" value="{{ $settings->default_annual_leave_days }}" min="0" max="60" required>
                </div>
            </div>
            <div class="form-group mt-2">
                <label class="small text-muted mb-1">Notes</label>
                <textarea class="form-control form-control-sm" name="notes" rows="2">{{ $settings->notes }}</textarea>
            </div>
        </div></div>

        <button class="btn btn-primary"><i class="fa fa-save mr-1"></i> Save policy</button>
    </form>
</div>
@endsection
