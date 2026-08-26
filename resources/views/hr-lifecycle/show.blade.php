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
                            @foreach($workflow['tasks'] as $task)
                                <form class="mt-2" method="POST" action="{{ route('hr-lifecycle.tasks.update', [$type, $task->id]) }}">
                                    @csrf
                                    <label><input type="checkbox" name="complete" value="1" onchange="this.form.submit()" {{ $task->status === 'completed' ? 'checked' : '' }}> {{ $task->title }} <small>{{ $task->due_date }}</small></label>
                                </form>
                            @endforeach
                            <form class="form-inline mt-2" method="POST" action="{{ route('hr-lifecycle.tasks.add', [$type, $workflow['case']->id]) }}">
                                @csrf
                                <input class="form-control form-control-sm mr-1" name="title" placeholder="Add task" required>
                                <select class="form-control form-control-sm mr-1" name="assigned_to"><option value="">Assign to</option>@foreach($employees as $assignee)<option value="{{ $assignee->id }}">{{ $assignee->name }}</option>@endforeach</select>
                                <input class="form-control form-control-sm mr-1" type="date" name="due_date">
                                <button class="btn btn-sm btn-light">Add</button>
                            </form>
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
            <h5>Branch transfer</h5>
            <form method="POST" action="{{ route('hr-lifecycle.transfer.request', $employee->id) }}" class="form-row">@csrf
                <div class="col-md-3"><select class="form-control" name="to_branch_id" required><option value="">New branch</option>@foreach($branches as $branch)<option value="{{ $branch->id }}">{{ $branch->name }}</option>@endforeach</select></div>
                <div class="col-md-3"><select class="form-control" name="to_department_id"><option value="">Keep current department</option>@foreach($departments as $department)<option value="{{ $department->id }}">{{ $department->team_name }}</option>@endforeach</select></div>
                <div class="col-md-3"><select class="form-control" name="to_manager_id"><option value="">Keep current manager</option>@foreach($employees as $manager)<option value="{{ $manager->id }}">{{ $manager->name }}</option>@endforeach</select></div>
                <div class="col-md-2"><input class="form-control" type="date" name="effective_date" required></div>
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
