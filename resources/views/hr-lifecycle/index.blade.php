@extends('layouts.app')
@section('content')
<div class="content-wrapper"><div class="d-flex justify-content-between mb-3"><h4>HR lifecycle worklist</h4><a class="btn btn-light" href="{{ route('hr-lifecycle.index', ['status' => $status === 'open' ? 'completed' : 'open']) }}">Show {{ $status === 'open' ? 'completed' : 'open' }}</a></div>
@foreach (['Onboarding' => $onboardingCases, 'Offboarding' => $offboardingCases, 'Transfers' => $transfers] as $title => $cases)
<div class="card mb-3"><div class="card-body"><h5>{{ $title }}</h5><table class="table mb-0"><thead><tr><th>Employee</th><th>Status</th><th>Due/effective date</th><th></th></tr></thead><tbody>@forelse($cases as $case)<tr><td>{{ $case->employee?->name }}</td><td>{{ $case->status }}</td><td>{{ $case->due_date ?? $case->last_working_date ?? $case->effective_date }}</td><td><a href="{{ route('hr-lifecycle.show', $case->employee_id) }}">Open</a></td></tr>@empty<tr><td colspan="4">No records.</td></tr>@endforelse</tbody></table></div></div>
@endforeach</div>
@endsection
