@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-0">IT offboarding worklist</h4>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr-worklist.index') }}"><i class="fa fa-arrow-left mr-1"></i> HR worklist</a>
    </div>

    <div class="card mb-3"><div class="card-body">
        <h6 class="mb-2">Returns awaiting IT certification <span class="badge badge-primary">{{ $pendingCertifications->count() }}</span></h6>
        <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light"><tr><th>Employee</th><th>Asset</th><th>Returned</th><th class="text-right"></th></tr></thead>
            <tbody>
            @forelse($pendingCertifications as $r)
                <tr>
                    <td class="align-middle">{{ $r->assignment?->employee?->name }}</td>
                    <td class="align-middle">{{ $r->assignment?->asset?->name }} <span class="badge badge-light border">{{ $r->assignment?->serialLabel() ?: '—' }}</span></td>
                    <td class="align-middle small">{{ optional($r->returned_at)->format('d M Y') }}</td>
                    <td class="text-right"><a class="btn btn-sm btn-primary" href="{{ route('hr-asset-custody.certifications') }}">Certify</a></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-success py-3"><i class="fa fa-check-circle mr-1"></i>Nothing awaiting certification.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div></div>

    <div class="card"><div class="card-body">
        <h6 class="mb-2">Assets still out with departing employees <span class="badge badge-warning">{{ $assetsStillOut->count() }}</span></h6>
        <div class="table-responsive">
        <table class="table table-sm table-hover mb-0">
            <thead class="thead-light"><tr><th>Employee</th><th>Asset</th><th>Serial</th><th>Status</th></tr></thead>
            <tbody>
            @forelse($assetsStillOut as $a)
                <tr>
                    <td class="align-middle">{{ $a->employee?->name ?: '#'.$a->employee_id }}</td>
                    <td class="align-middle">{{ $a->asset?->name }}</td>
                    <td class="align-middle small">{{ $a->serial_no ?: '—' }}</td>
                    <td class="align-middle"><span class="badge badge-{{ $a->status === \App\Models\AssetAssignment::STATUS_ASSIGNED ? 'info' : 'secondary' }}">{{ ucfirst($a->status) }}</span></td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-success py-3"><i class="fa fa-check-circle mr-1"></i>All assets returned.</td></tr>
            @endforelse
            </tbody>
        </table>
        </div>
    </div></div>
</div>
@endsection
