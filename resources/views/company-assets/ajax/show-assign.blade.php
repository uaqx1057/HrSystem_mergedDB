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
                            <h3 class="heading-h1">@lang('app.assignCompanyAsset')</h3>
                        </div>
                        {{-- <div class="col-md-2 col-2 text-right">
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
                                            href="{{ route('insurance.edit', $asset->id) }}">@lang('app.edit')</a>
                                        <a class="dropdown-item delete-insurance" data-insurance-id="{{ $asset->id }}">@lang('app.delete')</a>

                                </div>
                            </div>
                        </div> --}}
                    </div>
                </div>
                @php
                    $signatureFile = ($assignment->signed_document) ? asset_url_local_s3('asset' . '/' . $assignment->signed_document) : '';
                @endphp
                <div class="card-body">
                    <x-cards.data-row :label="__('app.catalog')" :value="$asset->catalog" />
                    <x-cards.data-row :label="__('SKU No')" :value="$asset->sku_no" />
                        <x-cards.data-row :label="__('Name')" :value="$asset->name" />
                    <x-cards.data-row :label="__('Type')" :value="$asset->type" />
                    <x-cards.data-row :label="__('Brand')" :value="$asset->brand" />
                    <h4>Assignment Details</h4>
                    <x-cards.data-row :label="__('Employee')" :value="$assignment->employee->name ?? 'N/A'" />
                    <x-cards.data-row :label="__('Status')" :value="$assignment->status ?? 'N/A'" />
                    <div class="col-12 px-0 pb-3 d-lg-flex d-md-flex d-block">
                        <p class="mb-0 text-lightest f-14 w-30 text-capitalize">Signature</p>
                        <p class="mb-0 text-dark-grey f-14 w-70 text-wrap"> <a href="{{ $signatureFile }}" target="_blank">
                            View Signature
                        </a></p>
                    </div>
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
