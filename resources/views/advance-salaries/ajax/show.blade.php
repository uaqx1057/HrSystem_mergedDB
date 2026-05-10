@php
    $editDepartmentPermission = user()->permission('edit_employees');
    $deleteDepartmentPermission = user()->permission('delete_employees');
@endphp

<div id="department-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('modules.advanceSalary.title')</h3>
                        </div>
                        <div class="col-md-2 col-2 text-right">
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        if ($advanceSalary->status == 'approved') {
                            $class = 'text-light-green';
                            $status = __('app.approved');
                        } elseif ($advanceSalary->status == 'pending') {
                            $class = 'text-yellow';
                            $status = __('app.pending');
                        } else {
                            $class = 'text-red';
                            $status = __('app.rejected');
                        }
                        $paidStatus = '<i class="fa fa-circle mr-1 ' . $class . ' f-10"></i> ' . $status;

                        $reject_reason = !is_null($advanceSalary->reject_reason) ? $advanceSalary->reject_reason : '--';
                        $approve_reason = !is_null($advanceSalary->approve_reason) ? $advanceSalary->approve_reason : '--';
                    @endphp
                    <x-cards.data-row :label="__('app.employee')" :value="$advanceSalary->employee?->name" />
                    <x-cards.data-row :label="__('modules.advanceSalary.amount')" :value="number_format($advanceSalary->advance_salary, 2)" />
                    <x-cards.data-row :label="__('modules.advanceSalary.date')" :value="$advanceSalary->date ? \Carbon\Carbon::parse($advanceSalary->date)->format(company()->date_format) : '-'" />
                    <x-cards.data-row :label="__('app.status')" :value="$paidStatus" html="true" />
                    @if ($advanceSalary->status == 'rejected')
                        <x-cards.data-row :label="__('messages.reasonForAdvanceSalaryRejection')" :value="$reject_reason"
                            html="true" />
                    @endif

                    @if ($advanceSalary->status == 'approved')
                        <x-cards.data-row :label="__('messages.reasonForAdvanceSalaryApproval')" :value="$approve_reason"
                            html="true" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-advance-salary', function() {
        var id = $(this).data('advance-salary-id');
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
                var url = "{{ route('advance-salaries.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            showTable();
                            window.location.href = response.redirectUrl
                        }
                    }
                });
            }
        });
    });
</script>
