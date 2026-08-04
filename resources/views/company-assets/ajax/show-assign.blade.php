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
                    $signatureFile = ($assignment && $assignment->signed_document) ? asset_url_local_s3('asset' . '/' . $assignment->signed_document) : '';
                @endphp
                <div class="card-body">
                    <x-cards.data-row :label="__('app.catalog')" :value="$asset->catalog" />
                    <x-cards.data-row :label="__('SKU No')" :value="$asset->sku_no" />
                        <x-cards.data-row :label="__('Name')" :value="$asset->name" />
                    <x-cards.data-row :label="__('Type')" :value="$asset->type" />
                    <x-cards.data-row :label="__('Brand')" :value="$asset->brand" />
                    <x-cards.data-row :label="__('app.department')" :value="$asset->department->name ?? 'N/A'" />
                    <x-cards.data-row :label="__('app.branchName')" :value="$asset->branch->name ?? 'N/A'" />
                    <x-cards.data-row :label="__('app.qty')" :value="$asset->qty" />
                    <x-cards.data-row :label="__('app.availableQty')" :value="$asset->available_qty" />
                    <x-cards.data-row :label="__('app.status')" :value="ucfirst($asset->status)" />

                    <h4>Assignment History</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>@lang('app.serialNo')</th>
                                    <th>@lang('app.employee')</th>
                                    <th>@lang('app.action')</th>
                                    <th>@lang('app.signature')</th>
                                    <th>@lang('app.date')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($history as $record)
                                    @php
                                        $signatureFile = ($record && $record->signed_document) ? asset_url_local_s3('asset' . '/' . $record->signed_document) : '';
                                    @endphp
                                    <tr>
                                        <td>{{ $record->serial_no ?? 'N/A' }}</td>
                                        <td>{{ $record->employee->name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst($record->action_type) }}</td>
                                        <td>
                                            @if ($signatureFile)
                                                <a href="{{ $signatureFile }}" target="_blank">View Signature</a>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>{{ $record->action_at ? $record->action_at->format('d-m-Y H:i') : 'N/A' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">@lang('messages.noRecordFound')</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
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
