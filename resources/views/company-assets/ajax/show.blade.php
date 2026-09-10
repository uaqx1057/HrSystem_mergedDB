@php
    $S_AVAILABLE = \App\Models\CompanyAssetSerial::STATUS_AVAILABLE;
    $S_PENDING   = \App\Models\CompanyAssetSerial::STATUS_PENDING;
    $S_ASSIGNED  = \App\Models\CompanyAssetSerial::STATUS_ASSIGNED;

    $assignPermission = user()->permission('assign_company_asset_to_employee');
    $viewAssignPerm   = user()->permission('view_assign_company_assets_to_employee');
    $editAssignPerm   = user()->permission('edit_assign_company_assets_to_employee');
    $sigPerm          = user()->permission('upload_signature_assign_company_assets_to_employee');
    $editAssetPerm    = user()->permission('edit_company_assets');

    $canAssign       = in_array($assignPermission, ['all', 'added', 'branch']);
    $canEditAssign   = in_array($editAssignPerm, ['all', 'added', 'owned', 'both', 'branch']);
    $canSign         = in_array($sigPerm, ['all', 'added', 'owned', 'both', 'branch']);
    $canFlagSerials  = in_array($editAssetPerm, ['all', 'added', 'branch']);

    $serialBadge = fn ($s) => [
        'available' => 'badge-success',
        'pending'   => 'badge-warning',
        'assigned'  => 'badge-info',
        'lost'      => 'badge-danger',
        'damaged'   => 'badge-danger',
        'retired'   => 'badge-secondary',
    ][strtolower((string) $s)] ?? 'badge-secondary';

    $assetBadge = fn ($s) => [
        'available'          => 'badge-success',
        'partially_assigned' => 'badge-warning',
        'assigned'           => 'badge-info',
    ][strtolower((string) $s)] ?? 'badge-secondary';

    $assetLabel = fn ($s) => [
        'available'          => 'Available',
        'partially_assigned' => 'Partially assigned',
        'assigned'           => 'Fully assigned',
    ][strtolower((string) $s)] ?? ucfirst((string) $s);

    $countBy = fn ($status) => $serials->where('status', $status)->count();
    $flaggedCount = $serials->whereIn('status', [
        'lost', 'damaged', 'retired',
    ])->count();
@endphp

<div id="company-asset-detail">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">

                <div class="card-header form-heading-background border-bottom-grey d-flex flex-wrap justify-content-between align-items-center p-20">
                    <h3 class="heading-h1 mb-0">
                        @lang('app.companyAssetDetail')
                        <span class="badge {{ $assetBadge($asset->status) }} f-12 ml-2 align-middle">{{ $assetLabel($asset->status) }}</span>
                    </h3>
                    <div class="d-flex flex-wrap align-items-center">
                        @if ($asset->available_qty > 0 && $canAssign)
                            <a href="{{ route('company-assets.assign', $asset->id) }}" class="btn btn-sm btn-primary openRightModal mr-2">
                                <i class="fa fa-user-plus mr-1"></i> @lang('app.assign')
                            </a>
                        @endif
                        @if (in_array($viewAssignPerm, ['all', 'added', 'owned', 'both', 'branch']))
                            <a href="{{ route('company-assets.view-assign', $asset->id) }}" class="btn btn-sm btn-outline-primary openRightModal mr-2">
                                <i class="fa fa-history mr-1"></i> @lang('app.assignmentHistory')
                            </a>
                        @endif
                        <a href="{{ route('company-assets.index') }}" class="btn btn-sm btn-secondary">
                            <i class="fa fa-arrow-left mr-1"></i> @lang('app.back')
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    @if (session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                    @if (session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

                    <div class="row">
                        <div class="col-md-6">
                            <x-cards.data-row :label="__('app.name')" :value="$asset->name ?: '--'" />
                            <x-cards.data-row :label="__('app.catalog')" :value="$asset->catalog ?: '--'" />
                            <x-cards.data-row :label="__('SKU No')" :value="$asset->sku_no ?: '--'" />
                            <x-cards.data-row :label="__('app.type')" :value="$asset->type ?: '--'" />
                            <x-cards.data-row :label="__('app.brand')" :value="$asset->brand ?: '--'" />
                        </div>
                        <div class="col-md-6">
                            <x-cards.data-row :label="__('app.department')" :value="optional($asset->department)->name ?? '--'" />
                            <x-cards.data-row :label="__('app.branchName')" :value="optional($asset->branch)->name ?? '--'" />
                            <x-cards.data-row :label="__('app.status')" :value="$assetLabel($asset->status)" />
                        </div>
                    </div>

                    {{-- At-a-glance --}}
                    <div class="d-flex flex-wrap mt-2" style="gap: 8px;">
                        <span class="badge badge-success f-12 p-2">{{ $countBy('available') }} available</span>
                        <span class="badge badge-warning f-12 p-2">{{ $countBy('pending') }} reserved</span>
                        <span class="badge badge-info f-12 p-2">{{ $countBy('assigned') }} issued</span>
                        @if ($flaggedCount)
                            <span class="badge badge-danger f-12 p-2">{{ $flaggedCount }} lost / damaged / retired</span>
                        @endif
                        <span class="badge badge-light f-12 p-2">{{ $serials->count() }} units total</span>
                    </div>

                    {{-- Units --}}
                    <h4 class="mt-4 mb-2 f-16">Units</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="text-uppercase f-11 text-dark-grey">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>@lang('app.serialNo')</th>
                                    <th style="width: 110px;">@lang('app.status')</th>
                                    <th>@lang('app.employee')</th>
                                    <th class="text-right" style="white-space: nowrap;">@lang('app.action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($serials as $i => $serial)
                                    @php
                                        $a = $serial->assignment;                       // AssetAssignment|null
                                        $holder = optional(optional($a)->employee)->name;
                                        $sigFile = ($a && $a->signed_document) ? asset_url_local_s3('asset/' . $a->signed_document) : '';
                                    @endphp
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td class="text-darkest-grey f-w-500">{{ $serial->serial_no }}</td>
                                        <td><span class="badge {{ $serialBadge($serial->status) }}">{{ ucfirst($serial->status) }}</span></td>
                                        <td>
                                            {{ $holder ?: '--' }}
                                            @if ($sigFile)
                                                <a href="{{ $sigFile }}" target="_blank" rel="noopener" class="f-11 d-block"><i class="fa fa-file-signature mr-1"></i> signed doc</a>
                                            @endif
                                        </td>
                                        <td class="text-right" style="white-space: nowrap;">
                                            <div class="d-inline-flex align-items-center flex-nowrap" style="gap: 4px;">
                                                {{-- AVAILABLE --}}
                                                @if ($serial->status === $S_AVAILABLE)
                                                    @if ($canAssign)
                                                        <a href="{{ route('company-assets.assign', [$asset->id, 'serial_id' => $serial->id]) }}"
                                                           class="btn btn-xs btn-primary openRightModal">
                                                            <i class="fa fa-user-plus mr-1"></i> Assign
                                                        </a>
                                                    @endif
                                                    @if ($canFlagSerials)
                                                        <div class="dropdown d-inline-block">
                                                            <button class="btn btn-xs btn-outline-secondary dropdown-toggle" data-toggle="dropdown">Flag</button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a class="dropdown-item serial-status" data-serial="{{ $serial->id }}" data-status="lost">Mark lost</a>
                                                                <a class="dropdown-item serial-status" data-serial="{{ $serial->id }}" data-status="damaged">Mark damaged</a>
                                                                <a class="dropdown-item serial-status" data-serial="{{ $serial->id }}" data-status="retired">Retire</a>
                                                            </div>
                                                        </div>
                                                    @endif

                                                {{-- PENDING (reserved, not signed) --}}
                                                @elseif ($serial->status === $S_PENDING && $a)
                                                    <a href="{{ route('company-assets.generate-pdf', $a->id) }}" class="btn btn-xs btn-outline-dark" target="_blank" rel="noopener">
                                                        <i class="fa fa-file-pdf mr-1"></i> Handover
                                                    </a>
                                                    @if ($canSign)
                                                        <a href="{{ route('company-assets.upload-signature', [$a->id]) }}" class="btn btn-xs btn-success">
                                                            <i class="fa fa-upload mr-1"></i> Signature
                                                        </a>
                                                    @endif
                                                    @if ($canEditAssign)
                                                        <a href="{{ route('company-assets.edit-assign', [$a->id]) }}" class="btn btn-xs btn-primary openRightModal">
                                                            <i class="fa fa-edit mr-1"></i> @lang('app.edit')
                                                        </a>
                                                        <a href="javascript:;" class="btn btn-xs btn-danger delete-assignment" data-assignment-id="{{ $a->id }}">
                                                            <i class="fa fa-trash mr-1"></i> @lang('app.delete')
                                                        </a>
                                                    @endif

                                                {{-- ASSIGNED (signed, out) --}}
                                                @elseif ($serial->status === $S_ASSIGNED && $a)
                                                    <a href="{{ route('company-assets.generate-pdf', $a->id) }}" class="btn btn-xs btn-outline-dark" target="_blank" rel="noopener">
                                                        <i class="fa fa-file-pdf mr-1"></i> Handover
                                                    </a>
                                                    <a href="{{ route('company-assets.return-pdf', $a->id) }}" class="btn btn-xs btn-outline-dark" target="_blank" rel="noopener">
                                                        <i class="fa fa-file-pdf mr-1"></i> Return PDF
                                                    </a>
                                                    @if ($canSign)
                                                        <a href="{{ route('company-assets.return', [$a->id]) }}" class="btn btn-xs btn-warning">
                                                            <i class="fa fa-undo mr-1"></i> Return
                                                        </a>
                                                    @endif

                                                {{-- LOST / DAMAGED / RETIRED --}}
                                                @elseif ($canFlagSerials)
                                                    <a class="btn btn-xs btn-outline-success serial-status" data-serial="{{ $serial->id }}" data-status="available">
                                                        <i class="fa fa-undo mr-1"></i> Restore
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">@lang('messages.noRecordFound')</td></tr>
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
    (function () {
        function swal(text, cb) {
            Swal.fire({
                title: "@lang('messages.sweetAlertTitle')", text: text, icon: 'warning',
                showCancelButton: true, focusConfirm: false,
                confirmButtonText: "@lang('messages.confirmDelete')", cancelButtonText: "@lang('app.cancel')",
                customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }, buttonsStyling: false
            }).then((r) => { if (r.isConfirmed) cb(); });
        }

        $('body').on('click', '#company-asset-detail .delete-assignment', function () {
            var id = $(this).data('assignment-id');
            swal("@lang('messages.recoverRecord')", function () {
                var url = "{{ route('company-assets.delete-assign', ':id') }}".replace(':id', id);
                $.easyAjax({ type: 'GET', url: url, blockUI: true, success: function () { window.location.reload(); } });
            });
        });

        $('body').on('click', '#company-asset-detail .serial-status', function () {
            var serial = $(this).data('serial'), status = $(this).data('status');
            swal("Change this unit's status to \"" + status + "\"?", function () {
                var url = "{{ route('company-assets.serial-status', ['serialId' => 0]) }}".replace('/serial/0/status', '/serial/' + serial + '/status');
                $.easyAjax({
                    type: 'POST', url: url, blockUI: true,
                    data: { _token: "{{ csrf_token() }}", status: status },
                    success: function () { window.location.reload(); }
                });
            });
        });
    })();
</script>
