@extends('layouts.app')

@php
    $checklist = \App\Services\AssetReturnService::CHECKLIST;
    $options   = \App\Services\AssetReturnService::RESULT_OPTIONS;
    // Sensible defaults so IT only changes the exceptions.
    $defaults  = ['accessories' => 'returned', 'technical' => 'good', 'data' => 'done'];
    $sectionTitles = ['accessories' => 'Accessories & items', 'technical' => 'Technical & physical evaluation', 'data' => 'Data & security clearance'];
@endphp

@section('content')
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <h4 class="mb-0">Asset return certifications</h4>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('hr-lifecycle.it-worklist') }}">
            <i class="fa fa-list mr-1"></i> IT worklist
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <p class="text-muted mb-3">
                Inspect each employee-submitted return, complete the RT-0005-0004 checklist, and certify.
                Certifying releases the serial and finalises the immutable RT record. Rows are pre-filled with the
                expected result &mdash; change only where the finding differs.
            </p>

            @forelse($records as $record)
                <div class="border rounded p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                        <div>
                            <span class="font-weight-bold">{{ $record->assignment?->employee?->name }}</span>
                            &mdash; {{ $record->assignment?->asset?->name }}
                            <span class="badge badge-light border ml-1">{{ $record->assignment?->serialLabel() ?: 'no serial' }}</span>
                        </div>
                        <span class="text-muted small">returned {{ optional($record->returned_at)->format('d M Y') }}</span>
                    </div>
                    @if($record->return_condition)
                        <div class="small text-muted mt-1"><i class="fa fa-comment-o mr-1"></i>Employee note: {{ $record->return_condition }}</div>
                    @endif

                    <details class="mt-2">
                        <summary class="btn btn-sm btn-primary">Inspect &amp; certify</summary>
                        <form method="POST" action="{{ route('hr-asset-custody.certify', $record->asset_assignment_id) }}" class="mt-3">
                            @csrf
                            @foreach($checklist as $section => $labels)
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="text-uppercase small text-muted mb-0">{{ $sectionTitles[$section] }}</h6>
                                </div>
                                <table class="table table-sm table-bordered mb-3">
                                    <thead class="thead-light">
                                        <tr><th style="width:44%">Item</th><th style="width:22%">Result</th><th>Remarks / observation</th></tr>
                                    </thead>
                                    <tbody>
                                    @foreach($labels as $i => $label)
                                        <tr>
                                            <td>{{ $label }}</td>
                                            <td>
                                                <select class="form-control form-control-sm" name="lines[{{ $section }}][{{ $i }}][result]">
                                                    @foreach($options[$section] as $opt)
                                                        <option value="{{ $opt }}" @selected($opt === $defaults[$section])>{{ ucfirst($opt) }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td><input class="form-control form-control-sm" name="lines[{{ $section }}][{{ $i }}][remarks]" maxlength="500" placeholder="—"></td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            @endforeach
                            <div class="form-group">
                                <label class="small text-muted mb-1">Inspector's notes / disposition</label>
                                <textarea class="form-control form-control-sm" name="certification_notes" rows="2" maxlength="2000"></textarea>
                            </div>
                            <button class="btn btn-sm btn-success" onclick="return confirm('Certify this return? The RT record becomes immutable.')">
                                <i class="fa fa-check mr-1"></i> Certify return
                            </button>
                        </form>
                    </details>
                </div>
            @empty
                <div class="text-center text-muted py-4">
                    <i class="fa fa-check-circle fa-2x mb-2 d-block text-success"></i>
                    No pending return certifications.
                </div>
            @endforelse

            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
