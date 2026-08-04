@php
    $editDepartmentPermission = user()->permission('edit_employees');
    $deleteDepartmentPermission = user()->permission('delete_employees');
@endphp

<div id="department-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header form-heading-background  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('app.companyAssetDetail')</h3>
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
                <div class="card-body">
                      @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="text-right d-flex justify-content-end">
                        @if ($asset->available_qty > 0
                        && (in_array(user()->permission('assign_company_asset_to_employee'), ['all', 'added','branch']))
                        )
                            <div class="">
                                <x-forms.link-primary :link="route('company-assets.assign', $asset->id)" class="mr-3 openRightModal" icon="plus">
                                    @lang('app.assign')
                                </x-forms.link-primary>
                            </div>
                        @endif
                        <a href="{{ route('company-assets.index') }}" class="btn btn-sm btn-primary">Back</a>
                        @if (in_array(user()->permission('view_assign_company_assets_to_employee'), ['all', 'added', 'owned', 'both','branch']))
                        <a href="{{ route('company-assets.view-assign', $asset->id) }}" class="btn btn-sm btn-primary openRightModal ml-2">
                            <i class="fa fa-history mr-2"></i> Assignment History
                        </a>
                        @endif
                    </div>
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

                    <h4>Serial Numbers</h4>
                    @foreach ($serials as $serial)
                        <x-cards.data-row :label="$serial->serial_no" :value="ucfirst($serial->status)" />
                    @endforeach
                    {{-- Assignment Assets  --}}
                    <h4>@lang('app.assignment')</h4>

                    <br>
                    <br>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>@lang('app.serialNo')</th>
                                    <th>@lang('app.employee')</th>
                                    <th>@lang('app.signature')</th>
                                    <th>@lang('app.status')</th>
                                    <th>@lang('app.action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $viewPermission = user()->permission('view_assign_company_assets_to_employee');
                                    $assignments = $asset->assignments();
                                    if ($viewPermission == 'added') {
                                        $assignments = $assignments->where('added_by', user()->id);
                                    }
                                    if ($viewPermission == 'owned') {
                                        $assignments = $assignments->where('employee_id', user()->id);
                                    }
                                    if ($viewPermission == 'both') {
                                        $assignments = $assignments->where('employee_id', user()->id)->orWhere('added_by', user()->id);
                                    }
                                    if ($viewPermission == 'branch' && !hr_has_all_branch_access('company_assets')) {
                                        $assignments = $assignments->where('branch_id', user()->branch_id);
                                    }
                                    $assignments = $assignments->get();
                                @endphp
                                @forelse ($assignments as $assignment)
                                    @php
                                        $signatureFile = ($assignment->signed_document) ? asset_url_local_s3('asset' . '/' . $assignment->signed_document) : '';
                                    @endphp
                                    <tr>
                                        <td>{{ $assignment->serial_no }}</td>
                                        <td>{{ $assignment->employee->name ?? 'N/A' }}</td>
                                        <td>
                                            @if ($assignment->signed_document)
                                                <a href="{{ $signatureFile }}" target="_blank">
                                                    View Signature
                                                </a>
                                            @else
                                                --
                                            @endif
                                        </td>
                                        <td>{{ $assignment->status }}</td>
                                        <td>
                                            @if (in_array(user()->permission('edit_assign_company_assets_to_employee'), ['all', 'added', 'owned', 'both','branch']) && $assignment->status === 'Pending')
                                                <a href="{{ route('company-assets.edit-assign', [$assignment->id]) }}" class="btn btn-sm btn-primary openRightModal">
                                                    <i class="fa fa-edit mr-2"></i>
                                                    @lang('app.edit')
                                                </a>
                                            @endif

                                            @if (in_array(user()->permission('edit_assign_company_assets_to_employee'), ['all', 'added', 'owned', 'both','branch']) && $assignment->status === 'Pending')
                                                <a href="javascript:;" class="btn btn-sm btn-danger delete-assignment" data-assignment-id="{{ $assignment->id }}">
                                                    <i class="fa fa-trash mr-2"></i>
                                                    @lang('app.delete')
                                                </a>
                                            @endif
                                            @if (in_array(user()->permission('upload_signature_assign_company_assets_to_employee'), ['all', 'added', 'owned', 'both','branch']) == 'all' && !$assignment->signed_document)
                                                <a href="{{ route('company-assets.upload-signature', [$assignment->id]) }}" class="btn btn-sm btn-primary ">
                                                    <i class="fa fa-upload mr-2"></i>
                                                    Upload Signature
                                                </a>
                                            @endif

                                            @if (in_array(user()->permission('upload_signature_assign_company_assets_to_employee'), ['all', 'added', 'owned', 'both','branch']) == 'all' && $assignment->signed_document)
                                                <a href="{{ route('company-assets.return', [$assignment->id]) }}" class="btn btn-sm btn-primary ">
                                                    <i class="fa fa-undo mr-2"></i>
                                                    Return Asset
                                                </a>
                                            @endif

                                        </td>
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

    $('body').on('click', '.delete-assignment', function() {
        var id = $(this).data('assignment-id');
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
                var url = "{{ route('company-assets.delete-assign', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'GET'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });
</script>
