@extends('layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-0">HR worklist</h4>
    </div>

    <div class="row">
        @foreach($items as $item)
            <div class="col-md-4 col-xl-3 mb-3">
                <div class="card h-100">
                    <div class="card-body py-3">
                        <div class="text-muted small text-uppercase">{{ $item['label'] }}</div>
                        <div class="h3 mb-0 {{ $item['count'] > 0 ? 'text-dark' : 'text-muted' }}">{{ $item['count'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mt-2"><div class="card-body">
        <h6 class="text-uppercase small text-muted mb-3">Queues &amp; screens</h6>
        <div class="d-flex flex-wrap" style="gap:.5rem;">
            <a class="btn btn-outline-primary btn-sm" href="{{ route('hr-lifecycle.index') }}"><i class="fa fa-random mr-1"></i> Lifecycle workflows</a>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('hr-lifecycle.approvals') }}"><i class="fa fa-check-square-o mr-1"></i> Offboarding approvals</a>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('hr-lifecycle.it-worklist') }}"><i class="fa fa-laptop mr-1"></i> IT worklist</a>
            <a class="btn btn-outline-primary btn-sm" href="{{ route('hr-lifecycle.finance-worklist') }}"><i class="fa fa-money mr-1"></i> Finance worklist</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('asset-recovery.index') }}"><i class="fa fa-undo mr-1"></i> Asset recovery</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('hr-asset-custody.certifications') }}"><i class="fa fa-clipboard mr-1"></i> IT certifications</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('hr-attendance-exceptions.index') }}"><i class="fa fa-clock-o mr-1"></i> Attendance exceptions</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('hr-employee-requests.index') }}"><i class="fa fa-inbox mr-1"></i> Employee requests</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('hr-compliance.index') }}"><i class="fa fa-id-card-o mr-1"></i> Compliance</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('hr-candidates.index') }}"><i class="fa fa-user-plus mr-1"></i> Recruitment</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('hr-payroll-preflight.index') }}"><i class="fa fa-list-alt mr-1"></i> Payroll preflight</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('hr-asset-custody.index') }}"><i class="fa fa-briefcase mr-1"></i> Asset custody</a>
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('hr-settlement.settings') }}"><i class="fa fa-shield mr-1"></i> Settlement policy</a>
        </div>
    </div></div>
</div>
@endsection
