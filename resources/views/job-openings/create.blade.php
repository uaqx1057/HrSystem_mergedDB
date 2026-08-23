@extends('layouts.app')
@section('content')
<div class="content-wrapper">
    <div class="card">
        <div class="card-body">
            <h4>Add Job Opening</h4>
            <form method="POST" action="{{ route('job-openings.store') }}" id="job-opening-form">
                @csrf
                @include('job-openings.form')
                <button class="btn btn-primary mt-3">Save</button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        quillMention(null, '#description-editor');
        quillMention(null, '#requirements-editor');

        $('#job-opening-form').on('submit', function () {
            $('#description-text').val($('#description-editor .ql-editor').html());
            $('#requirements-text').val($('#requirements-editor .ql-editor').html());
        });
    });
</script>
@endpush
