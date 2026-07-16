@php
$addProjectPermission = user()->permission('add_employee_bank_account');
@endphp

<div class="row py-0 py-md-0 py-lg-3">
    <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4">
        <div class="d-flex justify-content-between action-bar mt-2">
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
</div>

@include('sections.datatable_js')

<script>
    $('#employee-bank-accounts-table').on('preXhr.dt', function(e, settings, data) {
        var employee_id = "{{ $employee->id }}";
        data['employeeId'] = employee_id;
    });

    const showTable = () => {
        window.LaravelDataTables['employee-bank-accounts-table'].draw(false);
    }

    $('body').on('click', '.delete-table-row', function() {
        var id = $(this).data('employee-bank-account-id');
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
                var url = "{{ route('employee-bank-accounts.destroy', ':id') }}";
                url = url.replace(':id', id);
                $.easyAjax({
                    type: 'POST',
                    url: url,
                    data: { '_token': '{{ csrf_token() }}', '_method': 'DELETE' },
                    success: function(response) {
                        if (response.status === 'success') {
                            showTable();
                        }
                    }
                });
            }
        });
    });
</script>
