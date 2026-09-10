@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-0">My resignation</h4>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card"><div class="card-body">

                @if($case)
                    <div class="d-flex align-items-center mb-2">
                        <span class="badge badge-pill p-2 mr-2 badge-{{ $case->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</span>
                        <span class="badge badge-pill p-2 badge-secondary">{{ ucfirst(str_replace('_', ' ', $case->approval_status)) }}</span>
                    </div>
                    <p class="text-muted small">
                        Requested {{ optional($case->created_at)->format('d M Y') }}
                        &middot; proposed last working day {{ optional($case->last_working_date)->format('d M Y') }}
                    </p>

                    @if($case->approval_status === 'awaiting_approval')
                        <div class="alert alert-info"><i class="fa fa-clock-o mr-1"></i> Your request is with HR / your manager for approval. You will be notified of the decision.</div>
                    @elseif($case->approval_status === 'rejected')
                        <div class="alert alert-danger"><i class="fa fa-times-circle mr-1"></i> Request not approved.<br><strong>Reason:</strong> {{ $case->rejected_reason }}</div>
                    @else
                        <h6 class="text-uppercase small text-muted mt-3">Clearance progress</h6>
                        <ul class="list-unstyled mb-3">
                            @foreach($case->tasks as $task)
                                <li class="py-1">
                                    <i class="fa fa-{{ in_array($task->status, ['completed','waived']) ? 'check-circle text-success' : 'circle-o text-muted' }} mr-2"></i>
                                    {{ $task->title }}
                                    <span class="badge badge-light border ml-1">{{ $task->status }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="border rounded p-2 bg-light">
                            <strong>Final settlement:</strong>
                            @if(!$settlement)
                                <span class="text-muted">not started</span>
                            @elseif($settlement->status === \App\Models\HrSettlementForm::STATUS_FINAL)
                                <span class="text-success">finalised &mdash; SAR {{ number_format((float) $settlement->net_amount, 2) }} net</span>
                            @else
                                <span class="text-warning">in preparation</span>
                            @endif
                        </div>
                    @endif
                @else
                    <p class="text-muted">You have no open resignation or offboarding request.</p>
                    <form method="POST" action="{{ route('employees.resignation') }}"
                          onsubmit="return confirm('Submit your resignation for approval?')">
                        @csrf
                        <div class="form-group">
                            <label class="small text-muted mb-1">Reason for resignation</label>
                            <input class="form-control" name="reason" maxlength="1000" required>
                        </div>
                        <div class="form-row">
                            <div class="col-md-6 mb-2">
                                <label class="small text-muted mb-1">Resignation date</label>
                                <input class="form-control" type="date" name="resignation_date" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="small text-muted mb-1">Proposed last working day</label>
                                <input class="form-control" type="date" name="last_working_date" required>
                            </div>
                        </div>
                        <button class="btn btn-warning"><i class="fa fa-paper-plane mr-1"></i> Submit resignation</button>
                    </form>
                @endif

            </div></div>
        </div>
    </div>
</div>
@endsection
