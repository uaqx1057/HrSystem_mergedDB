@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between mb-3">
            <h4>{{ $employee->name }}</h4>
            <a class="btn btn-light" href="{{ route('employees.show', $employee->id) }}">Employee profile</a>
        </div>

        <div class="row">
            @foreach(['onboarding' => ['case' => $onboarding, 'tasks' => $onboardingTasks, 'title' => 'Onboarding', 'badge' => 'info'], 'offboarding' => ['case' => $offboarding, 'tasks' => $offboardingTasks, 'title' => 'Offboarding', 'badge' => 'warning']] as $type => $workflow)
                <div class="col-md-6">
                    <div class="card"><div class="card-body">
                        <h5>{{ $workflow['title'] }}</h5>
                        @if($workflow['case'])
                            <span class="badge badge-{{ $workflow['badge'] }}">{{ $workflow['case']->status }}{{ $workflow['case']->exit_type ? ' - ' . ucfirst($workflow['case']->exit_type) : '' }}</span>
                            @if($type === 'offboarding')
                                <span class="badge badge-secondary">{{ str_replace('_', ' ', $workflow['case']->approval_status) }}</span>
                            @endif
                            @if($type === 'offboarding' && $workflow['case']->approval_status === 'awaiting_approval')
                                <div class="alert alert-warning mt-2 mb-2">Awaiting manager or HR approval.</div>
                                <form class="d-inline" method="POST" action="{{ route('hr-lifecycle.offboarding.approve', $workflow['case']->id) }}">@csrf<button class="btn btn-sm btn-success">Approve</button></form>
                                <form class="form-inline mt-2" method="POST" action="{{ route('hr-lifecycle.offboarding.reject', $workflow['case']->id) }}">@csrf<input class="form-control form-control-sm mr-1" name="reason" placeholder="Reason for rejection" maxlength="1000" required><button class="btn btn-sm btn-outline-danger">Reject</button></form>
                            @else
                            @if($type === 'offboarding')
                                @php
                                    $tasks = $workflow['tasks'];
                                    $doneCount = $tasks->whereIn('status', ['completed', 'waived'])->count();
                                @endphp
                                <div class="mt-2 mb-1 small text-muted">Departmental clearance (HR-0111) — {{ $doneCount }} / {{ $tasks->count() }} done</div>
                                <div class="progress mb-2" style="height:5px;">
                                    <div class="progress-bar bg-info" style="width: {{ $tasks->count() ? round($doneCount / $tasks->count() * 100) : 0 }}%"></div>
                                </div>
                                <a class="btn btn-sm btn-primary" href="{{ route('hr-lifecycle.offboarding.console', $workflow['case']->id) }}">Open offboarding console</a>
                                <a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ route('hr-lifecycle.offboarding.clearance-pdf', $workflow['case']->id) }}">HR-0111 PDF</a>
                                @if($workflow['case']->termination)
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('hr-settlement.edit', $workflow['case']->termination->id) }}">Settlement</a>
                                @endif
                                <div class="small text-muted mt-2">Access revoked: {{ $workflow['case']->access_revoked_at ? $workflow['case']->access_revoked_at->format('d M Y H:i') : 'pending' }}</div>
                            @else
                                @foreach($workflow['tasks'] as $task)
                                    <form class="mt-1" method="POST" action="{{ route('hr-lifecycle.tasks.update', [$type, $task->id]) }}">
                                        @csrf
                                        <label class="mb-0"><input type="checkbox" name="complete" value="1" onchange="this.form.submit()" {{ $task->status === 'completed' ? 'checked' : '' }}> {{ $task->title }} <small class="text-muted">{{ $task->status }}{{ $task->due_date ? ' · '.$task->due_date : '' }}</small></label>
                                    </form>
                                @endforeach
                                <form class="form-inline mt-2" method="POST" action="{{ route('hr-lifecycle.tasks.add', [$type, $workflow['case']->id]) }}">
                                    @csrf
                                    <input class="form-control form-control-sm mr-1" name="title" placeholder="Add task" required>
                                    <select class="form-control form-control-sm mr-1" name="assigned_to"><option value="">Assign to</option>@foreach($employees as $assignee)<option value="{{ $assignee->id }}">{{ $assignee->name }}</option>@endforeach</select>
                                    <input class="form-control form-control-sm mr-1" type="date" name="due_date">
                                    <button class="btn btn-sm btn-light">Add</button>
                                </form>
                            @endif
                            @endif
                        @elseif($type === 'onboarding')
                            <form method="POST" action="{{ route('hr-lifecycle.onboarding.start', $employee->id) }}">@csrf<button class="btn btn-primary">Start onboarding</button></form>
                        @else
                            <form method="POST" action="{{ route('hr-lifecycle.resignation.start', $employee->id) }}">@csrf<input class="form-control mb-2" name="reason" placeholder="Resignation reason" required><input class="form-control mb-2" type="date" name="resignation_date" required><input class="form-control mb-2" type="date" name="last_working_date" required><button class="btn btn-warning">Record resignation</button></form>
                            <form class="mt-2" method="POST" action="{{ route('hr-lifecycle.offboarding.start', $employee->id) }}">@csrf<input type="hidden" name="exit_type" value="termination"><input class="form-control mb-2" name="reason" placeholder="Termination reason" required><input class="form-control mb-2" type="date" name="last_working_date" required><button class="btn btn-danger">Start termination offboarding</button></form>
                        @endif
                    </div></div>
                </div>
            @endforeach
        </div>

        <div class="card mt-3"><div class="card-body">
            <h5>Lifecycle timeline</h5>
            @forelse($lifecycleEvents as $event)
                <div class="border-bottom py-2"><strong>{{ str_replace('_', ' ', ucfirst($event->event)) }}</strong> <small class="text-muted">{{ optional($event->created_at)->format('Y-m-d H:i') }} by {{ $event->actor?->name ?: 'System' }}</small>@if($event->meta)<div class="small text-muted">{{ json_encode($event->meta) }}</div>@endif</div>
            @empty
                <p class="text-muted mb-0">No lifecycle events recorded.</p>
            @endforelse
        </div></div>

        <div class="card mt-3"><div class="card-body">
            <h5>Branch transfer</h5>
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
        </div></div>
    </div>
@endsection
