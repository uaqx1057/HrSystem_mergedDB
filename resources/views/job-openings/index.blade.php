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
                <label class="f-14 text-dark-grey mb-12 text-capitalize">Status</label>
                <div class="select-filter mb-4">
                    <select class="form-control select-picker" name="status_filter" id="status_filter">
                        <option value="">@lang('app.all')</option>
                        <option value="open">Open</option>
                        <option value="on_hold">On hold</option>
                        <option value="closed">Closed</option>
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
                <a href="{{ route('job-openings.create') }}" class="btn btn-sm btn-primary ml-3">
                    <i class="fa fa-plus mr-1"></i> Add Job Opening
                </a>
            </div>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>

    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#job-openings-table').on('preXhr.dt', function (e, settings, data) {
            data['searchText'] = $('#search-text-field').val();
            data['statusFilter'] = $('#status_filter').val();
        });

        const showTable = () => {
            window.LaravelDataTables["job-openings-table"].draw(false);
        }

        $('#status_filter').on('change', function () {
            if ($(this).val() != "") {
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
            $('#status_filter').val('');
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '.delete-table-row', function () {
            var id = $(this).data('job-id');
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')",
                text: "@lang('messages.recoverRecord')",
                icon: 'warning',
                showCancelButton: true,
                focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')",
                cancelButtonText: "@lang('app.cancel')",
                customClass: {
                    confirmButton: 'btn btn-primary mr-3',
                    cancelButton: 'btn btn-secondary'
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = "{{ route('job-openings.destroy', ':id') }}";
                    url = url.replace(':id', id);

                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        blockUI: true,
                        data: {
                            '_token': "{{ csrf_token() }}",
                            '_method': 'DELETE'
                        },
                        success: function (response) {
                            showTable();
                        }
                    });
                }
            });
        });
    </script>
@endpush