@extends('layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between mb-2"><h4>HR compliance</h4><a class="btn btn-light" href="{{ route('hr-certification-rules.index') }}">Certification rules</a></div>
    <div class="card"><div class="card-body">
        <form method="POST" action="{{ route('hr-compliance.probation') }}">@csrf<select name="employee_id">@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->name }}</option>@endforeach</select><select name="review_day"><option>30</option><option>60</option><option>90</option></select><input type="date" name="due_date" required><button>Save probation review</button></form><hr>
        <form method="POST" action="{{ route('hr-compliance.certification') }}">@csrf<select name="employee_id">@foreach($employees as $employee)<option value="{{ $employee->id }}">{{ $employee->name }}</option>@endforeach</select><input name="name" placeholder="Certification" required><input type="date" name="expires_at"><button>Save certification</button></form><hr>
        <form method="POST" action="{{ route('hr-compliance.case') }}">@csrf<input name="category" placeholder="Restricted case category" required><input name="details" placeholder="Confidential details" required><button>Create restricted case</button></form>
    </div></div>
    @if($requiredCertificationGaps->isNotEmpty())<div class="card mt-3 border-warning"><div class="card-body"><h5>Missing required certifications</h5><p class="text-muted">Requirements are matched by company and designation. Add or renew a certification to clear the gap.</p>@foreach($requiredCertificationGaps as $gap)<div>{{ $gap['employee']->name }} — {{ $gap['requirements']->implode(', ') }}</div>@endforeach</div></div>@endif
    <div class="card mt-3"><div class="card-body">
        <h5>Probation reviews</h5>@foreach($reviews as $review)<div>{{ $review->employee->name }} — day {{ $review->review_day }} — {{ $review->status }}<form class="d-inline" method="POST" action="{{ route('hr-compliance.probation.complete',$review) }}">@csrf<select name="status"><option value="confirmed">Confirm</option><option value="extended">Extend</option><option value="completed">Complete</option></select><input name="notes" placeholder="Review notes"><button>Save</button></form></div>@endforeach
        <h5 class="mt-3">Certifications</h5>@foreach($certifications as $certification)<div>{{ $certification->employee->name }} — {{ $certification->name }} — {{ $certification->expires_at?->format('Y-m-d') }}<form class="d-inline" method="POST" action="{{ route('hr-compliance.certification.renew',$certification) }}">@csrf<input type="date" name="expires_at" required><button>Renew</button></form></div>@endforeach
        <h5 class="mt-3">Restricted cases</h5>@foreach($cases as $case)<div>{{ $case->category }} — {{ $case->status }}<form class="d-inline" method="POST" action="{{ route('hr-compliance.case.update',$case) }}">@csrf<select name="status"><option>open</option><option>in_review</option><option>closed</option></select><button>Update</button></form></div>@endforeach
    </div></div>
</div>
@endsection
