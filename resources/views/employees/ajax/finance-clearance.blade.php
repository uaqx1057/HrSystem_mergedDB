@php
    /** @var \App\Models\EmployeeTermination $termination */
    $issued   = $termination && $termination->finance_clearance_status === 'issued';
    $hasFinal = !is_null($settlement);
    $saved    = $clearanceData ?? [];
    $savedChecklist = $saved['checklist'] ?? [];
    $cur = static fn ($key, $default = '') => data_get($saved, $key, $default);

    $curSym  = company()->currency->currency_symbol ?? '';
    $payable      = $hasFinal ? $settlement->lineItems->where('kind', 'payable') : collect();
    $recoverable  = $hasFinal ? $settlement->lineItems->where('kind', 'recoverable') : collect();
    $totalB = $hasFinal ? (float) $settlement->total_payable : 0.0;
    $totalA = $hasFinal ? (float) $settlement->total_recoverable : 0.0;
    $net    = $hasFinal ? (float) $settlement->net_amount : 0.0;
@endphp

<div id="finance-clearance-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header form-heading-background border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-8 col-8"><h3 class="heading-h1 mb-0">Finance Clearance &amp; Dues Settlement</h3></div>
                        <div class="col-md-4 col-4 text-right">
                            @if ($issued)
                                <span class="badge badge-success p-2">Issued &mdash; {{ \App\Support\Clearance::FINANCE_DECISIONS[$termination->finance_clearance_decision] ?? ucfirst((string) $termination->finance_clearance_decision) }}</span>
                            @else
                                <span class="badge badge-warning p-2">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-right d-flex justify-content-end mb-3">
                        <a href="{{ route('employees.index') }}?tab=pending-offboard" class="btn btn-sm btn-secondary">Back</a>
                        @unless ($issued)
                            <a href="javascript:;" class="btn btn-sm btn-warning send-finance-reminder ml-2">
                                <i class="fa fa-bell mr-1"></i> Send Reminder
                            </a>
                        @endunless
                        @if ($issued)
                            <a href="{{ route('employees.finance-clearance.letter', $employee->id) }}" class="btn btn-sm btn-primary ml-2" target="_blank">
                                <i class="fa fa-file-pdf-o mr-1"></i> Download FC Letter
                            </a>
                        @endif
                    </div>

                    {{-- 1. Employee --}}
                    <h5 class="heading-h4 mb-2">1. Employee</h5>
                    <x-cards.data-row :label="__('modules.employees.employeeId')" :value="$employee->employeeDetail->employee_id ?? '--'" />
                    <x-cards.data-row :label="__('modules.employees.fullName')" :value="$employee->name" />
                    <x-cards.data-row :label="__('app.designation')" :value="$employee->employeeDetail->designation->name ?? '--'" />
                    <x-cards.data-row :label="__('app.department')" :value="$employee->employeeDetail->department->team_name ?? '--'" />
                    <x-cards.data-row label="Last working day" :value="optional($termination->last_working_date)->format(company()->date_format) ?? '--'" />

                    {{-- Settlement summary --}}
                    <h5 class="heading-h4 mt-4 mb-2">2 &ndash; 4. Amounts &amp; net settlement</h5>
                    @unless ($hasFinal)
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle mr-1"></i>
                            The finance settlement for this employee is <strong>not finalised</strong>. Prepare and finalise it
                            first &mdash; the payable / recoverable amounts and the net settlement come from there.
                            <a href="{{ route('hr-settlement.edit', $termination->id) }}" class="btn btn-sm btn-success ml-2">
                                <i class="fa fa-money mr-1"></i> Prepare settlement
                            </a>
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light"><tr><th>Recoverable from employee</th><th class="text-right">{{ $curSym }} Amount</th></tr></thead>
                                    <tbody>
                                        @forelse ($recoverable as $li)
                                            <tr><td>{{ $li->description }}</td><td class="text-right">{{ number_format((float) $li->amount, 2) }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="text-muted">None</td></tr>
                                        @endforelse
                                        <tr class="font-weight-bold bg-light"><td>Total recoverable (A)</td><td class="text-right">{{ number_format($totalA, 2) }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm table-bordered">
                                    <thead class="thead-light"><tr><th>Payable to employee</th><th class="text-right">{{ $curSym }} Amount</th></tr></thead>
                                    <tbody>
                                        @forelse ($payable as $li)
                                            <tr><td>{{ $li->description }}</td><td class="text-right">{{ number_format((float) $li->amount, 2) }}</td></tr>
                                        @empty
                                            <tr><td colspan="2" class="text-muted">None</td></tr>
                                        @endforelse
                                        <tr class="font-weight-bold bg-light"><td>Total payable (B)</td><td class="text-right">{{ number_format($totalB, 2) }}</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="alert {{ $net < 0 ? 'alert-danger' : 'alert-success' }} py-2">
                            <strong>Net settlement (B &minus; A): {{ $curSym }} {{ number_format($net, 2) }}</strong>
                            &mdash; {{ $net > 0 ? 'Payable to employee' : ($net < 0 ? 'Recoverable from employee' : 'Nil - fully settled') }}
                            <a href="{{ route('hr-settlement.edit', $termination->id) }}" class="float-right small">view settlement</a>
                        </div>
                    @endunless

                    <form id="finance-clearance-form" autocomplete="off">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">

                        {{-- 5. Verification checklist --}}
                        <h5 class="heading-h4 mt-4 mb-2">5. Finance verification checklist</h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead class="thead-light">
                                    <tr><th style="width:44%">Verification point</th><th class="text-center" style="width:26%">Yes / No / N/A</th><th>Remarks</th></tr>
                                </thead>
                                <tbody>
                                    @foreach ($financeChecklist as $i => $label)
                                        @php $ans = $savedChecklist[$i]['answer'] ?? ''; @endphp
                                        <tr>
                                            <td class="align-middle">{{ $i + 1 }}. {{ $label }}</td>
                                            <td class="text-center align-middle">
                                                @foreach (['yes' => 'Yes', 'no' => 'No', 'na' => 'N/A'] as $v => $t)
                                                    <label class="mr-2 mb-0 f-13">
                                                        <input type="radio" name="checklist[{{ $i }}][answer]" value="{{ $v }}" @checked($ans === $v) @disabled($issued)> {{ $t }}
                                                    </label>
                                                @endforeach
                                            </td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="checklist[{{ $i }}][remarks]"
                                                       value="{{ $savedChecklist[$i]['remarks'] ?? '' }}" @disabled($issued)>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Payment & bank --}}
                        <h5 class="heading-h4 mt-4 mb-2">Payment &amp; bank details</h5>
                        <div class="form-row">
                            <div class="col-md-3 mb-2">
                                <label class="f-13 mb-1">Payment method</label>
                                <select name="payment_method" class="form-control form-control-sm" @disabled($issued)>
                                    <option value="">--</option>
                                    @foreach ($paymentMethods as $m)
                                        <option value="{{ $m }}" @selected($cur('payment_method') === $m)>{{ $m }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="f-13 mb-1">If "Other"</label>
                                <input type="text" name="payment_method_other" class="form-control form-control-sm" value="{{ $cur('payment_method_other') }}" @disabled($issued)>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="f-13 mb-1">Bank name</label>
                                <input type="text" name="bank_name" class="form-control form-control-sm" value="{{ $cur('bank_name') }}" @disabled($issued)>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label class="f-13 mb-1">IBAN</label>
                                <input type="text" name="iban" class="form-control form-control-sm" value="{{ $cur('iban') }}" @disabled($issued)>
                            </div>
                        </div>

                        {{-- 6. Decision --}}
                        <h5 class="heading-h4 mt-4 mb-2">6. Clearance decision</h5>
                        <div class="mb-2">
                            @foreach ($financeDecisions as $v => $t)
                                <label class="d-block f-13 mb-1">
                                    <input type="radio" name="finance_clearance_decision" value="{{ $v }}" @checked($termination->finance_clearance_decision === $v) @disabled($issued)>
                                    {{ $t }}
                                </label>
                            @endforeach
                        </div>
                        <div class="form-row">
                            <div class="col-md-4 mb-2">
                                <label class="f-13 mb-1">Prepared by (Accounts)</label>
                                <input type="text" name="prepared_by" class="form-control form-control-sm" value="{{ $cur('prepared_by') }}" @disabled($issued)>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="f-13 mb-1">Verified by (Finance Mgr)</label>
                                <input type="text" name="verified_by" class="form-control form-control-sm" value="{{ $cur('verified_by') }}" @disabled($issued)>
                            </div>
                            <div class="col-md-4 mb-2">
                                <label class="f-13 mb-1">Finance remarks</label>
                                <input type="text" name="finance_remarks" class="form-control form-control-sm" value="{{ $cur('finance_remarks') }}" @disabled($issued)>
                            </div>
                        </div>

                        @unless ($issued)
                            <button type="button" class="btn btn-primary mt-2" id="issue-finance-clearance" @disabled(!$hasFinal)>
                                <i class="fa fa-check mr-1"></i> Issue Finance Clearance
                            </button>
                            @unless ($hasFinal)
                                <span class="text-muted f-13 ml-2">Finalise the settlement to enable this.</span>
                            @endunless
                        @else
                            <div class="alert alert-success mt-2 mb-0">
                                <i class="fa fa-lock mr-1"></i> Issued on
                                {{ optional($termination->finance_clearance_issued_at)->format(company()->date_format . ' H:i') }}
                                by {{ $termination->financeClearanceIssuedBy->name ?? '--' }}.
                            </div>
                        @endunless
                    </form>

                    {{-- Informational: raw dues / asset losses --}}
                    <h5 class="heading-h4 mt-4 mb-2">Reference &mdash; open advances &amp; asset recoveries</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Date</th><th>Advance (remaining)</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse ($pendingAdvances as $advance)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($advance->date)->translatedFormat(company()->date_format) }}</td>
                                        <td>{{ $curSym }}{{ number_format($advance->advance_salary - $advance->deducted_amount, 2) }}</td>
                                        <td>{{ ucfirst($advance->status) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-muted">No open advances.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>Asset</th><th>Serial</th><th>Loss</th><th>Deducted</th><th>Remaining</th><th>Status</th></tr></thead>
                            <tbody>
                                @forelse ($assetDeductions as $d)
                                    <tr>
                                        <td>{{ optional($d->companyAsset)->name }}</td>
                                        <td>{{ optional($d->assetLoss)->serial_no }}</td>
                                        <td>{{ number_format((float) $d->loss_amount, 2) }}</td>
                                        <td>{{ number_format((float) $d->deducted_amount, 2) }}</td>
                                        <td>{{ number_format((float) $d->loss_amount - (float) $d->deducted_amount, 2) }}</td>
                                        <td>{{ ucfirst($d->status) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-muted">No open asset recoveries.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').off('click', '.send-finance-reminder').on('click', '.send-finance-reminder', function () {
        $.easyAjax({
            type: 'POST',
            url: "{{ route('employees.finance-clearance.reminder', $employee->id) }}",
            blockUI: true,
            data: { '_token': "{{ csrf_token() }}" }
        });
    });

    $('body').off('click', '#issue-finance-clearance').on('click', '#issue-finance-clearance', function () {
        Swal.fire({
            title: 'Issue Finance clearance?',
            text: 'The verification checklist, bank details and decision below will be locked into the record.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, issue',
            customClass: { confirmButton: 'btn btn-primary mr-2', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.easyAjax({
                type: 'POST',
                url: "{{ route('employees.finance-clearance.issue', $employee->id) }}",
                blockUI: true,
                disableButton: true,
                buttonSelector: '#issue-finance-clearance',
                data: $('#finance-clearance-form').serialize(),
                success: function (res) {
                    if (res.status === 'success') { window.location.href = res.redirectUrl || window.location.href; }
                }
            });
        });
    });
</script>
