@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-0">Asset recovery approvals <span class="badge badge-warning">{{ $forms->total() }}</span></h4>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr-lifecycle.finance-worklist') }}"><i class="fa fa-arrow-left mr-1"></i> Finance worklist</a>
    </div>

    <div class="card"><div class="card-body">
        <p class="text-muted mb-3">
            Finalised asset write-offs with a recommended recovery amount. <strong>Approve</strong> the recoverable value
            (it is capped at the remaining recommendation and recorded in the allocation ledger, then flows into the
            employee's final settlement), or <strong>waive</strong> it with a reason.
        </p>

        <div class="table-responsive">
        <table class="table table-sm">
            <thead class="thead-light"><tr><th>Ref</th><th>Employee</th><th>Asset</th><th class="text-right">Recommended</th><th style="width:360px">Decision</th></tr></thead>
            <tbody>
            @forelse($forms as $form)
                <tr>
                    <td class="align-middle">{{ $form->reference }}</td>
                    <td class="align-middle">{{ $form->employee?->name }}</td>
                    <td class="align-middle">{{ $form->asset?->name }}<div class="small text-muted">{{ $form->recovery_reason }}</div></td>
                    <td class="align-middle text-right font-weight-bold">{{ number_format((float) $form->recommended_recovery_amount, 2) }}</td>
                    <td>
                        <form method="POST" action="{{ route('asset-recovery.approve', $form->id) }}" class="form-inline mb-1">
                            @csrf
                            <input class="form-control form-control-sm mr-1 text-right" name="amount" type="number" min="0.01" step="0.01" value="{{ $form->recommended_recovery_amount }}" required style="width:110px">
                            <input class="form-control form-control-sm mr-1" name="notes" placeholder="Notes" style="width:130px">
                            <button class="btn btn-sm btn-primary">Approve</button>
                        </form>
                        <form method="POST" action="{{ route('asset-recovery.waive', $form->id) }}" class="form-inline"
                              onsubmit="return confirm('Waive the remaining recovery for {{ $form->reference }}?');">
                            @csrf
                            <input class="form-control form-control-sm mr-1" name="reason" placeholder="Waiver reason" required style="width:200px">
                            <button class="btn btn-sm btn-outline-secondary">Waive</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-success py-4"><i class="fa fa-check-circle fa-2x d-block mb-2"></i>No pending asset recoveries.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
        {{ $forms->links() }}
    </div></div>
</div>
@endsection
