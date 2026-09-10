@php
    $assignments = $assignments ?? collect();
    $history = $history ?? collect();
@endphp

<div id="assignment-history-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header bg-white border-bottom-grey p-20 d-flex justify-content-between align-items-center">
                    <h3 class="heading-h1 mb-0">{{ $asset->name }} &mdash; Assignments</h3>
                    <a href="{{ route('company-assets.show', $asset->id) }}" class="btn btn-sm btn-secondary openRightModal">
                        <i class="fa fa-arrow-left mr-1"></i> @lang('app.back')
                    </a>
                </div>

                <div class="card-body">
                    <x-cards.data-row :label="__('SKU No')" :value="$asset->sku_no ?: '--'" />
                    <x-cards.data-row :label="__('app.brand')" :value="$asset->brand ?: '--'" />
                    <x-cards.data-row :label="__('app.branchName')" :value="optional($asset->branch)->name ?? '--'" />

                    <h4 class="mt-4 mb-2 f-16">Current holders</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="text-uppercase f-11 text-dark-grey">
                                <tr>
                                    <th>@lang('app.serialNo')</th>
                                    <th>@lang('app.employee')</th>
                                    <th>@lang('app.status')</th>
                                    <th>@lang('app.signature')</th>
                                    <th>@lang('app.createdAt')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assignments as $a)
                                    @php $sig = $a->signed_document ? asset_url_local_s3('asset/' . $a->signed_document) : ''; @endphp
                                    <tr>
                                        <td>{{ $a->serial?->serial_no ?: ($a->serial_no ?: '--') }}</td>
                                        <td>{{ optional($a->employee)->name ?? 'N/A' }}</td>
                                        <td><span class="badge {{ $a->status === \App\Models\AssetAssignment::STATUS_ASSIGNED ? 'badge-info' : 'badge-warning' }}">{{ $a->status }}</span></td>
                                        <td>@if ($sig)<a href="{{ $sig }}" target="_blank" rel="noopener">@lang('app.view')</a>@else -- @endif</td>
                                        <td>{{ optional($a->created_at)->format('d M Y H:i') ?? '--' }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">@lang('messages.noRecordFound')</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h4 class="mt-4 mb-2 f-16">Full history</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="text-uppercase f-11 text-dark-grey">
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
                                    @php $sig = $record->signed_document ? asset_url_local_s3('asset/' . $record->signed_document) : ''; @endphp
                                    <tr>
                                        <td>{{ $record->serial?->serial_no ?: ($record->serial_no ?: '--') }}</td>
                                        <td>{{ optional($record->employee)->name ?? 'N/A' }}</td>
                                        <td>{{ ucfirst((string) $record->action_type) }}</td>
                                        <td>@if ($sig)<a href="{{ $sig }}" target="_blank" rel="noopener">@lang('app.view')</a>@else -- @endif</td>
                                        <td>{{ $record->action_at ? $record->action_at->format('d M Y H:i') : '--' }}</td>
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
