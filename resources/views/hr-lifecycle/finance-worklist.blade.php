@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-0">Finance offboarding worklist</h4>
        <div>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('asset-recovery.index') }}">Asset recovery</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr-worklist.index') }}"><i class="fa fa-arrow-left mr-1"></i> HR worklist</a>
        </div>
    </div>

    <div class="card mb-3"><div class="card-body">
        <h6 class="mb-2">Settlements to prepare / finalise <span class="badge badge-primary">{{ $pendingTerminations->count() }}</span></h6>
        <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light"><tr><th>Employee</th><th>Exit</th><th>Last day</th><th>Settlement</th><th class="text-right"></th></tr></thead>
            <tbody>
            @forelse($pendingTerminations as $t)
                <tr>
                    <td class="align-middle">{{ $t->employee?->name ?: '#'.$t->user_id }}</td>
                    <td class="align-middle"><span class="badge badge-light border">{{ ucfirst((string) $t->exit_type) }}</span></td>
                    <td class="align-middle small">{{ optional($t->last_working_date)->format('d M Y') }}</td>
                    <td class="align-middle">
                        @if(!$t->settlement)
                            <span class="badge badge-secondary">Not started</span>
                        @elseif($t->settlement->status === \App\Models\HrSettlementForm::STATUS_FINAL)
                            <span class="badge badge-success">Finalised &middot; {{ number_format((float) $t->settlement->net_amount, 2) }} net</span>
                        @else
                            <span class="badge badge-warning">Draft</span>
                        @endif
                    </td>
                    <td class="text-right"><a class="btn btn-sm btn-outline-success" href="{{ route('hr-settlement.edit', $t->id) }}">Open worksheet</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-3">No pending settlements.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div></div>

    <div class="card"><div class="card-body">
        <h6 class="mb-2">Asset recoveries to approve <span class="badge badge-warning">{{ $pendingRecoveries->count() }}</span></h6>
        <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light"><tr><th>Ref</th><th>Employee</th><th>Asset</th><th class="text-right">Recommended</th><th>Status</th><th class="text-right"></th></tr></thead>
            <tbody>
            @forelse($pendingRecoveries as $f)
                <tr>
                    <td class="align-middle">{{ $f->reference }}</td>
                    <td class="align-middle">{{ $f->employee?->name }}</td>
                    <td class="align-middle">{{ $f->asset?->name }}</td>
                    <td class="align-middle text-right">{{ number_format((float) $f->recommended_recovery_amount, 2) }}</td>
                    <td class="align-middle"><span class="badge badge-light border">{{ $f->recovery_status }}</span></td>
                    <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('asset-recovery.index') }}">Review</a></td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-success py-3"><i class="fa fa-check-circle mr-1"></i>No recoveries pending.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div></div>
</div>
@endsection
