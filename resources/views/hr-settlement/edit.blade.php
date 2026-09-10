@extends('layouts.app')

@php
    $isFinal = $settlement && $settlement->status === \App\Models\HrSettlementForm::STATUS_FINAL;
    $val = function ($key, $default = 0) use ($settlement, $suggested) {
        return old($key, data_get($settlement?->inputs, $key, $suggested[$key] ?? $default));
    };
    $meta = $suggested['_meta'] ?? [];
    $payableFields = [
        'eosb_amount'      => ['End-of-service benefit', 'Article 84 / 85', true],
        'leave_encashment' => ['Unused leave encashment', 'Article 111', true],
        'pending_salary'   => ['Pending salary to last day', 'From last payslip → LWD', true],
        'notice_payment'   => ['Notice period payment in lieu', 'Unserved notice after LWD', true],
        'manual_payable'   => ['Other payable', 'Bonus, reimbursements, etc.', false],
    ];
    $recoverableFields = [
        'advance_balance' => ['Salary advances outstanding', 'Approved, undeducted', true],
        'asset_recovery'  => ['Asset loss / damage recovery', 'From approved RT allocations', true],
        'manual_recovery' => ['Other recovery', 'Fines, overpaid salary, etc.', false],
    ];
@endphp

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div>
            <h4 class="mb-0">Finance settlement &mdash; {{ $termination->employee?->name }}</h4>
            <span class="text-muted small">
                {{ ucfirst((string) $termination->exit_type) }} &middot;
                joined {{ data_get($meta, 'joining_date', '—') }} &middot;
                last day {{ data_get($meta, 'last_working_date', '—') }} &middot;
                {{ $suggested['service_years'] ?? 0 }} yrs service
            </span>
        </div>
        <span class="badge badge-pill p-2 badge-{{ $isFinal ? 'success' : ($settlement ? 'warning' : 'secondary') }}">
            {{ $isFinal ? 'Finalised '.optional($settlement->finalized_at)->format('d M Y') : ($settlement ? 'Draft' : 'Not started') }}
        </span>
    </div>

    <div class="alert alert-warning py-2">
        <i class="fa fa-exclamation-triangle mr-1"></i>
        <strong>Provisional.</strong> Auto-derived figures must be reviewed by Finance before finalisation.
        EOSB / leave amounts follow policy version <code>{{ $suggested['policy_version'] ?? 'provisional' }}</code>.
    </div>

    <div class="row">
        <div class="col-lg-8">
            @if(!$isFinal)
            <form method="POST" action="{{ route('hr-settlement.worksheet', $termination->id) }}">
                @csrf
                <input type="hidden" name="monthly_wage" value="{{ $val('monthly_wage') }}">
                <input type="hidden" name="wage_basis" value="{{ $val('wage_basis', 'gross') }}">
                <input type="hidden" name="service_years" value="{{ $val('service_years') }}">

                <div class="card mb-3"><div class="card-body">
                    <div class="form-row align-items-end">
                        <div class="col-md-6 mb-2">
                            <label class="small text-muted mb-1">Policy version</label>
                            <input class="form-control form-control-sm" name="policy_version"
                                   value="{{ $val('policy_version', $suggested['policy_version'] ?? 'provisional-2026-09-09') }}" required>
                        </div>
                        <div class="col-md-6 mb-2">
                            <label class="small text-muted mb-1">Leave balance (days)</label>
                            <input class="form-control form-control-sm" type="number" step="0.01" min="0"
                                   name="leave_balance_days" value="{{ $val('leave_balance_days') }}">
                            <small class="text-muted">system {{ $suggested['leave_balance_days'] ?? 0 }}d &middot; daily wage SAR {{ number_format((float) data_get($meta, 'daily_wage', 0), 2) }}</small>
                        </div>
                    </div>
                </div></div>

                @foreach([['Amounts payable to employee', 'success', $payableFields], ['Amounts recoverable from employee', 'danger', $recoverableFields]] as [$groupLabel, $groupColour, $fields])
                    <div class="card mb-3"><div class="card-body">
                        <h6 class="text-{{ $groupColour }} mb-2">{{ $groupLabel }}</h6>
                        <table class="table table-sm mb-0">
                            <thead class="thead-light"><tr><th style="width:48%">Item</th><th style="width:20%">Amount (SAR)</th><th>System</th></tr></thead>
                            <tbody>
                            @foreach($fields as $key => [$label, $hint, $isAuto])
                                <tr class="{{ $isAuto ? 'bg-light' : '' }}">
                                    <td>
                                        {{ $label }}
                                        @if($isAuto)<span class="badge badge-light border ml-1"><i class="fa fa-magic mr-1"></i>auto</span>@endif
                                        <div class="small text-muted">{{ $hint }}</div>
                                    </td>
                                    <td>
                                        <input class="form-control form-control-sm text-right" type="number" step="0.01" min="0"
                                               name="{{ $key }}" value="{{ $val($key) }}">
                                    </td>
                                    <td class="text-muted small align-middle">{{ number_format((float) ($suggested[$key] ?? 0), 2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div></div>
                @endforeach

                <button class="btn btn-primary"><i class="fa fa-save mr-1"></i> Save worksheet</button>
                <a class="btn btn-light" href="{{ route('hr-settlement.edit', $termination->id) }}"><i class="fa fa-refresh mr-1"></i> Recompute from source</a>
            </form>
            @else
                <div class="alert alert-success"><i class="fa fa-lock mr-1"></i> This settlement is finalised and cannot be changed.</div>
            @endif
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-uppercase small text-muted">Settlement summary</h6>
                    @if($settlement)
                        <table class="table table-sm mb-2">
                            <tr><td>Total payable (B)</td><td class="text-right">{{ number_format((float) $settlement->total_payable, 2) }}</td></tr>
                            <tr><td>Total recoverable (A)</td><td class="text-right">{{ number_format((float) $settlement->total_recoverable, 2) }}</td></tr>
                            <tr class="font-weight-bold border-top"><td>Net (B &minus; A)</td><td class="text-right h5 mb-0 text-{{ (float) $settlement->net_amount < 0 ? 'danger' : 'success' }}">{{ number_format((float) $settlement->net_amount, 2) }}</td></tr>
                        </table>
                        @if(!$isFinal)
                            <form method="POST" action="{{ route('hr-settlement.finalize', $termination->id) }}"
                                  onsubmit="return confirm('Finalise this settlement? It becomes an immutable record and generates the FC form.');">
                                @csrf<button class="btn btn-success btn-block btn-sm"><i class="fa fa-check-circle mr-1"></i> Finalise settlement</button>
                            </form>
                        @else
                            <a class="btn btn-outline-secondary btn-block btn-sm" href="{{ route('hr-settlement.pdf', $termination->id) }}">
                                <i class="fa fa-file-pdf-o mr-1"></i> Download FC form (PDF)
                            </a>
                        @endif
                    @else
                        <p class="text-muted small mb-0">Save the worksheet to see the net settlement.</p>
                    @endif

                    <hr>
                    <h6 class="text-uppercase small text-muted">EOSB basis</h6>
                    <p class="small mb-1">{{ data_get($meta, 'eosb_basis', '—') }}</p>
                    <p class="small text-muted mb-0">
                        Monthly wage ({{ $suggested['wage_basis'] ?? 'gross' }}): SAR {{ number_format((float) ($suggested['monthly_wage'] ?? 0), 2) }}<br>
                        Full Art. 84 award: SAR {{ number_format((float) data_get($meta, 'award_full', 0), 2) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
