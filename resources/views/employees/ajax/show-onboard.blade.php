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

                    @php
                        $onboardingFlags = [
                            'verify_employee_profile' => 'Profile & documents verified',
                            'setup_bank_and_payroll' => 'Bank & payroll set up',
                            'assign_insurance' => 'Medical insurance assigned',
                            'assign_required_assets' => 'Required assets handed over',
                            'manager_confirmation' => 'Line manager confirmed',
                        ];
                        $completedFlags = collect($onboardingFlags)->keys()
                            ->filter(fn ($flag) => (bool) ($employeeDetail->{$flag} ?? false))
                            ->count();
                        $taskCount = ($onboardingTasks ?? collect())->count();
                        $completedTasks = ($onboardingTasks ?? collect())
                            ->filter(fn ($task) => in_array($task->status, ['completed', 'waived']))
                            ->count();
                    @endphp

                    <div class="border-top mt-4 pt-3">
                        <h4 class="f-16 font-weight-bold mb-2">Onboarding progress</h4>
                        <div class="d-flex justify-content-between f-13 mb-1">
                            <span>Checklist</span>
                            <span>{{ $completedFlags }} / {{ count($onboardingFlags) }} complete</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar"
                                style="width: {{ count($onboardingFlags) ? round($completedFlags / count($onboardingFlags) * 100) : 0 }}%"></div>
                        </div>

                        @foreach ($onboardingFlags as $flag => $label)
                            <div class="f-13 py-1">
                                <i class="fa fa-{{ ($employeeDetail->{$flag} ?? false) ? 'check text-success' : 'clock-o text-muted' }} mr-1"></i>
                                {{ $label }}
                            </div>
                        @endforeach

                        @if ($onboardingCase)
                            <div class="f-13 mt-3 mb-1">
                                Workflow case <strong>{{ $onboardingCase->reference ?? ('#' . $onboardingCase->id) }}</strong>
                                <span class="text-muted">({{ $onboardingCase->status }})</span>
                                @if ($taskCount)
                                    <span class="text-muted">&middot; {{ $completedTasks }} / {{ $taskCount }} tasks complete</span>
                                @endif
                            </div>
                            @foreach ($onboardingTasks as $task)
                                <div class="f-12 py-1 border-bottom">
                                    <i class="fa fa-{{ in_array($task->status, ['completed', 'waived']) ? 'check text-success' : 'clock-o text-muted' }} mr-1"></i>
                                    {{ $task->title }} <span class="text-muted">&mdash; {{ $task->status }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="f-12 text-muted mt-3">No linked workflow case has been created for this employee.</div>
                        @endif
                    </div>

                    @if ($employeeDetail->getCustomFieldGroupsWithFields())
                        <x-forms.custom-field-show :fields="$employeeDetail->getCustomFieldGroupsWithFields()->fields" :model="$employeeDetail"></x-forms.custom-field-show>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
