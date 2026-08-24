@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    <x-filters.filter-box>
        <div class="task-search d-flex py-1 pr-lg-2 px-0 border-right-grey align-items-center">
            <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                <div class="input-group bg-grey rounded">
                    <div class="input-group-prepend">
                        <span class="input-group-text border-0 bg-additional-grey">
                            <i class="fa fa-search f-13 text-dark-grey"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                           placeholder="@lang('app.startTyping')">
                </div>
            </form>
        </div>

        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>

        <x-filters.more-filter-box>
            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize">Job opening</label>
                <div class="select-filter mb-4">
                    <select class="form-control select-picker" name="job_opening_id" id="job_opening_id"
                            data-live-search="true">
                        <option value="all">@lang('app.all')</option>
                        <option value="general" @selected(request('job_opening_id') === 'general')>General applications only</option>
                        @foreach ($jobOpenings as $job)
                            <option value="{{ $job->id }}" @selected(request('job_opening_id') == $job->id)>{{ $job->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="more-filter-items">
                <label class="f-14 text-dark-grey mb-12 text-capitalize">Status</label>
                <div class="select-filter mb-4">
                    <select class="form-control select-picker" name="status_filter" id="status_filter">
                        <option value="">@lang('app.all')</option>
                        @foreach (['new', 'applied', 'screening', 'interview_scheduled', 'interviewed', 'approved', 'onboarding', 'converted', 'rejected', 'handoff'] as $s)
                            <option value="{{ $s }}" @selected(request('status') == $s)>
                                {{ ucwords(str_replace('_', ' ', $s)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </x-filters.more-filter-box>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">

        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                {{-- <a href="{{ route('hr-candidates.create') }}" class="btn btn-sm btn-primary ml-3">
                    <i class="fa fa-plus mr-1"></i> Add Recruitment Candidates
                </a> --}}
            </div>
        </div>
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>

    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#hr-candidates-table').on('preXhr.dt', function (e, settings, data) {
            data['searchText'] = $('#search-text-field').val();
            data['jobOpeningId'] = $('#job_opening_id').val();
            data['statusFilter'] = $('#status_filter').val();
        });

        const showTable = () => {
            window.LaravelDataTables["hr-candidates-table"].draw(false);
        }

        $('#job_opening_id, #status_filter').on('change', function () {
            if ($('#job_opening_id').val() != "all" || $('#status_filter').val() != "") {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }
            showTable();
        });

        $('#search-text-field').on('keyup', function () {
            if ($(this).val() != "") {
                $('#reset-filters').removeClass('d-none');
            }
            showTable();
        });

        $('#reset-filters').click(function () {
            $('#search-text-field').val('');
            $('#job_opening_id').val('all');
            $('#status_filter').val('');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '.handoff-row', function () {
            var id = $(this).data('candidate-id');
            var url = "{{ route('hr-candidates.handoff', ':id') }}";
            url = url.replace(':id', id);
            window.location.href = url;
        });
    </script>
@endpush
