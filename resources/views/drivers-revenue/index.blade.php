@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')

    <x-filters.filter-box>
        <div class="py-1 px-lg-3 px-0 align-items-center d-flex">
            <h4 class="mb-0">Filters</h4>
            <select name="business_id" id="business_id" class="form-control ml-1">
                <option value="">Select Business</option>
                @foreach ($businesses as $business)
                    <option value="{{ $business->id }}">{{ $business->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="py-1 px-lg-3 px-0 align-items-center d-flex">
            <select name="driver_type_id" id="driver_type_id" class="form-control ml-1">
                <option value="">Select Driver Type</option>
                @foreach ($driver_types as $driver)
                    <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="py-1 px-lg-3 px-0 align-items-center d-flex">
            <select name="branch_id" id="branch_id" class="form-control ml-1">
                <option value="">Select Branch</option>
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        <!-- DATE START -->
        <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
            <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
            <div class="select-status d-flex">
                <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                    id="datatableRange" placeholder="@lang('placeholders.dateRange')">
            </div>
        </div>
        <!-- DATE END -->

        <!-- RESET START -->
        <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
            <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                @lang('app.clearFilters')
            </x-forms.button-secondary>
        </div>
        <!-- RESET END -->


    </x-filters.filter-box>

@endsection

@php
    $addDriverPermission = user()->permission('add_drivers');
@endphp



@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="row">
                <div class="col-12">
                    <h4>Business Report</h4>
                </div>
                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <a href="{{ route('clients.index') }}">
                        <x-cards.widget :title="__('modules.dashboard.totalRevenue')" :widgetId="$totalRevenue = 'totalRevenue'" :value="number_format($total_revenue, 2)"
                            icon="users">
                        </x-cards.widget>
                    </a>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <a href="{{ route('employees.index') }}">
                        <x-cards.widget :title="__('modules.dashboard.totalCost')" :widgetId="$totalCost = 'totalCost'" :value="number_format($total_cost, 2)"
                            icon="user">
                        </x-cards.widget>
                    </a>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <a href="{{ route('projects.index'). '?projects=all' }}">
                        <x-cards.widget :title="__('modules.dashboard.grossProfit')" :widgetId="$grossProfit = 'grossProfit'" :value="number_format($gross_profile, 2)"
                            icon="layer-group">
                        </x-cards.widget>
                    </a>
                </div>

                <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                    <a href="{{ route('invoices.index') . '?status=pending' }}">
                        <x-cards.widget :title="__('modules.dashboard.totalOrders')"
                            :widgetId="$totalOrders = 'totalOrders'"
                            :value="number_format($total_orders)" icon="file-invoice">
                        </x-cards.widget>
                    </a>
                </div>
                <div class="col-12">
                    <h4>Order Report</h4>
                </div>


                <div class="row col-12 p-0 m-0" id="business_with_orders">
                    @foreach ($businesses as $business)
                        <div class="col-xl-3 col-lg-6 col-md-6 mb-3">
                            <a href="javascript:;">
                                <x-cards.widget :title="$business->name" :value="$business->total_orders"
                                    icon="clock">
                                </x-cards.widget>
                            </a>
                        </div>
                    @endforeach
                </div>
        </div>
        <!-- Task Box Start -->
        <div class="d-flex flex-column w-tables rounded mt-3 bg-white table-responsive">
            {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
        </div>
        <!-- Task Box End -->
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')
    @include('sections.datatable_js')

    <script>
        $('#drivers-revenue-report-table').on('preXhr.dt', function (e, settings, data) {
            const dateRangePicker = $('#datatableRange').data('daterangepicker');
            let startDate = null;
            let endDate = null;

            if ($('#datatableRange').val() !== '') {
                startDate = dateRangePicker.startDate.format('YYYY-MM-DD');
                endDate = dateRangePicker.endDate.format('YYYY-MM-DD');
            }
            const business_id = $('#business_id').val();
            const driver_type_id = $('#driver_type_id').val();
            const branch_id = $('#branch_id').val();
            data['searchText'] =  $('#search-text-field').val();
            data['startDate'] = startDate;
            data['endDate'] = endDate;
            data['business_id'] = business_id;
            data['driver_type_id'] = driver_type_id;
            data['branch_id'] = branch_id;
        });

        const showTable = () => {
            window.LaravelDataTables["drivers-revenue-report-table"].draw(false);
        }

        $('#searchText, #datatableRange').on('change',
            function () {
                if ($('#datatableRange').val() != "") {
                    $('#reset-filters').removeClass('d-none');
                } else if ($('#searchText').val() != "") {
                    $('#reset-filters').removeClass('d-none');
                } else {
                    $('#reset-filters').addClass('d-none');
                }
                showTable();
            });

        $('#search-text-field').on('keyup', function () {
            if ($('#search-text-field').val() != "") {
                $('#reset-filters').removeClass('d-none');
                showTable();
            }
        });

        $('#reset-filters, #reset-filters-2').click(function () {
            $('#filter-form')[0].reset();
            $('.filter-box .select-picker').selectpicker("refresh");
            $('#reset-filters').addClass('d-none');
            showTable();
        });

    </script>
@endpush
