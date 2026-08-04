<div class="tab-pane fade show active mt-5" role="tabpanel" aria-labelledby="nav-email-tab">


    <div class="d-flex justify-content-between align-items-center mb-3">

        @if (in_array(user()->permission('assign_company_asset_to_employee'), ['all', 'added', 'branch']))
            <a href="{{ route('employees.assign-company-asset', $companyAssetEmployeeId) }}"
                class="btn btn-sm btn-primary openRightModal">
                <i class="fa fa-plus mr-1"></i> {{ __('app.assign') }}
            </a>
        @endif

    </div>

    <div class="card">
        <div class="card-header form-heading-background d-flex justify-content-between align-items-center">
            <span>Assign Assets</span>
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered text-nowrap">
                <thead>
                    <tr>
                        <th>{{ __('app.menu.companyAssets') }}</th>
                        <th>{{ __('app.catalog') }}</th>
                        <th>{{ __('app.serialNo') }}</th>
                        <th>{{ __('app.qty') }}</th>
                        <th>{{ __('app.branchName') }}</th>
                        <th>{{ __('app.status') }}</th>
                        <th>{{ __('app.action') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($employeeAssetAssignments as $assignment)
                        @php
                            $asset = $assignment->asset;
                        @endphp

                        @if (!$asset)
                            @continue
                        @endif

                        <tr>
                            <td>{{ $asset->name }}</td>
                            <td>{{ $asset->catalog }}</td>
                            <td>{{ $assignment->serial_no ?: '--' }}</td>
                            <td>{{ $assignment->qty }}</td>
                            <td>{{ $asset->branch->name ?? 'N/A' }}</td>
                            <td>{{ ucfirst($assignment->status) }}</td>
                            <td>
                                @if (in_array(user()->permission('upload_signature_assign_company_assets_to_employee'), [
                                        'all',
                                        'added',
                                        'owned',
                                        'both',
                                        'branch',
                                    ]) &&
                                        $assignment->status === 'Pending' &&
                                        !$assignment->signed_document)
                                    <a href="{{ route('company-assets.upload-signature', [$assignment->id, 'employee_id' => $companyAssetEmployeeId]) }}"
                                        class="btn btn-sm btn-secondary mr-2">
                                        <i class="fa fa-upload mr-1"></i> {{ __('app.signature') }}
                                    </a>
                                @endif

                                @if (in_array(user()->permission('upload_signature_assign_company_assets_to_employee'), [
                                        'all',
                                        'added',
                                        'owned',
                                        'both',
                                        'branch',
                                    ]) && $assignment->signed_document)
                                    <a href="{{ route('company-assets.return', [$assignment->id, 'employee_id' => $companyAssetEmployeeId]) }}"
                                        class="btn btn-sm btn-secondary mr-2">
                                        <i class="fa fa-undo mr-1"></i> {{ __('app.return') }}
                                    </a>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">{{ __('messages.noRecordFound') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
