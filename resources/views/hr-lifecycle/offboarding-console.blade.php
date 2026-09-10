@extends('layouts.app')

@php
    $required = $case->tasks->where('is_required', true);
    $requiredDone = $required->whereIn('status', ['completed', 'waived'])->count();
    $requiredTotal = max($required->count(), 1);
    $requiredOpen = $requiredTotal - $requiredDone;
    $pct = (int) round(($requiredDone / $requiredTotal) * 100);
    $settlementFinal = $settlement && $settlement->status === \App\Models\HrSettlementForm::STATUS_FINAL;
    $accessRevoked = (bool) $case->access_revoked_at;
    $hrIssued = ($case->hr_clearance_status ?? 'pending') === 'issued';
    $cleared = $requiredOpen === 0 && $settlementFinal && $accessRevoked && $hrIssued;
    $done = fn ($t) => in_array($t->status, ['completed', 'waived']);

    $blockers = [];
    if ($requiredOpen > 0) $blockers[] = $requiredOpen . ' required clearance task(s) still open';
    if (!$settlementFinal) $blockers[] = 'final settlement not finalised';
    if (!$accessRevoked) $blockers[] = 'DMS/DOBS access revocation not yet confirmed';
    if (!$hrIssued) $blockers[] = 'HR Clearance form not issued';
@endphp

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-start flex-wrap mb-2">
        <div>
            <h4 class="mb-0">Offboarding &mdash; {{ $employee->name }}</h4>
            <span class="text-muted small">
                {{ $case->reference }} &middot; {{ ucfirst((string) $case->exit_type) }}
                &middot; last day {{ optional($case->last_working_date)->format('d M Y') }}
                &middot; {{ ucfirst(str_replace('_', ' ', $case->approval_status)) }}
            </span>
        </div>
        <div class="text-right">
            <span class="badge badge-pill p-2 badge-{{ $cleared ? 'success' : 'warning' }}">{{ $cleared ? 'READY TO COMPLETE' : 'IN PROGRESS' }}</span><br>
            <a class="btn btn-sm btn-outline-primary mt-2" target="_blank" href="{{ route('hr-lifecycle.offboarding.clearance-pdf', $case->id) }}"><i class="fa fa-file-pdf-o mr-1"></i> HR-0111 PDF</a>
        </div>
    </div>

    <div class="progress mb-1" style="height:6px;">
        <div class="progress-bar bg-{{ $cleared ? 'success' : 'info' }}" style="width: {{ $pct }}%"></div>
    </div>
    <p class="small text-muted">{{ $requiredDone }} of {{ $requiredTotal }} required clearances complete</p>

    @if($blockers)
        <div class="alert alert-warning py-2">
            <i class="fa fa-exclamation-triangle mr-1"></i> <strong>Not ready to complete.</strong> Outstanding: {{ implode('; ', $blockers) }}.
        </div>
    @else
        <div class="alert alert-success py-2"><i class="fa fa-check-circle mr-1"></i> All gates green &mdash; the offboarding can be completed from the employee's termination screen.</div>
    @endif

    <ul class="nav nav-tabs" role="tablist">
        @foreach(['overview' => 'Overview', 'clearance' => 'HR Clearance', 'finance' => 'Finance', 'assets' => 'Assets', 'statutory' => 'Statutory'] as $id => $label)
            <li class="nav-item"><a class="nav-link {{ $loop->first ? 'active' : '' }}" data-toggle="tab" href="#tab-{{ $id }}">{{ $label }}</a></li>
        @endforeach
    </ul>

    <div class="tab-content border border-top-0 p-3 bg-white">
        <div class="tab-pane fade show active" id="tab-overview">
            <table class="table table-sm w-auto mb-3">
                <tr><td class="text-muted pr-4">Approval</td><td>{{ ucfirst(str_replace('_', ' ', $case->approval_status)) }}</td></tr>
                <tr><td class="text-muted pr-4">Required clearances</td><td>{{ $requiredDone }} / {{ $requiredTotal }}</td></tr>
                <tr><td class="text-muted pr-4">Settlement</td><td>{{ $settlementFinal ? 'Finalised (net SAR '.number_format((float) $settlement->net_amount, 2).')' : ($settlement ? 'Draft' : 'Not started') }}</td></tr>
                <tr><td class="text-muted pr-4">DMS/DOBS access revoked</td><td>{{ $accessRevoked ? $case->access_revoked_at->format('d M Y H:i') : 'pending' }}</td></tr>
            </table>
            <h6 class="text-uppercase small text-muted">Exit terms</h6>
            <form class="form-inline mb-3" method="POST" action="{{ route('hr-lifecycle.offboarding.exit-terms', $case->id) }}">
                @csrf
                <select class="form-control form-control-sm mr-2" name="notice_type" id="exit-notice-type" onchange="document.getElementById('exit-notice-months').disabled = (this.value !== 'notice');">
                    <option value="immediate" {{ $case->notice_type === 'immediate' ? 'selected' : '' }}>Immediate effect</option>
                    <option value="notice" {{ $case->notice_type !== 'immediate' ? 'selected' : '' }}>Notice period</option>
                </select>
                <select class="form-control form-control-sm mr-2" name="notice_months" id="exit-notice-months" {{ $case->notice_type === 'immediate' ? 'disabled' : '' }}>
                    @foreach ([1, 2, 3] as $m)
                        <option value="{{ $m }}" {{ (int) $case->notice_months === $m ? 'selected' : '' }}>{{ $m }} month{{ $m > 1 ? 's' : '' }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-light">Update &amp; recompute last day</button>
                <span class="small text-muted ml-2">Current last working day: {{ optional($case->last_working_date)->format('d M Y') ?: '—' }}</span>
            </form>

            <h6 class="text-uppercase small text-muted">Timeline</h6>
            @forelse($timeline as $e)
                <div class="small border-bottom py-1">{{ ucfirst(str_replace('_', ' ', $e->event)) }} <span class="text-muted">&mdash; {{ optional($e->created_at)->format('d M Y H:i') }} by {{ $e->actor?->name ?: 'System' }}</span></div>
            @empty
                <p class="text-muted small">No events yet.</p>
            @endforelse
        </div>

        <div class="tab-pane fade" id="tab-clearance">
            <div class="alert {{ $hrIssued ? 'alert-success' : 'alert-warning' }} py-2 d-flex justify-content-between align-items-center">
                <span>
                    <i class="fa fa-{{ $hrIssued ? 'check-circle' : 'exclamation-triangle' }} mr-1"></i>
                    <strong>HR Clearance form:</strong>
                    {{ $hrIssued ? 'issued — ' . (\App\Support\Clearance::HR_DECISIONS[$case->hr_clearance_decision] ?? $case->hr_clearance_decision) : 'not completed yet. Offboarding cannot be completed until it is issued.' }}
                </span>
                <a class="btn btn-sm btn-{{ $hrIssued ? 'outline-primary' : 'primary' }}" href="{{ route('hr-lifecycle.offboarding.hr-clearance', $case->id) }}">
                    {{ $hrIssued ? 'View / download' : 'Open HR Clearance form' }}
                </a>
            </div>
            <h6 class="mb-2">Departmental clearance (HR-0111)</h6>
            <table class="table table-sm">
                <thead class="thead-light"><tr><th>Area</th><th>Owner</th><th style="width:130px">Clear</th><th>Cleared by</th></tr></thead>
                <tbody>
                @foreach($case->tasks as $task)
                    <tr class="{{ $done($task) ? 'table-success' : '' }}">
                        <td class="align-middle">{{ $task->title }}@if(!$task->is_required)<span class="badge badge-light border ml-1">optional</span>@endif</td>
                        <td class="align-middle">{{ ucfirst((string) $task->owner_type) }}</td>
                        <td class="align-middle">
                            <form method="POST" action="{{ route('hr-lifecycle.tasks.update', ['offboarding', $task->id]) }}">
                                @csrf
                                <label class="mb-0 small"><input type="checkbox" name="complete" value="1" onchange="this.form.submit()" {{ $task->status === 'completed' ? 'checked' : '' }}> {{ $task->status }}</label>
                            </form>
                        </td>
                        <td class="align-middle small text-muted">{{ $task->completedBy?->name ?: ($task->status === 'waived' ? 'waived' : '—') }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="tab-finance">
            @if($termination)
                <a class="btn btn-sm btn-outline-success mb-2" href="{{ route('hr-settlement.edit', $termination->id) }}"><i class="fa fa-calculator mr-1"></i> Open settlement worksheet</a>
                @if($settlement)
                    <table class="table table-sm w-auto">
                        <tr><td class="text-muted pr-4">Total payable</td><td class="text-right">{{ number_format((float) $settlement->total_payable, 2) }}</td></tr>
                        <tr><td class="text-muted pr-4">Total recoverable</td><td class="text-right">{{ number_format((float) $settlement->total_recoverable, 2) }}</td></tr>
                        <tr class="font-weight-bold border-top"><td>Net</td><td class="text-right">{{ number_format((float) $settlement->net_amount, 2) }}</td></tr>
                    </table>
                    @if($settlementFinal)<a class="btn btn-sm btn-outline-secondary" href="{{ route('hr-settlement.pdf', $termination->id) }}"><i class="fa fa-file-pdf-o mr-1"></i> FC form PDF</a>@endif
                @endif
            @else
                <p class="text-muted">No legal termination yet &mdash; approve the case first.</p>
            @endif
        </div>

        <div class="tab-pane fade" id="tab-assets">
            <h6 class="mb-2">Still assigned</h6>
            @forelse($assignedAssets as $a)
                <div class="border-bottom py-1">{{ $a->asset?->name }} <span class="text-muted small">({{ $a->serial_no ?: 'no serial' }}) &mdash; {{ $a->status }}</span></div>
            @empty
                <p class="text-success small"><i class="fa fa-check mr-1"></i>No assets outstanding.</p>
            @endforelse
            <h6 class="mt-3 mb-2">Return / write-off forms</h6>
            @forelse($returnForms as $f)
                <div class="border-bottom py-1">
                    <span class="font-weight-bold">{{ $f->reference }}</span> &mdash; {{ $f->asset?->name }} &mdash; {{ ucfirst($f->outcome) }}
                    <span class="badge badge-light border">recovery: {{ $f->recovery_status }}</span>
                    <a class="small ml-1" target="_blank" href="{{ route('company-assets.return-form.pdf', $f->id) }}">RT PDF</a>
                </div>
            @empty
                <p class="text-muted small">No return forms recorded.</p>
            @endforelse
        </div>

        <div class="tab-pane fade" id="tab-statutory">
            <p class="text-muted small">
                Qiwa / GOSI / Muqeem / exit-visa / CCHI / WPS actions are tracked as the "Admin / Government Relations"
                clearance task and printed on the HR-0111 PDF. Tick it when the statutory work is complete.
            </p>
            @foreach($case->tasks->where('category', 'statutory') as $task)
                <form method="POST" action="{{ route('hr-lifecycle.tasks.update', ['offboarding', $task->id]) }}">
                    @csrf
                    <label class="small"><input type="checkbox" name="complete" value="1" onchange="this.form.submit()" {{ $task->status === 'completed' ? 'checked' : '' }}> {{ $task->title }}</label>
                </form>
            @endforeach
        </div>
    </div>
</div>
@endsection
