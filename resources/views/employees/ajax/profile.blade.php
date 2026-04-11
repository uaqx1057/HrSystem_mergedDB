<script src="{{ asset('vendor/jquery/Chart.min.js') }}"></script>
<style>
    .card-img {
        width: 120px;
        height: 120px;
    }

    .card-img img {
        width: 120px;
        height: 120px;
        object-fit: cover;
    }
    .appreciation-count {
        top: -6px;
        right: 10px;
    }
</style>
@php

$showFullProfile = false;

if ($viewPermission == 'all'
    || ($viewPermission == 'added' && $employee->employeeDetail->added_by == user()->id)
    || ($viewPermission == 'owned' && $employee->employeeDetail->user_id == user()->id)
    || ($viewPermission == 'both' && ($employee->employeeDetail->user_id == user()->id || $employee->employeeDetail->added_by == user()->id))
) {
    $showFullProfile = true;
}

// Safely resolve marital_status whether stored as enum object or plain string
$rawMaritalStatus = $employee->employeeDetail->marital_status ?? null;
$storedMaritalStatus = ($rawMaritalStatus instanceof \App\Enums\MaritalStatus)
    ? $rawMaritalStatus->value
    : (string) $rawMaritalStatus;
$isMarried = $storedMaritalStatus === \App\Enums\MaritalStatus::Married->value;

// Dependants
$dependants = \App\Models\EmployeeDependant::where('employee_id', $employee->id)->get();
$allowance = \App\Models\EmployeeAllowance::where('employee_id', $employee->id)->get();

@endphp

@php
$editEmployeePermission = user()->permission('edit_employees');
$viewAppreciationPermission = user()->permission('view_appreciation');
@endphp

<div class="d-lg-flex">
    <div class="w-100 py-0 py-lg-3 py-md-0">
        <!-- ROW START -->
        <div class="row">
            <!--  USER CARDS START -->
            <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
                <div class="row">
                    <div class="col-xl-7 col-md-6 mb-4 mb-lg-0">

                        <x-cards.user :image="$employee->image_url">
                            <div class="row">
                                <div class="col-10">
                                    <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                                        {{ ($employee->salutation ? $employee->salutation->label() . ' ' : '') . $employee->name }}
                                        @isset($employee->country)
                                            <x-flag :country="$employee->country" />
                                        @endisset
                                    </h4>
                                </div>
                                @if ($editEmployeePermission == 'all' || ($editEmployeePermission == 'added' && $employee->employeeDetail->added_by == user()->id))
                                    <div class="col-2 text-right">
                                        <div class="dropdown">
                                            <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle"
                                                type="button" data-toggle="dropdown" aria-haspopup="true"
                                                aria-expanded="false">
                                                <i class="fa fa-ellipsis-h"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                                aria-labelledby="dropdownMenuLink" tabindex="0">
                                                <a class="dropdown-item openRightModal"
                                                    href="{{ route('employees.edit', $employee->id) }}">@lang('app.edit')</a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <p class="f-12 font-weight-normal text-dark-grey mb-0">
                                {{ !is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->designation) ? $employee->employeeDetail->designation->name : '' }}
                                &bull;
                                {{ isset($employee->employeeDetail) && !is_null($employee->employeeDetail->department) ? $employee->employeeDetail->department->team_name : '' }}
                            </p>

                            @if ($employee->status == 'Active')
                                <p class="card-text f-11 text-lightest">@lang('app.lastLogin')
                                    @if (!is_null($employee->last_login))
                                        {{ $employee->last_login->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                    @else
                                        --
                                    @endif
                                </p>
                            @else
                                <p class="card-text f-12 text-lightest">
                                    <x-status :value="__('app.inactive')" color="red" />
                                </p>
                            @endif

                            @if ($showFullProfile)
                                <div class="card-footer bg-white border-top-grey pl-0">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.openTasks')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $employee->open_tasks_count }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.menu.projects')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $employee->member_count }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 text-capitalize" for="usr">@lang('modules.employees.hoursLogged')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $hoursLogged }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 text-capitalize" for="usr">@lang('app.menu.tickets')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $employee->agents_count }}</p>
                                        </span>
                                    </div>
                                </div>
                            @endif
                        </x-cards.user>

                        @if ($employee->employeeDetail->about_me != '')
                            <x-cards.data :title="__('app.about')" class="mt-4">
                                <div>{{ $employee->employeeDetail->about_me }}</div>
                            </x-cards.data>
                        @endif

                        {{-- ── PROFILE INFO ──────────────────────────────────────── --}}
                        <x-cards.data :title="__('modules.client.profileInfo')" class="mt-4">
                            <x-cards.data-row :label="__('modules.employees.employeeId')"
                                :value="$employee->employeeDetail->employee_id ?? '--'" />

                            <x-cards.data-row :label="__('modules.employees.fullName')"
                                :value="$employee->name" />

                            <x-cards.data-row :label="__('app.designation')"
                                :value="$employee->employeeDetail->designation->name ?? '--'" />

                            <x-cards.data-row :label="__('app.department')"
                                :value="$employee->employeeDetail->department->team_name ?? '--'" />

                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('modules.employees.gender')</p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    <x-gender :gender='$employee->gender' />
                                </p>
                            </div>

                            @php
                                $currentyearJoiningDate = \Carbon\Carbon::parse(now(company()->timezone)->year.'-'.$employee->employeeDetail->joining_date->translatedFormat('m-d'));
                                if ($currentyearJoiningDate->copy()->endOfDay()->isPast()) {
                                    $currentyearJoiningDate = $currentyearJoiningDate->addYear();
                                }
                                $diffInHoursJoiningDate = now(company()->timezone)->floatDiffInHours($currentyearJoiningDate, false);
                            @endphp

                            <x-cards.data-row :label="__('modules.employees.workAnniversary')"
                                :value="(!is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->joining_date))
                                    ? (($diffInHoursJoiningDate > -23 && $diffInHoursJoiningDate <= 0) ? __('app.today') : $currentyearJoiningDate->longRelativeToNowDiffForHumans())
                                    : '--'" />

                            <x-cards.data-row :label="__('modules.employees.dateOfBirth')"
                                :value="$employee->employeeDetail->date_of_birth
                                    ? $employee->employeeDetail->date_of_birth->translatedFormat('d F')
                                    : '--'" />

                            @if ($showFullProfile)
                                <x-cards.data-row :label="__('app.email')" :value="$employee->email" />

                                <x-cards.data-row :label="__('app.mobile')"
                                    :value="$employee->mobile_with_phonecode" />

                                <x-cards.data-row :label="__('modules.employees.linkedinUsername')"
                                    :value="$employee->employeeDetail->slack_username
                                        ? '@'.$employee->employeeDetail->slack_username
                                        : '--'" />

                                <x-cards.data-row :label="__('modules.employees.hourlyRate')"
                                    :value="company()->currency->currency_symbol . ($employee->employeeDetail->hourly_rate ?? '0')" />

                                <x-cards.data-row :label="__('app.address')"
                                    :value="$employee->employeeDetail->address ?? '--'" />

                                <x-cards.data-row :label="__('app.language')"
                                    :value="$employeeLanguage->language_name ?? '--'" />

                                <x-cards.data-row :label="__('modules.employees.joiningDate')"
                                    :value="$employee->employeeDetail->joining_date
                                        ? $employee->employeeDetail->joining_date->translatedFormat(company()->date_format)
                                        : '--'" />

                                <x-cards.data-row :label="__('modules.employees.probationEndDate')"
                                    :value="$employee->employeeDetail->probation_end_date
                                        ? \Carbon\Carbon::parse($employee->employeeDetail->probation_end_date)->translatedFormat(company()->date_format)
                                        : '--'" />

                                <x-cards.data-row :label="__('modules.employees.noticePeriodStartDate')"
                                    :value="$employee->employeeDetail->notice_period_start_date
                                        ? \Carbon\Carbon::parse($employee->employeeDetail->notice_period_start_date)->translatedFormat(company()->date_format)
                                        : '--'" />

                                <x-cards.data-row :label="__('modules.employees.noticePeriodEndDate')"
                                    :value="$employee->employeeDetail->notice_period_end_date
                                        ? \Carbon\Carbon::parse($employee->employeeDetail->notice_period_end_date)->translatedFormat(company()->date_format)
                                        : '--'" />

                                <x-cards.data-row :label="__('modules.employees.maritalStatus')"
                                    :value="$storedMaritalStatus ? ucfirst($storedMaritalStatus) : '--'" />

                                <x-cards.data-row :label="__('modules.employees.employmentType')"
                                    :value="$employee->employeeDetail->employment_type
                                        ? __('modules.employees.' . $employee->employeeDetail->employment_type)
                                        : '--'" />
                                <x-cards.data-row :label="__('modules.employees.basic_salary')"
                                    :value="$employee->employeeDetail->basic_salary
                                        ? __($employee->employeeDetail->basic_salary)
                                        : '--'" />
                                <x-cards.data-row :label="__('modules.employees.vehicle_allocation')"
                                    :value="$employee->employeeDetail->vehicle_allocation
                                        ? __($employee->employeeDetail->vehicle_allocation)
                                        : '--'" />

                                @if($employee->employeeDetail->employment_type == 'internship')
                                    <x-cards.data-row :label="__('modules.employees.internshipEndDate')"
                                        :value="$employee->employeeDetail->internship_end_date
                                            ? \Carbon\Carbon::parse($employee->employeeDetail->internship_end_date)->translatedFormat(company()->date_format)
                                            : '--'" />
                                @endif

                                @if($employee->employeeDetail->employment_type == 'on_contract')
                                    <x-cards.data-row :label="__('modules.employees.contractEndDate')"
                                        :value="$employee->employeeDetail->contract_end_date
                                            ? \Carbon\Carbon::parse($employee->employeeDetail->contract_end_date)->translatedFormat(company()->date_format)
                                            : '--'" />
                                @endif

                                {{-- Custom fields data --}}
                                <x-forms.custom-field-show :fields="$fields" :model="$employee->employeeDetail"></x-forms.custom-field-show>
                            @endif
                        </x-cards.data>
                            @if ($allowance->count() > 0)
                                <x-cards.data :title="__('modules.employees.allowances')" class="mt-4">
                                    <div class="table-responsive">
                                        <table class="table table-hover f-14 mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>@lang('modules.employees.allowanceName')</th>
                                                    <th>@lang('modules.employees.allowanceAmount')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($allowance as $i => $al)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $al->name }}</td>
                                                        <td>{{ $al->amount ?? '--' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </x-cards.data>
                            @endif
                        {{-- ── NEW: IQAMA & PASSPORT INFO ───────────────────────── --}}
                        @if ($showFullProfile)
                            <x-cards.data :title="__('modules.employees.iqamaPassportInfo')" class="mt-4">

                                {{-- IQAMA --}}
                                <x-cards.data-row :label="__('modules.employees.Iqama No')"
                                    :value="$employee->employeeDetail->iqama_no ?? '--'" />


                                <x-cards.data-row :label="__('modules.employees.iqama_profession')"
                                    :value="$employee->employeeDetail->iqama_profession ?? '--'" />

                                <x-cards.data-row :label="__('modules.employees.iqama_expiry_date')"
                                    :value="$employee->employeeDetail->iqama_expiry_date
                                        ? \Carbon\Carbon::parse($employee->employeeDetail->iqama_expiry_date)->translatedFormat(company()->date_format)
                                        : '--'" />

                                @if ($employee->employeeDetail->iqama_image)
                                    <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                            @lang('modules.employees.iqama_image')</p>
                                        <p class="mb-0 text-dark-grey f-14 w-70">
                                            <a href="{{ asset('user-uploads/iqama/' . $employee->employeeDetail->iqama_image) }}"
                                               target="_blank">
                                                <img src="{{ asset('user-uploads/iqama/' . $employee->employeeDetail->iqama_image) }}"
                                                     style="max-width:120px; max-height:80px; object-fit:cover; border-radius:4px;">
                                            </a>
                                        </p>
                                    </div>
                                @endif

                                {{-- PASSPORT --}}
                                <x-cards.data-row :label="__('modules.employees.passport_no')"
                                    :value="$employee->employeeDetail->passport_no ?? '--'" />

                                <x-cards.data-row :label="__('modules.employees.passport_expiry_date')"
                                    :value="$employee->employeeDetail->passport_expiry_date
                                        ? \Carbon\Carbon::parse($employee->employeeDetail->passport_expiry_date)->translatedFormat(company()->date_format)
                                        : '--'" />

                                @if ($employee->employeeDetail->passport_image)
                                    <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                        <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                            @lang('modules.employees.passport_image')</p>
                                        <p class="mb-0 text-dark-grey f-14 w-70">
                                            <a href="{{ asset('user-uploads/passport/' . $employee->employeeDetail->passport_image) }}"
                                               target="_blank">
                                                <img src="{{ asset('user-uploads/passport/' . $employee->employeeDetail->passport_image) }}"
                                                     style="max-width:120px; max-height:80px; object-fit:cover; border-radius:4px;">
                                            </a>
                                        </p>
                                    </div>
                                @endif

                                {{-- SPONSOR --}}
                                <x-cards.data-row :label="__('modules.employees.Sponsor / kafala')"
                                    :value="$employee->employeeDetail->sponsor_kafala ?? '--'" />

                                <x-cards.data-row :label="__('modules.employees.sponsorship_transfer_date')"
                                    :value="$employee->employeeDetail->sponsorship_transfer_date
                                        ? \Carbon\Carbon::parse($employee->employeeDetail->sponsorship_transfer_date)->translatedFormat(company()->date_format)
                                        : '--'" />

                            </x-cards.data>

                            {{-- ── NEW: DEPENDANTS ───────────────────────────────── --}}
                            @if ($isMarried && $dependants->count() > 0)
                                <x-cards.data :title="__('modules.employees.dependants')" class="mt-4">
                                    <div class="table-responsive">
                                        <table class="table table-hover f-14 mb-0">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>@lang('app.name')</th>
                                                    <th>@lang('modules.employees.Iqama No')</th>
                                                    <th>Relation</th>
                                                    <th>@lang('modules.employees.dateOfBirth')</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($dependants as $i => $dep)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ $dep->name }}</td>
                                                        <td>{{ $dep->iqama_no ?? '--' }}</td>
                                                        <td>{{ $dep->relation }}</td>
                                                        <td>{{ $dep->date_of_birth ? $dep->date_of_birth->translatedFormat(company()->date_format) : '--' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </x-cards.data>
                            @endif
                        @endif

                    </div>

                    <div class="col-xl-5 col-lg-6 col-md-6">

                        @if ($showFullProfile)
                            <x-cards.data class="mb-4" :title="__('modules.appreciations.appreciation')">
                                @forelse ($employee->appreciationsGrouped as $item)
                                    <div class="float-left position-relative mb-2" style="width: 50px"
                                         data-toggle="tooltip"
                                         data-original-title="@if(isset($item->award->title)){{ $item->award->title }}@endif">
                                        @if(isset($item->award->awardIcon->icon))
                                            <x-award-icon :award="$item->award" />
                                        @endif
                                        <span class="position-absolute badge badge-secondary rounded-circle border-additional-grey appreciation-count">{{ $item->no_of_awards }}</span>
                                    </div>
                                @empty
                                    <x-cards.no-record icon="medal" :message="__('messages.noRecordFound')" />
                                @endforelse
                            </x-cards.data>
                        @endif

                        <x-cards.data class="mb-4">
                            <div class="d-flex justify-content-between">
                                <div class="col-6">
                                    <p class="f-14 text-dark-grey">@lang('modules.employees.reportingTo')</p>
                                    @if ($employee->employeeDetail->reportingTo)
                                        <x-employee :user="$employee->employeeDetail->reportingTo" />
                                    @else
                                        --
                                    @endif
                                </div>
                                @if ($employee->reportingTeam)
                                    <div class="col-6">
                                        <p class="f-14 text-dark-grey">@lang('modules.employees.reportingTeam')</p>
                                        @if (count($employee->reportingTeam) > 0)
                                            @if (count($employee->reportingTeam) > 1)
                                                @foreach ($employee->reportingTeam as $item)
                                                    <div class="taskEmployeeImg rounded-circle mr-1">
                                                        <a href="{{ route('employees.show', $item->user->id) }}">
                                                            <img data-toggle="tooltip"
                                                                 data-original-title="{{ $item->user->name }}"
                                                                 src="{{ $item->user->image_url }}">
                                                        </a>
                                                    </div>
                                                @endforeach
                                            @else
                                                @foreach ($employee->reportingTeam as $item)
                                                    <x-employee :user="$item->user" />
                                                @endforeach
                                            @endif
                                        @else
                                            --
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </x-cards.data>

                        @if ($showFullProfile)
                            <div class="row">
                                @if (in_array('attendance', user_modules()))
                                    <div class="col-xl-6 col-sm-12 mb-4">
                                        <x-cards.widget :title="__('modules.dashboard.lateAttendanceMark')"
                                            :value="$lateAttendance" :info="__('modules.dashboard.thisMonth')"
                                            icon="map-marker-alt" />
                                    </div>
                                @endif
                                @if (in_array('leaves', user_modules()))
                                    <div class="col-xl-6 col-sm-12 mb-4">
                                        <x-cards.widget :title="__('modules.dashboard.leavesTaken')"
                                            :value="$leavesTaken" :info="__('modules.dashboard.thisMonth')"
                                            icon="sign-out-alt" />
                                    </div>

                                @endif
                                @if ($showFullProfile)
                                    @php
                                        $totalEarned  = collect($leaveHistory)->sum('earned');
                                        $totalTaken   = collect($leaveHistory)->sum('taken');
                                        $totalBalance = $totalEarned - $totalTaken;
                                    @endphp
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data title="Leaves" class="mt-4">

                                            {{-- Summary row --}}
                                            <div class="row f-13 mb-3 px-2">

                                                <div class="col-4">
                                                    <strong>Total Earned:</strong> <p>{{ number_format($totalEarned, 1) }} days</p>
                                                </div>

                                                <div class="col-4">
                                                    <strong>Total Taken:</strong><p>{{ number_format($totalTaken, 1) }} days</p>
                                                </div>

                                                <div class="col-4 {{ $totalBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                                    <strong>Balance:</strong> <p>{{ number_format($totalBalance, 1) }} days</p>
                                                </div>

                                            </div>

                                            {{-- Per month breakdown --}}
                                            {{-- @foreach ($leaveHistory as $row)
                                                <div class="col-12 px-0 pb-2 d-block d-lg-flex d-md-flex">
                                                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                                        {{ $row['month'] }}
                                                    </p>
                                                    <p class="mb-0 text-dark-grey f-14 w-70">
                                                        Earned: <strong>{{ number_format($row['earned'], 1) }}</strong> &nbsp;|&nbsp;
                                                        Taken: <strong>{{ number_format($row['taken'], 1) }}</strong> &nbsp;|&nbsp;
                                                        Balance:
                                                        <strong class="{{ $row['balance'] >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ number_format($row['balance'], 1) }}
                                                        </strong>
                                                    </p>
                                                </div>
                                            @endforeach --}}

                                        </x-cards.data>
                                    </div>

                                    {{-- Homeland ticket --}}
                                    @if (!empty($homelandTickets) && $homelandTickets > 0)
                                        <div class="col-md-12 mb-4">
                                            <x-cards.data title="Homeland Ticket" class="mt-4">
                                                <div class="row f-13 mb-3 px-2">

                                                    <div class="col-4">
                                                        <strong>Total Earned:</strong> <p>{{ $ticketHistory['total_earned'] }}</p>
                                                    </div>

                                                    <div class="col-4">
                                                        <strong>Total Taken:</strong><p>{{ $ticketHistory['total_used'] }}</p>
                                                    </div>

                                                    <div class="col-4 text-success }}">
                                                        <strong>Remaining:</strong> <p>{{ $ticketHistory['total_remaining'] }}</p>
                                                    </div>

                                                </div>
                                            </x-cards.data>
                                        </div>
                                    @endif
                                @endif

                                @if (!empty($employeeInsurances) && $employeeInsurances->count() > 0)
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data :title="__('app.menu.insurance')">
                                            @foreach ($employeeInsurances as $ins)
                                                <div class="row">

                                                    <div class="col-6 mb-2">
                                                        <span class="text-lightest f-14 w-30 text-capitalize">Policy No:</span><span class="text-dark-grey f-14 w-70"> {{ $ins->policy_no ?? '--' }}</span>
                                                    </div>

                                                    <div class="col-6 mb-2">
                                                        <span class="text-lightest f-14 w-30 text-capitalize">Company:</span><span class="text-dark-grey f-14 w-70"> {{ $ins->company ?? '--' }}</span>
                                                    </div>

                                                    <div class="col-6 mb-2">
                                                        <span class="text-lightest f-14 w-30 text-capitalize">Class:</span><span class="text-dark-grey f-14 w-70"> {{ $ins->class ?? '--' }}</span>
                                                    </div>

                                                    <div class="col-6 mb-2">
                                                        <span class="text-lightest f-14 w-30 text-capitalize">Issue Date:</span><span class="text-dark-grey f-14 w-70"> {{ $ins->issue_date ? $ins->issue_date->translatedFormat(company()->date_format) : '--' }}</span>
                                                    </div>

                                                    <div class="col-6 mb-2">
                                                        <span class="text-lightest f-14 w-30 text-capitalize">Expiry Date:</span><span class="text-dark-grey f-14 w-70"> {{ $ins->expiry_date ? $ins->expiry_date->translatedFormat(company()->date_format) : '--' }}</span>

                                                    </div>

                                                    <div class="col-6 mb-2">
                                                        <span class="text-lightest f-14 w-30 text-capitalize">Status:</span><span class="text-dark-grey f-14 w-70"> {{ ucfirst($ins->status ?? '--') }}</span>
                                                    </div>

                                                </div>

                                                @if (!$loop->last)
                                                    <hr class="my-2">
                                                @endif
                                            @endforeach
                                        </x-cards.data>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @if (in_array('tasks', user_modules()))
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data :title="__('app.menu.tasks')" padding="false">
                                            <x-pie-chart id="task-chart" :labels="$taskChart['labels']"
                                                :values="$taskChart['values']" :colors="$taskChart['colors']"
                                                height="250" width="300" />
                                        </x-cards.data>
                                    </div>
                                @endif
                                @if (in_array('tickets', user_modules()))
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data :title="__('app.menu.tickets')" padding="false">
                                            <x-pie-chart id="ticket-chart" :labels="$ticketChart['labels']"
                                                :values="$ticketChart['values']" :colors="$ticketChart['colors']"
                                                height="250" width="300" />
                                        </x-cards.data>
                                    </div>
                                @endif

                                @php $showButton = true; @endphp
                                <div class="col-md-12 mb-4">
                                    <x-cards.data :title="__('app.menu.change_password')" padding="false">
                                        <form method="POST" id="save-employee-data-form">
                                            @csrf
                                            <input type="hidden" name="email" value="{{ $employee->email }}">
                                            <div class="col-lg-12 mb-2">
                                                <x-forms.text fieldId="password"
                                                    :fieldLabel="__('app.menu.new_password')"
                                                    fieldName="password" fieldRequired="true"
                                                    :fieldPlaceholder="__('placeholders.password')"
                                                    :fieldValue="old('password')" :showButton="$showButton">
                                                </x-forms.text>
                                            </div>
                                            <div class="col-lg-12 mb-2">
                                                <button type="button" id="changePassowrdForm"
                                                    class="btn btn-primary btn-block">Change Password</button>
                                            </div>
                                        </form>
                                    </x-cards.data>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
            </div>
            <!--  USER CARDS END -->
        </div>
        <!-- ROW END -->
    </div>
</div>

<script>
$(document).ready(function () {
    $('#changePassowrdForm').click(function () {
        const url = "{{ route('employees.change-password') }}";
        var data = $('#save-employee-data-form').serialize();
        saveEmployee(data, url, "#changePassowrdForm");
    });

    function saveEmployee(data, url, buttonSelector) {
        $.easyAjax({
            url: url,
            container: '#save-employee-data-form',
            type: "POST",
            disableButton: true,
            blockUI: true,
            buttonSelector: buttonSelector,
            file: true,
            data: data,
            success: function (response) {
                if (response.status == 'success') {
                    if ($(MODAL_XL).hasClass('show')) {
                        $(MODAL_XL).modal('hide');
                        window.location.reload();
                    } else if (response.add_more == true) {
                        var right_modal_content = $.trim($(RIGHT_MODAL_CONTENT).html());
                        if (right_modal_content.length) {
                            $(RIGHT_MODAL_CONTENT).html(response.html.html);
                            $('#add_more').val(false);
                        } else {
                            $('.content-wrapper').html(response.html.html);
                            init('.content-wrapper');
                            $('#add_more').val(false);
                        }
                    } else {
                        window.location.href = response.redirectUrl;
                    }
                    if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                        showTable();
                    }
                }
            }
        });
    }
});
</script>
