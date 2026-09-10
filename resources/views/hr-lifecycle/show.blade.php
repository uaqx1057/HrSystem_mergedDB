@extends('layouts.app')

@php
    $activeCase = $offboarding && in_array($offboarding->status, ['open', 'completion_pending'], true);
    $tab = request('tab');
    if (!in_array($tab, ['onboarding', 'offboarding', 'transfers', 'timeline'], true)) {
        $tab = $activeCase ? 'offboarding' : 'onboarding';
    }
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between mb-3">
            <h4>{{ $employee->name }} <span class="text-muted f-14">— employee lifecycle</span></h4>
            <a class="btn btn-light" href="{{ route('employees.show', $employee->id) }}">Employee profile</a>
        </div>

        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item"><a class="nav-link {{ $tab === 'onboarding' ? 'active' : '' }}" data-toggle="tab" href="#lc-onboarding">Onboarding</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab === 'offboarding' ? 'active' : '' }}" data-toggle="tab" href="#lc-offboarding">Offboarding</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab === 'transfers' ? 'active' : '' }}" data-toggle="tab" href="#lc-transfers">Transfers</a></li>
            <li class="nav-item"><a class="nav-link {{ $tab === 'timeline' ? 'active' : '' }}" data-toggle="tab" href="#lc-timeline">Timeline</a></li>
        </ul>

        <div class="tab-content border border-top-0 bg-white p-3">

            {{-- ── ONBOARDING ──────────────────────────────────────── --}}
            <div class="tab-pane fade {{ $tab === 'onboarding' ? 'show active' : '' }}" id="lc-onboarding">
                <h5 class="mb-2">Onboarding</h5>
                @if($onboarding)
                    <span class="badge badge-info">{{ $onboarding->status }}</span>
                    @php $obDone = $onboardingTasks->whereIn('status', ['completed', 'waived'])->count(); @endphp
                    <div class="mt-2 mb-1 small text-muted">{{ $obDone }} / {{ $onboardingTasks->count() }} tasks done</div>
                    <div class="progress mb-3" style="height:5px;">
                        <div class="progress-bar bg-info" style="width: {{ $onboardingTasks->count() ? round($obDone / $onboardingTasks->count() * 100) : 0 }}%"></div>
                    </div>
                    @foreach($onboardingTasks as $task)
                        <form class="mt-1" method="POST" action="{{ route('hr-lifecycle.tasks.update', ['onboarding', $task->id]) }}">
                            @csrf
                            <label class="mb-0"><input type="checkbox" name="complete" value="1" onchange="this.form.submit()" {{ $task->status === 'completed' ? 'checked' : '' }}> {{ $task->title }} <small class="text-muted">{{ $task->status }}{{ $task->due_date ? ' · '.$task->due_date : '' }}</small></label>
                        </form>
                    @endforeach
                    <form class="form-inline mt-3" method="POST" action="{{ route('hr-lifecycle.tasks.add', ['onboarding', $onboarding->id]) }}">
                        @csrf
                        <input class="form-control form-control-sm mr-1" name="title" placeholder="Add task" required>
                        <select class="form-control form-control-sm mr-1" name="assigned_to"><option value="">Assign to</option>@foreach($employees as $assignee)<option value="{{ $assignee->id }}">{{ $assignee->name }}</option>@endforeach</select>
                        <input class="form-control form-control-sm mr-1" type="date" name="due_date">
                        <button class="btn btn-sm btn-light">Add</button>
                    </form>
                @else
                    <p class="text-muted">No onboarding checklist for this employee.</p>
                    <form method="POST" action="{{ route('hr-lifecycle.onboarding.start', $employee->id) }}">@csrf<button class="btn btn-primary">Start onboarding</button></form>
                @endif
            </div>

            {{-- ── OFFBOARDING ─────────────────────────────────────── --}}
            <div class="tab-pane fade {{ $tab === 'offboarding' ? 'show active' : '' }}" id="lc-offboarding">
                <h5 class="mb-2">Offboarding</h5>
                @if($offboarding)
                    <span class="badge badge-warning">{{ $offboarding->status }}{{ $offboarding->exit_type ? ' — ' . ucfirst($offboarding->exit_type) : '' }}</span>
                    <span class="badge badge-secondary">{{ str_replace('_', ' ', $offboarding->approval_status) }}</span>

                    @if($offboarding->approval_status === 'awaiting_approval')
                        <div class="alert alert-warning mt-3 mb-2">Awaiting manager or HR approval.</div>
                        <form class="d-inline" method="POST" action="{{ route('hr-lifecycle.offboarding.approve', $offboarding->id) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                        <form class="form-inline mt-2" method="POST" action="{{ route('hr-lifecycle.offboarding.reject', $offboarding->id) }}">@csrf<input class="form-control form-control-sm mr-1" name="reason" placeholder="Reason for rejection" maxlength="1000" required><button class="btn btn-sm btn-outline-danger">Reject</button></form>
                    @else
                        @php $obDone = $offboardingTasks->whereIn('status', ['completed', 'waived'])->count(); @endphp
                        <div class="mt-3 mb-1 small text-muted">Departmental clearance (HR-0111) — {{ $obDone }} / {{ $offboardingTasks->count() }} done</div>
                        <div class="progress mb-3" style="height:5px;">
                            <div class="progress-bar bg-info" style="width: {{ $offboardingTasks->count() ? round($obDone / $offboardingTasks->count() * 100) : 0 }}%"></div>
                        </div>
                        <p class="text-muted">Run the offboarding from the console — clearances, HR-0111 form, settlement and completion all live there.</p>
                        <a class="btn btn-primary" href="{{ route('hr-lifecycle.offboarding.console', $offboarding->id) }}"><i class="fa fa-tasks mr-1"></i> Open offboarding console</a>
                        <a class="btn btn-outline-primary" target="_blank" href="{{ route('hr-lifecycle.offboarding.clearance-pdf', $offboarding->id) }}"><i class="fa fa-file-pdf-o mr-1"></i> HR-0111 PDF</a>
                        @if($offboarding->termination)
                            <a class="btn btn-outline-success" href="{{ route('hr-settlement.edit', $offboarding->termination->id) }}">Settlement</a>
                        @endif
                        <div class="small text-muted mt-2">DMS/DOBS access revoked: {{ $offboarding->access_revoked_at ? $offboarding->access_revoked_at->format('d M Y H:i') : 'pending' }}</div>
                    @endif
                @else
                    <p class="text-muted">No offboarding case. Start a resignation or termination below.</p>
                    @php $noticeSelects = '<select class="form-control mb-2" name="notice_type"><option value="notice">With notice period</option><option value="immediate">Immediate effect</option></select><select class="form-control mb-2" name="notice_months"><option value="1">1 month</option><option value="2">2 months</option><option value="3">3 months</option></select>'; @endphp
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Resignation</h6>
                            <form method="POST" action="{{ route('hr-lifecycle.resignation.start', $employee->id) }}">@csrf<input class="form-control mb-2" name="reason" placeholder="Resignation reason" required><input class="form-control mb-2" type="date" name="resignation_date" required>{!! $noticeSelects !!}<button class="btn btn-warning">Record resignation</button></form>
                        </div>
                        <div class="col-md-6">
                            <h6>Termination</h6>
                            <form method="POST" action="{{ route('hr-lifecycle.offboarding.start', $employee->id) }}">@csrf<input type="hidden" name="exit_type" value="termination"><input class="form-control mb-2" name="reason" placeholder="Termination reason" required>{!! $noticeSelects !!}<button class="btn btn-danger">Start termination offboarding</button></form>
                        </div>
                    </div>
                @endif
            </div>

            {{-- ── TRANSFERS ───────────────────────────────────────── --}}
            <div class="tab-pane fade {{ $tab === 'transfers' ? 'show active' : '' }}" id="lc-transfers">
                <h5 class="mb-2">Branch transfer</h5>
                <form method="POST" action="{{ route('hr-lifecycle.transfer.request', $employee->id) }}" class="form-row">@csrf
                    <div class="col-md-3"><select class="form-control" name="to_branch_id" required><option value="">New branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><select class="form-control" name="to_department_id"><option value="">Keep current department</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->team_name }}</option>@endforeach</select></div>
                    <div class="col-md-3"><select class="form-control" name="to_manager_id"><option value="">Keep current manager</option>@foreach($employees as $manager)<option value="{{ $manager->id }}">{{ $manager->name }}</option>@endforeach</select></div>
                    <div class="col-md-2"><input class="form-control" type="date" name="effective_date" required></div>
                    <div class="col-md-2"><select class="form-control" name="asset_decision"><option value="">Asset decision</option><option value="retain">Retain assignment</option><option value="return_required">Return before transfer</option><option value="reassign">Reassign asset</option></select></div>
                    <div class="col-md-1"><button class="btn btn-primary">Request</button></div>
                    <div class="col-12 mt-2"><input class="form-control" name="reason" placeholder="Reason for transfer"></div>
                </form>
                @foreach($transfers as $transfer)
                    <div class="mt-2">{{ $transfer->effective_date->format('Y-m-d') }} — {{ $transfer->status }}
                        @if($transfer->status === 'pending' && in_array('admin', user_roles()))<form class="d-inline" method="POST" action="{{ route('hr-lifecycle.transfer.approve', $transfer->id) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>@endif
                        @if($transfer->status === 'approved' && in_array('admin', user_roles()) && !$transfer->effective_date->isFuture())<form class="d-inline" method="POST" action="{{ route('hr-lifecycle.transfer.apply', $transfer->id) }}">@csrf<button class="btn btn-sm btn-primary">Apply</button></form>@endif
                    </div>
                @endforeach
            </div>

            {{-- ── TIMELINE ────────────────────────────────────────── --}}
            <div class="tab-pane fade {{ $tab === 'timeline' ? 'show active' : '' }}" id="lc-timeline">
                <h5 class="mb-2">Lifecycle timeline</h5>
                @forelse($lifecycleEvents as $event)
                    <div class="border-bottom py-2"><strong>{{ str_replace('_', ' ', ucfirst($event->event)) }}</strong> <small class="text-muted">{{ optional($event->created_at)->format('Y-m-d H:i') }} by {{ $event->actor?->name ?: 'System' }}</small>@if($event->meta)<div class="small text-muted">{{ json_encode($event->meta) }}</div>@endif</div>
                @empty
                    <p class="text-muted mb-0">No lifecycle events recorded.</p>
                @endforelse
            </div>

        </div>
    </div>
@endsection
