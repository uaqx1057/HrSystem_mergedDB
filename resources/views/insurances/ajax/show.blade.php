@php
    $editDepartmentPermission = user()->permission('edit_employees');
    $deleteDepartmentPermission = user()->permission('delete_employees');
@endphp

<div id="department-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white  border-bottom-grey text-capitalize justify-content-between p-20 form-heading-background">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('app.insuranceDetails')</h3>
                        </div>
                        <div class="col-md-2 col-2 text-right">
                            <div class="dropdown">
                                <button
                                    class="btn btn-lg f-14 px-2 py-1 text-dark-grey text-capitalize rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                        <a class="dropdown-item openRightModal"
                                            data-redirect-url="{{ url()->previous() }}"
                                            href="{{ route('insurance.edit', $insurance->id) }}">@lang('app.edit')</a>
                                        <a class="dropdown-item delete-insurance" data-insurance-id="{{ $insurance->id }}">@lang('app.delete')</a>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($insurance->employee_id)
                    <x-cards.data-row :label="__('app.employee')" :value="$insurance->employee?->name" />
                    @else
                    <x-cards.data-row :label="__('app.driver')" :value="$insurance->driver?->name" />
                    @endif
                    <x-cards.data-row :label="__('modules.insurance.issue_date')" :value="$insurance->issue_date->format(company()->date_format)" />
                    <x-cards.data-row :label="__('modules.insurance.expiry_date')" :value="$insurance->expiry_date->format(company()->date_format)" />
                    <x-cards.data-row :label="__('app.company_name')" :value="$insurance->company" />
                    <x-cards.data-row :label="__('app.policy_no')" :value="$insurance->policy_no" />
                    <x-cards.data-row :label="__('app.class')" :value="$insurance->class" />
                    @if($insurance->status == 'active' && $insurance->expiry_date <= today())
                    <x-cards.data-row :label="__('app.status')" :value="__('app.expired')" />
                    @else
                    <x-cards.data-row :label="__('app.status')" :value="ucfirst($insurance->status)" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-insurance', function() {
        var id = $(this).data('insurance-id');
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
                var url = "{{ route('insurance.destroy', ':id') }}";
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
