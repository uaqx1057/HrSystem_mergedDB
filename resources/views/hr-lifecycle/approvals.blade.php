@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-0">Offboarding approvals <span class="badge badge-warning">{{ $cases->count() }}</span></h4>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr-worklist.index') }}"><i class="fa fa-arrow-left mr-1"></i> HR worklist</a>
    </div>

    <div class="card"><div class="card-body">
        <p class="text-muted mb-3">
            Termination and resignation requests awaiting a manager / HR decision. <strong>Approving</strong> creates the
            legal termination record, seeds the 7-department clearance grid and starts DMS/DOBS access revocation.
        </p>

        <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead class="thead-light">
                <tr><th>Ref</th><th>Employee</th><th>Type</th><th>Last day</th><th>Reason</th><th>Requested</th><th style="width:320px">Decision</th></tr>
            </thead>
            <tbody>
            @forelse($cases as $case)
                <tr>
                    <td class="align-middle">{{ $case->reference ?: $case->id }}</td>
                    <td class="align-middle">
                        <a href="{{ route('hr-lifecycle.offboarding.console', $case->id) }}">{{ $case->employee?->name ?: '#'.$case->employee_id }}</a>
                        <div class="small text-muted">{{ $case->employee?->branch?->name }}</div>
                    </td>
                    <td class="align-middle"><span class="badge badge-light border">{{ ucfirst((string) $case->exit_type) }}</span></td>
                    <td class="align-middle small">{{ optional($case->last_working_date)->format('d M Y') }}</td>
                    <td class="align-middle small">{{ \Illuminate\Support\Str::limit($case->reason, 60) }}</td>
                    <td class="align-middle small text-muted">{{ optional($case->created_at)->diffForHumans() }}</td>
                    <td>
                        <form class="d-inline" method="POST" action="{{ route('hr-lifecycle.offboarding.approve', $case->id) }}"
                              onsubmit="return confirm('Approve this offboarding? This creates the legal termination.');">
                            @csrf<button class="btn btn-sm btn-success"><i class="fa fa-check mr-1"></i>Approve</button>
                        </form>
                        <form class="form-inline d-inline-flex mt-1" method="POST" action="{{ route('hr-lifecycle.offboarding.reject', $case->id) }}">
                            @csrf
                            <input class="form-control form-control-sm mr-1" name="reason" placeholder="Reason for rejection" maxlength="1000" required style="width:170px">
                            <button class="btn btn-sm btn-outline-danger">Reject</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4"><i class="fa fa-inbox fa-2x d-block mb-2"></i>No offboarding requests awaiting approval.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div></div>
</div>
@endsection
