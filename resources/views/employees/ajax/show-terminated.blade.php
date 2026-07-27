<div id="terminated-employee-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header form-heading-background border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('app.employeeDetail')</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-right d-flex justify-content-end mb-3">
                        <a href="{{ route('employees.index') }}?tab=terminated" class="btn btn-sm btn-primary">Back</a>
                    </div>

                    <x-cards.data-row :label="__('modules.employees.employeeId')" :value="$employee->employeeDetail->employee_id ?? '--'" />
                    <x-cards.data-row :label="__('modules.employees.fullName')" :value="$employee->name" />
                    <x-cards.data-row :label="__('app.email')" :value="$employee->email" />
                    <x-cards.data-row :label="__('app.designation')" :value="$employee->employeeDetail->designation->name ?? '--'" />
                    <x-cards.data-row :label="__('app.department')" :value="$employee->employeeDetail->department->team_name ?? '--'" />
                    <x-cards.data-row label="Notice Period Start" :value="$employee->employeeDetail->notice_period_start_date
                        ? \Carbon\Carbon::parse($employee->employeeDetail->notice_period_start_date)->translatedFormat(
                            company()->date_format,
                        )
                        : '--'" />
                    <x-cards.data-row label="Notice Period End" :value="$employee->employeeDetail->notice_period_end_date
                        ? \Carbon\Carbon::parse($employee->employeeDetail->notice_period_end_date)->translatedFormat(
                            company()->date_format,
                        )
                        : '--'" />
                    <x-cards.data-row label="Status" value="Terminated" />
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mt-3">
                <div class="card-header form-heading-background border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">Termination Summary</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($termination)
                        <x-cards.data-row label="Initiated By" :value="$termination->initiatedBy->name ?? '--'" />
                        <x-cards.data-row label="IT Clearance" :value="ucfirst($termination->it_clearance_status)" />
                        <x-cards.data-row label="IT Clearance Issued By" :value="$termination->itClearanceIssuedBy->name ?? '--'" />
                        <x-cards.data-row label="Finance Clearance" :value="ucfirst($termination->finance_clearance_status)" />
                        <x-cards.data-row label="Finance Clearance Issued By" :value="$termination->financeClearanceIssuedBy->name ?? '--'" />
                        <x-cards.data-row label="Completed By" :value="$termination->completedBy->name ?? '--'" />
                        <x-cards.data-row label="Completed On" :value="$termination->completed_at ? $termination->completed_at->translatedFormat(company()->date_format) : '--'" />

                        <div class="mt-3">
                            @if ($termination->it_clearance_status == 'issued')
                                <a href="{{ route('employees.it-clearance.letter', $employee->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fa fa-file-pdf-o mr-1"></i> IT Clearance Letter
                                </a>
                            @endif

                            @if ($termination->finance_clearance_status == 'issued')
                                <a href="{{ route('employees.finance-clearance.letter', $employee->id) }}" class="btn btn-sm btn-primary ml-2">
                                    <i class="fa fa-file-pdf-o mr-1"></i> Finance Clearance Letter
                                </a>
                            @endif
                        </div>
                    @else
                        <p class="text-center mb-0">No termination record found.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
