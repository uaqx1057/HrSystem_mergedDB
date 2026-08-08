@php
    $employeeDetail = $employee->employeeDetail;
@endphp

<div id="employee-onboard-detail">
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
                    <div class="text-right mb-3">
                        <a href="{{ route('employees.index') . '?tab=onboard' }}" class="btn btn-sm btn-primary">@lang('app.back')</a>
                    </div>

                    <x-cards.data-row :label="__('modules.employees.employeeId')" :value="$employeeDetail->employee_id ?? '--'" />
                    <x-cards.data-row :label="__('modules.employees.fullName')" :value="$employee->name" />
                    <x-cards.data-row :label="__('app.designation')" :value="$employeeDetail->designation->name ?? '--'" />
                    <x-cards.data-row :label="__('app.department')" :value="$employeeDetail->department->team_name ?? '--'" />
                    <x-cards.data-row :label="__('app.branchName')" :value="$employee->branch->name ?? '--'" />
                    <x-cards.data-row :label="__('modules.employees.gender')" :value="$employee->gender ?? '--'" />
                    <x-cards.data-row :label="__('app.email')" :value="$employee->email" />
                    <x-cards.data-row :label="__('app.mobile')" :value="$employee->mobile_with_phonecode" />
                    <x-cards.data-row :label="__('modules.employees.joiningDate')" :value="$employeeDetail->joining_date ? $employeeDetail->joining_date->translatedFormat(company()->date_format) : '--'" />
                    <x-cards.data-row :label="__('modules.employees.employeeType')" :value="$employeeDetail->employee_type ?? '--'" />
                    <x-cards.data-row :label="__('modules.employees.probationEndDate')" :value="$employeeDetail->probation_end_date ? \Carbon\Carbon::parse($employeeDetail->probation_end_date)->translatedFormat(company()->date_format) : '--'" />
                    <x-cards.data-row :label="__('app.address')" :value="$employeeDetail->address ?? '--'" />
                    <x-cards.data-row :label="__('app.language')" :value="$employee->locale ?? '--'" />

                    @if ($employeeDetail->getCustomFieldGroupsWithFields())
                        <x-forms.custom-field-show :fields="$employeeDetail->getCustomFieldGroupsWithFields()->fields" :model="$employeeDetail"></x-forms.custom-field-show>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
