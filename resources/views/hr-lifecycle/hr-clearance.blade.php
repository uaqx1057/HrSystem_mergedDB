@extends('layouts.app')

@php
    /** @var \App\Models\HrOffboardingCase $case */
    $issued  = ($case->hr_clearance_status ?? 'pending') === 'issued';
    $d       = $hrData ?? [];
    $detail  = $employee->employeeDetail;
    $isSaudi = ($detail?->employee_type ?: 'expat') === 'saudi';
    $inputs  = $settlementDraft && is_array($settlementDraft->inputs) ? $settlementDraft->inputs : [];
    $ent     = $d['entitlements'] ?? [];
    // Section-5 prefills come from the settlement worksheet when the field is blank.
    $entVal = function ($key) use ($ent, $inputs) {
        if (($ent[$key] ?? '') !== '') return $ent[$key];
        return match ($key) {
            'leave_balance'  => $inputs['leave_balance_days'] ?? '',
            default          => '',
        };
    };
    $doneAns = ['done' => 'Done', 'na' => 'N/A'];
@endphp

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
        <div>
            <h4 class="mb-0">HR Clearance &amp; Offboarding &mdash; {{ $employee->name }}</h4>
            <span class="text-muted small">
                {{ $case->reference }} &middot; {{ ucfirst((string) $case->exit_type) }}
                &middot; last day {{ optional($case->last_working_date)->format('d M Y') }}
            </span>
        </div>
        <div class="text-right">
            <span class="badge badge-pill p-2 badge-{{ $issued ? 'success' : 'warning' }}">
                {{ $issued ? 'ISSUED - ' . (\App\Support\Clearance::HR_DECISIONS[$case->hr_clearance_decision] ?? $case->hr_clearance_decision) : 'PENDING' }}
            </span><br>
            <a class="btn btn-sm btn-outline-primary mt-2" href="{{ route('hr-lifecycle.offboarding.console', $case->id) }}">Back to console</a>
            @if ($issued)
                <a class="btn btn-sm btn-primary mt-2" target="_blank" href="{{ route('hr-lifecycle.offboarding.clearance-pdf', $case->id) }}"><i class="fa fa-file-pdf-o mr-1"></i> HR-0111 PDF</a>
            @endif
        </div>
    </div>

    <form id="hr-clearance-form" autocomplete="off">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">

        {{-- 1. Separation details --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted mb-2">1. Employee &amp; separation details</h6>
            <table class="table table-sm w-auto mb-2">
                <tr><td class="text-muted pr-4">Employee</td><td>{{ $employee->name }} ({{ $detail?->employee_id ?: '--' }})</td></tr>
                <tr><td class="text-muted pr-4">Designation / Dept</td><td>{{ $detail?->designation?->name ?: '--' }} / {{ $detail?->department?->team_name ?: '--' }}</td></tr>
                <tr><td class="text-muted pr-4">{{ $isSaudi ? 'National ID' : 'Iqama No.' }}</td><td>{{ ($isSaudi ? $detail?->national_id : $detail?->iqama_no) ?: '--' }}</td></tr>
                <tr><td class="text-muted pr-4">Joining date</td><td>{{ optional($detail?->joining_date)->format('d M Y') ?: '--' }}</td></tr>
                <tr><td class="text-muted pr-4">Separation</td><td>{{ ucfirst((string) $case->exit_type) }} &middot; {{ $case->notice_type === 'immediate' ? 'Immediate effect' : ($case->notice_months . '-month notice') }} &middot; last working day <strong>{{ optional($case->last_working_date)->format('d M Y') }}</strong></td></tr>
            </table>
            <div class="form-row">
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Contract type</label>
                    <select name="separation[contract_type]" class="form-control form-control-sm" @disabled($issued)>
                        @foreach (['Fixed term', 'Indefinite', 'Probation'] as $ct)
                            <option value="{{ $ct }}" @selected(($d['separation']['contract_type'] ?? '') === $ct)>{{ $ct }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Nationality</label>
                    <input type="text" name="separation[nationality]" class="form-control form-control-sm" value="{{ $d['separation']['nationality'] ?? ($employee->country?->nicename ?? '') }}" @disabled($issued)>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Total service (Y / M / D)</label>
                    <input type="text" name="separation[total_service]" class="form-control form-control-sm" value="{{ $d['separation']['total_service'] ?? '' }}" @disabled($issued)>
                </div>
            </div>
        </div></div>

        {{-- 2. Departmental clearance (read-only) --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted mb-2">2. Departmental clearance (from the offboarding case)</h6>
            <table class="table table-sm mb-0">
                <thead class="thead-light"><tr><th>Area</th><th>Owner</th><th>Status</th></tr></thead>
                <tbody>
                @foreach ($case->tasks as $task)
                    <tr class="{{ in_array($task->status, ['completed','waived']) ? 'table-success' : '' }}">
                        <td>{{ $task->title }}@unless($task->is_required)<span class="badge badge-light border ml-1">optional</span>@endunless</td>
                        <td>{{ ucfirst((string) $task->owner_type) }}</td>
                        <td>{{ ucfirst($task->status) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="small text-muted mb-0 mt-1">Tick these on the console's <a href="{{ route('hr-lifecycle.offboarding.console', $case->id) }}#tab-clearance">HR Clearance tab</a>.</p>
        </div></div>

        {{-- 3. Handover checklist --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted mb-2">3. Handover of duties, documents &amp; company property</h6>
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th style="width:40%">Item</th><th style="width:16%">Done / N/A</th><th style="width:22%">Handed over to</th><th>Remarks</th></tr></thead>
                <tbody>
                @foreach ($handover as $i => $label)
                    @php $row = $d['handover'][$i] ?? []; @endphp
                    <tr>
                        <td class="align-middle f-13">{{ $i + 1 }}. {{ $label }}</td>
                        <td class="text-center align-middle">
                            @foreach ($doneAns as $v => $t)
                                <label class="mr-2 mb-0 f-13"><input type="radio" name="handover[{{ $i }}][result]" value="{{ $v }}" @checked(($row['result'] ?? '') === $v) @disabled($issued)> {{ $t }}</label>
                            @endforeach
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="handover[{{ $i }}][handed_to]" value="{{ $row['handed_to'] ?? '' }}" @disabled($issued)></td>
                        <td><input type="text" class="form-control form-control-sm" name="handover[{{ $i }}][remarks]" value="{{ $row['remarks'] ?? '' }}" @disabled($issued)></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div></div>

        {{-- 4. Statutory & government actions --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted mb-2">4. Statutory &amp; government actions (KSA)</h6>
            <table class="table table-sm table-bordered mb-0">
                <thead class="thead-light"><tr><th style="width:40%">Action</th><th style="width:16%">Done / N/A</th><th style="width:24%">Reference no.</th><th>Date</th></tr></thead>
                <tbody>
                @foreach ($statutory as $i => $label)
                    @php $row = $d['statutory'][$i] ?? []; @endphp
                    <tr>
                        <td class="align-middle f-13">{{ $i + 1 }}. {{ $label }}</td>
                        <td class="text-center align-middle">
                            @foreach ($doneAns as $v => $t)
                                <label class="mr-2 mb-0 f-13"><input type="radio" name="statutory[{{ $i }}][result]" value="{{ $v }}" @checked(($row['result'] ?? '') === $v) @disabled($issued)> {{ $t }}</label>
                            @endforeach
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="statutory[{{ $i }}][reference]" value="{{ $row['reference'] ?? '' }}" @disabled($issued)></td>
                        <td><input type="date" class="form-control form-control-sm" name="statutory[{{ $i }}][date]" value="{{ $row['date'] ?? '' }}" @disabled($issued)></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            <p class="small text-muted mb-0 mt-1">A reference number is required for every action marked <strong>Done</strong>.</p>
        </div></div>

        {{-- 5. Leave & entitlements --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted mb-2">5. Leave, entitlements &amp; documents due</h6>
            <div class="form-row">
                @foreach ($entitlements as $key => [$label, $type])
                    <div class="col-md-4 mb-2">
                        <label class="small mb-1">{{ $label }}</label>
                        @if ($type === 'yesno')
                            <select name="entitlements[{{ $key }}]" class="form-control form-control-sm" @disabled($issued)>
                                <option value="">--</option>
                                <option value="yes" @selected($entVal($key) === 'yes')>Yes</option>
                                <option value="no" @selected($entVal($key) === 'no')>No</option>
                            </select>
                        @elseif ($type === 'eosb')
                            <select name="entitlements[{{ $key }}]" class="form-control form-control-sm" @disabled($issued)>
                                <option value="">--</option>
                                @foreach (['full' => 'Full', 'partial' => 'Partial', 'none' => 'None'] as $v => $t)
                                    <option value="{{ $v }}" @selected($entVal($key) === $v)>{{ $t }}</option>
                                @endforeach
                            </select>
                        @elseif ($type === 'issuedna')
                            <select name="entitlements[{{ $key }}]" class="form-control form-control-sm" @disabled($issued)>
                                <option value="">--</option>
                                <option value="issued" @selected($entVal($key) === 'issued')>Issued</option>
                                <option value="na" @selected($entVal($key) === 'na')>N/A</option>
                            </select>
                        @else
                            <input type="number" step="0.5" min="0" name="entitlements[{{ $key }}]" class="form-control form-control-sm" value="{{ $entVal($key) }}" @disabled($issued)>
                        @endif
                    </div>
                @endforeach
            </div>
            @if ($settlement)
                <p class="small text-muted mb-0">Net settlement (finalised): SAR {{ number_format((float) $settlement->net_amount, 2) }}.</p>
            @else
                <p class="small text-warning mb-0"><i class="fa fa-exclamation-triangle mr-1"></i> Finance settlement not finalised yet.</p>
            @endif
        </div></div>

        {{-- 6. Employee declaration --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted mb-2">6. Employee declaration &amp; contact</h6>
            <div class="form-row">
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Personal email <span class="text-danger">*</span></label>
                    <input type="email" name="personal_email" class="form-control form-control-sm" value="{{ $d['personal_email'] ?? ($detail?->personal_email ?? '') }}" @disabled($issued)>
                    <small class="text-muted">Clearance documents are emailed here.</small>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Contact number</label>
                    <input type="text" name="contact_number" class="form-control form-control-sm" value="{{ $d['contact_number'] ?? ($employee->mobile ? '+966 ' . $employee->mobile : '') }}" @disabled($issued)>
                </div>
                <div class="col-md-4 mb-2">
                    <label class="small mb-1">Forwarding address</label>
                    <input type="text" name="forwarding_address" class="form-control form-control-sm" value="{{ $d['forwarding_address'] ?? '' }}" @disabled($issued)>
                </div>
            </div>
            <label class="mb-0 f-13">
                <input type="checkbox" name="declaration_acknowledged" value="1" @checked(!empty($d['declaration_acknowledged'])) @disabled($issued)>
                The employee confirms all company property and duties are handed over, retains no company data, and authorises
                recovery of any verified dues from the final settlement.
            </label>
        </div></div>

        {{-- 7. HR decision --}}
        <div class="card mb-3"><div class="card-body">
            <h6 class="text-uppercase small text-muted mb-2">7. HR clearance decision</h6>
            <div class="mb-2">
                @foreach ($decisions as $v => $t)
                    <label class="d-block f-13 mb-1"><input type="radio" name="hr_clearance_decision" value="{{ $v }}" @checked($case->hr_clearance_decision === $v) @disabled($issued)> {{ $t }}</label>
                @endforeach
            </div>
            <div class="form-row">
                <div class="col-md-4 mb-2"><label class="small mb-1">HR officer</label><input type="text" name="hr_officer" class="form-control form-control-sm" value="{{ $d['hr_officer'] ?? '' }}" @disabled($issued)></div>
                <div class="col-md-4 mb-2"><label class="small mb-1">HR manager</label><input type="text" name="hr_manager" class="form-control form-control-sm" value="{{ $d['hr_manager'] ?? '' }}" @disabled($issued)></div>
                <div class="col-md-4 mb-2"><label class="small mb-1">HR remarks</label><input type="text" name="hr_remarks" class="form-control form-control-sm" value="{{ $d['hr_remarks'] ?? '' }}" @disabled($issued)></div>
            </div>

            @unless ($issued)
                <button type="button" class="btn btn-primary mt-2" id="issue-hr-clearance"><i class="fa fa-check mr-1"></i> Issue HR Clearance</button>
                <span class="text-muted f-13 ml-2">Offboarding cannot be completed until this is issued.</span>
            @else
                <div class="alert alert-success mt-2 mb-0"><i class="fa fa-lock mr-1"></i> Issued {{ optional($case->hr_cleared_at)->format('d M Y H:i') }}. The HR-0111 document has been emailed to the employee's personal address.</div>
            @endunless
        </div></div>
    </form>
</div>

<script>
    $('body').off('click', '#issue-hr-clearance').on('click', '#issue-hr-clearance', function () {
        Swal.fire({
            title: 'Issue HR clearance?',
            text: 'Everything entered above is locked into the HR-0111 record and emailed to the employee.',
            icon: 'question', showCancelButton: true, confirmButtonText: 'Yes, issue',
            customClass: { confirmButton: 'btn btn-primary mr-2', cancelButton: 'btn btn-secondary' }, buttonsStyling: false
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.easyAjax({
                type: 'POST',
                url: "{{ route('hr-lifecycle.offboarding.hr-clearance.issue', $case->id) }}",
                blockUI: true, disableButton: true, buttonSelector: '#issue-hr-clearance',
                data: $('#hr-clearance-form').serialize(),
                success: function (res) { if (res.status === 'success') { window.location.href = res.redirectUrl || window.location.href; } }
            });
        });
    });
</script>
@endsection
