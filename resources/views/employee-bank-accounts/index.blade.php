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
                <label class="f-14 text-dark-grey mb-12 text-capitalize" for="employee_id">@lang('app.employee')</label>
                <div class="select-filter mb-4">
                    <div class="select-others">
                        <select class="form-control select-picker" name="employee_id" id="employee_id" data-live-search="true" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </x-filters.more-filter-box>
    </x-filters.filter-box>
@endsection

@section('content')
    <div class="content-wrapper">
        <div class="d-grid d-lg-flex d-md-flex action-bar">
            <div id="table-actions" class="flex-grow-1 align-items-center">
                @if (in_array(user()->permission('add_employee_bank_account'), ['all', 'added']))
                    <x-forms.link-primary :link="route('employee-bank-accounts.create')" class="mr-3 openRightModal float-left" icon="plus">
                        @lang('app.add')
                    </x-forms.link-primary>
                @endif
            </div>

            <x-datatable.actions>
                <div class="select-status mr-3 pl-3">
                    <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                        <option value="">@lang('app.selectAction')</option>
                        <option value="delete">@lang('app.delete')</option>
                    </select>
                </div>
            </x-datatable.actions>
        </div>

        <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
    </div>
@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#employee-bank-accounts-table').on('preXhr.dt', function (e, settings, data) {
            const searchText = $('#search-text-field').val();
            const employeeId = $('#employee_id').val();

            data['searchText'] = searchText;
            data['employeeId'] = employeeId;
        });

        const showTable = () => {
            window.LaravelDataTables['employee-bank-accounts-table'].draw(false);
        };

        $('#employee_id').on('change', function () {
            if ($('#employee_id').val() != 'all') {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }

            showTable();
        });

        $('#search-text-field').on('keyup', function () {
            if ($('#search-text-field').val() != '') {
                $('#reset-filters').removeClass('d-none');
            } else {
                $('#reset-filters').addClass('d-none');
            }

            showTable();
        });

        $('#reset-filters').click(function () {
            $('#search-text-field').val('');
            $('#employee_id').val('all');
            $('.filter-box .select-picker').selectpicker('refresh');
            $('#reset-filters').addClass('d-none');
            showTable();
        });

        $('body').on('click', '.delete-table-row', function () {
            var id = $(this).data('employee-bank-account-id');
            Swal.fire({
                title: '@lang('messages.sweetAlertTitle')',
                text: '@lang('messages.recoverRecord')',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: '@lang('messages.confirmDelete')',
                cancelButtonText: '@lang('app.cancel')',
                customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    var url = '{{ route('employee-bank-accounts.destroy', ':id') }}';
                    url = url.replace(':id', id);
                    $.easyAjax({
                        type: 'POST',
                        url: url,
                        data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                        success: function (response) {
                            if (response.status === 'success') {
                                showTable();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endpush
