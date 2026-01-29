@php
    $editCoordinatorReportPermission = user()->permission('edit_coordinator_reports');
@endphp
<div class="task_view">
    <div class="dropdown">
        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
            id="dropdownMenuLink-{{ $id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="icon-options-vertical icons"></i>
        </a>
        @if ($editCoordinatorReportPermission == 'all' && !$admin_submitted)
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-{{ $id }}"
                tabindex="0">


                <a class="dropdown-item openRightModal" href="{{ route('coordinator-report.edit', [$id]) }}">
                    <i class="fa fa-edit mr-2"></i>
                    {{ trans('app.edit') }}
                </a>
            </div>
        @endif
    </div>
</div>
