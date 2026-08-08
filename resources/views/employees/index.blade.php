@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
@endpush

@section('filter-section')
    {{-- @section('filter-section') --}}
    @php
        $viewEmployee = user()->permission('view_employees');
        $viewPendingTermination = user()->permission('view_pending_termination_employees');
        $viewTerminated = user()->permission('view_terminated_employees');
        $manageItClearance = user()->permission('manage_it_clearance');
        $manageFinanceClearance = user()->permission('manage_finance_clearance');
        $manageOnboarding = user()->permission('manage_onboarding_employees');
        $showPendingTerminationTab = (in_array($viewPendingTermination, ['all', 'added', 'owned', 'both', 'branch']))
            || in_array($manageItClearance, ['all', 'branch'])
            || in_array($manageFinanceClearance, ['all', 'branch']);
        $showOnboardTab = in_array($manageOnboarding, ['all', 'added', 'owned', 'both', 'branch']);
    @endphp
        <!-- FILTER START -->
        <!-- PROJECT HEADER STARTmplete -->
        <div class="d-flex filter-box project-header bg-white">

            <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>
            <div class="project-menu d-lg-flex" id="mob-client-detail">

                <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                    <i class="fa fa-times"></i>
                </a>

                @if ($showOnboardTab)
                    <x-tab :href="route('employees.index').'?tab=onboard'" :text="__('app.menu.onboard') ?? 'Onboard'" ajax="false" class="onboard" />
                @endif

                @if ($viewEmployee && $viewEmployee != 'none')
                    <x-tab :href="route('employees.index')" :text="__('app.menu.employees')" ajax="false" class="employee" />
                @endif

                @if ($showPendingTerminationTab)
                    <x-tab :href="route('employees.index').'?tab=pending-termination'" :text="__('app.menu.pendingTermination')" ajax="false" class="pending-termination" />
                @endif

                @if ($viewTerminated && $viewTerminated != 'none')
                    <x-tab :href="route('employees.index').'?tab=terminated'" :text="__('app.menu.terminated')" ajax="false" class="terminated" />
                @endif

            </div>

            <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey"
                onclick="openClientDetailSidebar()"><i class="fa fa-ellipsis-v "></i></a>

        </div>
        <!-- FILTER END -->
        <!-- PROJECT HEADER END -->

@endsection



@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        @include($view)
    </div>
    <!-- CONTENT WRAPPER END -->

@endsection

@push('scripts')

    <script>
        $("body").on("click", ".ajax-tab", function(event) {
            event.preventDefault();

            $('.project-menu .p-sub-menu').removeClass('active');
            $(this).addClass('active');


            const requestUrl = this.href;

            $.easyAjax({
                url: requestUrl,
                blockUI: true,
                container: ".content-wrapper",
                historyPush: true,
                success: function(response) {
                    if (response.status == "success") {
                        $('.content-wrapper').html(response.html);
                        init('.content-wrapper');
                    }
                }
            });
        });

    </script>
    <script>
        const activeTab = "{{ $activeTab }}";
        $('.project-menu .' + activeTab).addClass('active');

    </script>

@endpush
