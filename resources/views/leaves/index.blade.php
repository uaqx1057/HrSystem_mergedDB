@extends('layouts.app')

@push('datatable-styles')
    @include('sections.datatable_css')
    <style>
        .filter-box { z-index: 2; }
        .tab-leave-btn { border-radius: 6px; font-size: 13px; padding: 6px 18px; }
        .tab-leave-btn.active { background: #1d82f5; color: #fff; border-color: #1d82f5; }
        #summary-table thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; }
        .leave-progress { height: 6px; border-radius: 3px; }
        #tab-summary .datatable-checkboxes,
#tab-summary input[type="checkbox"] {
    display: none !important;
}

    .nav-tabs .nav-link.active{
        background: @if(!user()->dark_theme)
            radial-gradient(circle at 14% 18%, rgba(217, 119, 6, 0.16), transparent 22%),
            radial-gradient(circle at 84% 20%, rgba(5, 150, 105, 0.26), transparent 24%),
            radial-gradient(circle at 70% 82%, rgba(6, 182, 212, 0.18), transparent 22%),
            linear-gradient(135deg, #010e09 0%, #021810 38%, #031f14 100%);
        @else

        @endif;

        color: #fff !important;
    }
    .nav-tabs .nav-link{
        background-color: transparent !important;
    }

    </style>
@endpush

@php
    $addLeavePermission = user()->permission('add_leave');
    $approveRejectPermission = user()->permission('approve_or_reject_leaves');
@endphp

@section('filter-section')
    {{-- Only show filters when on the leaves list tab --}}
    <div id="filter-section-wrapper">
        <x-filters.filter-box>
            <div class="select-box d-flex pr-2 border-right-grey border-right-grey-sm-0">
                <p class="mb-0 pr-2 f-14 text-dark-grey d-flex align-items-center">@lang('app.duration')</p>
                <div class="select-status d-flex">
                    <input type="text" class="position-relative text-dark form-control border-0 p-2 text-left f-14 f-w-500 border-additional-grey"
                        id="datatableRange" placeholder="@lang('placeholders.dateRange')"
                        value="{{ request('start') && request('end') ? request('start') . ' ' . __('app.to') . ' ' . request('end') : '' }}">
                </div>
            </div>

            <div class="task-search d-flex py-1 px-lg-2 px-0 border-right-grey align-items-center">
                <form class="w-100 mr-1 mr-lg-0 mr-md-1 ml-md-1 ml-0 ml-lg-0">
                    <div class="input-group bg-grey rounded">
                        <div class="input-group-prepend">
                            <span class="input-group-text border-0 bg-additional-grey">
                                <i class="fa fa-search f-13 text-dark-grey"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control f-14 p-1 border-additional-grey" id="search-text-field"
                            placeholder="@lang('app.startTyping')">
                    </div>
                </form>
            </div>

            <div class="select-box d-flex py-1 px-lg-2 px-md-2 px-0">
                <x-forms.button-secondary class="btn-xs d-none" id="reset-filters" icon="times-circle">
                    @lang('app.clearFilters')
                </x-forms.button-secondary>
            </div>

            <x-filters.more-filter-box>
                <div class="more-filter-items">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize">@lang('app.employee')</label>
                    <div class="select-filter mb-4">
                        <select class="form-control select-picker" name="employee_id" id="employee_id"
                            data-live-search="true" data-container="body" data-size="8">
                            @if ($employees->count() > 1 || in_array('admin', user_roles()))
                                <option value="all">@lang('app.all')</option>
                            @endif
                            @foreach ($employees as $employee)
                                <x-user-option :user="$employee" />
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="more-filter-items">
                    <label class="f-14 text-dark-grey mb-12 text-capitalize">@lang('modules.leaves.leaveType')</label>
                    <div class="select-filter mb-4">
                        <select class="form-control select-picker" name="leave_type" id="leave_type"
                            data-live-search="true" data-container="body" data-size="8">
                            <option value="all">@lang('app.all')</option>
                            @foreach ($leaveTypes as $leaveType)
                                <option value="{{ $leaveType->id }}">{{ $leaveType->type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($approveRejectPermission == 'all')
                    <div class="more-filter-items">
                        <label class="f-14 text-dark-grey mb-12 text-capitalize">@lang('app.status')</label>
                        <div class="select-filter mb-4">
                            <select class="form-control select-picker" name="status" id="status"
                                data-live-search="true" data-container="body" data-size="8">
                                <option value="all">@lang('app.all')</option>
                                <option {{ request('status') == 'approved' ? 'selected' : '' }} value="approved">@lang('app.approved')</option>
                                <option value="pending">@lang('app.pending')</option>
                                <option value="rejected">@lang('app.rejected')</option>
                            </select>
                        </div>
                    </div>
                @endif
            </x-filters.more-filter-box>
        </x-filters.filter-box>
    </div>
@endsection


@section('content')
<div class="content-wrapper">

    {{-- Action bar --}}
    <div class="d-grid d-lg-flex d-md-flex action-bar">
        <div id="table-actions" class="flex-grow-1 align-items-center">
            @if ($addLeavePermission == 'all' || $addLeavePermission == 'added')
                <x-forms.link-primary :link="route('leaves.create')" class="mr-3 openRightModal float-left" icon="plus">
                    @lang('modules.leaves.addLeave')
                </x-forms.link-primary>
            @endif
        </div>

        <x-datatable.actions>
            <div class="select-status mr-3 pl-3">
                <select name="action_type" class="form-control select-picker" id="quick-action-type" disabled>
                    <option value="">@lang('app.selectAction')</option>
                    @if ($approveRejectPermission == 'all')
                        <option value="change-leave-status">@lang('app.changeLeaveStatus')</option>
                    @endif
                    <option value="delete">@lang('app.delete')</option>
                </select>
            </div>
            <div class="select-status mr-3 d-none quick-action-field" id="change-status-action">
                <select name="status" class="form-control select-picker">
                    <option value="approved">@lang('app.approved')</option>
                    <option value="pending">@lang('app.pending')</option>
                    <option value="rejected">@lang('app.rejected')</option>
                </select>
            </div>
        </x-datatable.actions>

        <div class="btn-group mt-2 mt-lg-0 mt-md-0 ml-0 ml-lg-3 ml-md-3" role="group">
            <a href="{{ route('leaves.index') }}" class="btn btn-secondary f-14 btn-active" data-toggle="tooltip"
                data-original-title="@lang('modules.leaves.tableView')"><i class="side-icon bi bi-list-ul"></i></a>
            <a href="{{ route('leaves.calendar') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                data-original-title="@lang('app.menu.calendar')"><i class="side-icon bi bi-calendar"></i></a>
            <a href="{{ route('leaves.personal') }}" class="btn btn-secondary f-14" data-toggle="tooltip"
                data-original-title="@lang('modules.leaves.myLeaves')"><i class="side-icon bi bi-person"></i></a>
        </div>
    </div>

    {{-- TABS --}}
    <ul class="nav nav-tabs mt-3 border-bottom-0" id="leaveTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active f-15 f-w-500 text-dark" id="tab-summary-link" data-toggle="tab" href="#tab-summary" role="tab">
                <i class="bi bi-people mr-1"></i> @lang('app.employee') @lang('app.summary')
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link f-15 f-w-500 text-dark" id="tab-list-link" data-toggle="tab" href="#tab-list" role="tab">
                <i class="bi bi-list-ul mr-1"></i> @lang('modules.leaves.leaveRequests')
            </a>
        </li>
    </ul>

    <div class="tab-content">

        {{-- TAB 1: Employee Summary --}}
        <div class="tab-pane fade show active" id="tab-summary" role="tabpanel">
            <div class="d-flex flex-column w-tables rounded bg-white">
                <div class="p-3 d-flex align-items-center border-bottom">
                    <div class="input-group bg-grey rounded" style="max-width:280px;">
                        <div class="input-group-prepend">
                            <span class="input-group-text border-0 bg-additional-grey">
                                <i class="fa fa-search f-13 text-dark-grey"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control f-14 p-1 border-additional-grey"
                            id="summary-search" placeholder="@lang('app.startTyping')">
                    </div>
                    <div class="ml-auto text-muted f-13" id="summary-count"></div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover border-0 w-100" id="summary-table">
                        <thead>
                            <tr>
                                <th class="pl-4">@lang('app.employee')</th>
                                <th class="text-center">@lang('modules.leaves.leavesGiven')</th>
                                <th class="text-center">@lang('modules.leaves.leavesTaken')</th>
                                <th class="text-center">@lang('modules.leaves.leavesRemaining')</th>
                            </tr>
                        </thead>
                        <tbody id="summary-tbody">
                            <tr>
                                <td colspan="4" class="text-center py-4">
                                    <div class="spinner-border text-primary spinner-border-sm" role="status"></div>
                                    <span class="ml-2 text-muted">Loading...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- TAB 2: Leave Requests (existing datatable) --}}
        <div class="tab-pane fade" id="tab-list" role="tabpanel">
            <div class="d-flex flex-column w-tables rounded bg-white">
                {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
@include('sections.datatable_js')

<script>
// ─── Summary Tab ──────────────────────────────────────────────────────────────
let summaryData = [];

function renderSummary(data) {
    const tbody = $('#summary-tbody');
    tbody.empty();

    if (!data.length) {
        tbody.append('<tr><td colspan="4" class="text-center text-muted py-4">No records found.</td></tr>');
        $('#summary-count').text('');
        return;
    }

    $('#summary-count').text(data.length + ' employees');

    data.forEach(function(emp) {
        const pct = emp.given > 0 ? Math.min(100, Math.round((emp.taken / emp.given) * 100)) : 0;
        const badgeClass = emp.remaining <= 0 ? 'badge-danger' : (emp.remaining <= 2 ? 'badge-warning' : 'badge-success');

        tbody.append(`
            <tr>
                <td class="pl-4">
                    <div class="d-flex align-items-center">
                        <a href="/employees/${emp.id}/profile" class="text-dark f-14 f-w-500">${emp.name}</a>
                    </div>
                </td>
                <td class="text-center f-14 f-w-500">${emp.given}</td>
                <td class="text-center">
                    <div class="d-flex flex-column align-items-center">
                        <span class="f-14 f-w-500 mb-1">${emp.taken}</span>
                        <div class="progress leave-progress w-75">
                            <div class="progress-bar bg-primary" style="width:${pct}%"></div>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge ${badgeClass} f-13 px-2 py-1">${emp.remaining}</span>
                </td>
            </tr>
        `);
    });
}

function loadSummary() {
    $.get("{{ route('leaves.employee_summary') }}", function(res) {
        summaryData = res.data || [];
        renderSummary(summaryData);
    }).fail(function() {
        $('#summary-tbody').html('<tr><td colspan="4" class="text-center text-danger py-4">Failed to load data.</td></tr>');
    });
}

$('#summary-search').on('keyup', function() {
    const q = $(this).val().toLowerCase();
    const filtered = summaryData.filter(e => e.name.toLowerCase().includes(q));
    renderSummary(filtered);
});

// Load summary on page load
loadSummary();

// ─── Datatable (Tab 2) ────────────────────────────────────────────────────────
$('#leaves-table').on('preXhr.dt', function(e, settings, data) {
    @if (request('start') && request('end'))
        $('#datatableRange').data('daterangepicker').setStartDate("{{ request('start') }}");
        $('#datatableRange').data('daterangepicker').setEndDate("{{ request('end') }}");
    @endif

    var drp = $('#datatableRange').data('daterangepicker');
    let startDate = $('#datatableRange').val();
    let endDate;

    if (startDate == '') { startDate = null; endDate = null; }
    else {
        startDate = drp.startDate.format('{{ company()->moment_date_format }}');
        endDate   = drp.endDate.format('{{ company()->moment_date_format }}');
    }

    data['startDate']   = startDate;
    data['endDate']     = endDate;
    data['searchText']  = $('#search-text-field').val();
    data['employeeId']  = $('#employee_id').val();
    data['leaveTypeId'] = $('#leave_type').val();
    data['status']      = $('#status').val();
});
// Delay datatable init until tab 2 is shown
let datatableInitialized = false;

$('#tab-list-link').on('shown.bs.tab', function () {
    if (!datatableInitialized) {
        datatableInitialized = true;
        // Re-render the datatable container
        window.LaravelDataTables["leaves-table"].draw(false);
    }
});

const showTable = () => { window.LaravelDataTables["leaves-table"].draw(false); }

$('#start-date, #end-date, #employee_id, #leave_type, #status').on('change keyup', function() {
    const hasFilter = $('#start-date').val() || $('#end-date').val()
        || $('#employee_id').val() != 'all' || $('#leave_type').val() != 'all' || $('#status').val() != 'all';
    hasFilter ? $('#reset-filters').removeClass('d-none') : $('#reset-filters').addClass('d-none');
    showTable();
});

$('#search-text-field').on('keyup', function() {
    if ($(this).val()) $('#reset-filters').removeClass('d-none');
    showTable();
});

$('#reset-filters, #reset-filters-2').click(function() {
    $('#filter-form')[0].reset();
    $('.filter-box #status, .filter-box #leave_type').val('all');
    $('.filter-box .select-picker').selectpicker("refresh");
    $('#reset-filters').addClass('d-none');
    showTable();
});

$('#quick-action-type').change(function() {
    const v = $(this).val();
    if (v) {
        $('#quick-action-apply').removeAttr('disabled');
        v == 'change-leave-status'
            ? ($('.quick-action-field').addClass('d-none'), $('#change-status-action').removeClass('d-none'))
            : $('.quick-action-field').addClass('d-none');
    } else {
        $('#quick-action-apply').attr('disabled', true);
        $('.quick-action-field').addClass('d-none');
    }
});

$('#quick-action-apply').click(function() {
    const actionValue = $('#quick-action-type').val();
    if (actionValue == 'delete') {
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')", text: "@lang('messages.recoverRecord')", icon: 'warning',
            showCancelButton: true, confirmButtonText: "@lang('messages.confirmDelete')", cancelButtonText: "@lang('app.cancel')",
            customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
            showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }, buttonsStyling: false
        }).then(r => { if (r.isConfirmed) applyQuickAction(); });
    } else { applyQuickAction(); }
});

$('body').on('click', '.delete-table-row', function() {
    const id = $(this).data('leave-id'), uniId = $(this).data('unique-id'),
          duration = $(this).data('duration'), type = $(this).data('type');
    Swal.fire({
        title: "@lang('messages.sweetAlertTitle')", text: "@lang('messages.recoverRecord')", icon: 'warning',
        showCancelButton: true, confirmButtonText: "@lang('messages.confirmDelete')", cancelButtonText: "@lang('app.cancel')",
        customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
        showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }, buttonsStyling: false
    }).then(r => {
        if (r.isConfirmed) {
            let url = "{{ route('leaves.destroy', ':id') }}".replace(':id', id);
            $.easyAjax({ type: 'POST', url, blockUI: true,
                data: { uniId, duration, _token: "{{ csrf_token() }}", _method: 'DELETE' },
                success: res => { if (res.status == 'success') { type == 'multiple-leave' ? window.location.reload() : showTable(); } }
            });
        }
    });
});

const applyQuickAction = () => {
    const rowdIds = $("#leaves-table input:checkbox:checked").map(function() { return $(this).val(); }).get();
    $.easyAjax({
        url: "{{ route('leaves.apply_quick_action') }}?row_ids=" + rowdIds,
        container: '#quick-action-form', type: "POST", disableButton: true,
        buttonSelector: "#quick-action-apply", data: $('#quick-action-form').serialize(),
        success: res => { if (res.status == 'success') { showTable(); resetActionButtons(); deSelectAll(); $('#quick-action-form').hide(); } }
    });
};

$('body').on('click', '.show-leave', function() {
    const url = '{{ route('leaves.show', ':id') }}'.replace(':id', $(this).data('leave-id'));
    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
    $.ajaxModal(MODAL_LG, url);
});

$('body').on('click', '.leave-action-approved', function() {
    const type = $(this).data('type') || 'single';
    const url = "{{ route('leaves.show_approved_modal') }}?leave_action=" + $(this).data('leave-action') + "&leave_id=" + $(this).data('leave-id') + "&type=" + type;
    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
    $.ajaxModal(MODAL_LG, url);
});

$('body').on('click', '.leave-action-reject', function() {
    const type = $(this).data('type') || 'single';
    const url = "{{ route('leaves.show_reject_modal') }}?leave_action=" + $(this).data('leave-action') + "&leave_id=" + $(this).data('leave-id') + "&type=" + type;
    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
    $.ajaxModal(MODAL_LG, url);
});

$('body').on('click', '.leave-action-preapprove', function() {
    const action = $(this).data('leave-action'), leaveId = $(this).data('leave-id');
    Swal.fire({
        title: "@lang('messages.sweetAlertTitle')", text: "@lang('messages.changeLeaveStatusConfirmation')", icon: 'warning',
        showCancelButton: true, confirmButtonText: "@lang('messages.confirm')", cancelButtonText: "@lang('app.cancel')",
        customClass: { confirmButton: 'btn btn-primary mr-3', cancelButton: 'btn btn-secondary' },
        showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' }, buttonsStyling: false
    }).then(r => {
        if (r.isConfirmed) {
            $.easyAjax({ type: 'POST', url: "{{ route('leaves.pre_approve_leave') }}", blockUI: true,
                data: { action, leaveId, _token: '{{ csrf_token() }}' },
                success: res => { if (res.status == 'success') { showTable(); resetActionButtons(); deSelectAll(); } }
            });
        }
    });
});

$('body').on('click', '.view-related-leave', function() {
    const url = "{{ route('leaves.view_related_leave', ':id') }}?uniqueId=" + $(this).data('unique-id');
    $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
    $.ajaxModal(MODAL_LG, url.replace(':id', $(this).data('leave-id')));
});
</script>
@endpush
