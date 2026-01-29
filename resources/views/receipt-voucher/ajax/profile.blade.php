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

$showFullProfile = true;

// if ($viewPermission == 'all'
//     || ($viewPermission == 'added' && $driver->employeeDetail->added_by == user()->id)
//     || ($viewPermission == 'owned' && $driver->employeeDetail->user_id == user()->id)
//     || ($viewPermission == 'both' && ($driver->employeeDetail->user_id == user()->id || $driver->employeeDetail->added_by == user()->id))
// ) {
//     $showFullProfile = true;
// }

@endphp

@php
$editEmployeePermission = true; // user()->permission('edit_employees');
$viewAppreciationPermission = true; // user()->permission('view_appreciation');
@endphp

<div class="d-lg-flex">

    <div class="w-100 py-0 py-lg-3 py-md-0">
        <!-- ROW START -->
        <div class="row">
            <!--  USER CARDS START -->
            <div class="col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0">
                <div class="row">
                    <div class="col-xl-7 col-md-6 mb-4 mb-lg-0">

                        <x-cards.user :image="$driver->image_url">
                            <div class="row">
                                <div class="col-10">
                                    <h4 class="card-title f-15 f-w-500 text-darkest-grey mb-0">
                                        {{ $driver->name }}
                                        @isset($driver->nationality)
                                            <x-flag :country="$driver->nationality" />
                                        @endisset
                                    </h4>
                                </div>
                                {{-- @if ($editEmployeePermission == 'all' || ($editEmployeePermission == 'added' && $driver->employeeDetail->added_by == user()->id)) --}}
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
                                                    href="{{ route('drivers.edit', $driver->id) }}">@lang('app.edit')</a>
                                            </div>
                                        </div>
                                    </div>
                                {{-- @endif --}}

                            </div>

                            <p class="f-12 font-weight-normal text-dark-grey mb-0">
                                {{-- {{ !is_null($driver->employeeDetail) && !is_null($driver->employeeDetail->designation) ? $driver->employeeDetail->designation->name : '' }}
                                &bull;
                                {{ isset($driver->employeeDetail) && !is_null($driver->employeeDetail->department) && !is_null($driver->employeeDetail->department) ? $driver->employeeDetail->department->team_name : '' }} --}}
                            </p>

                            {{-- @if ($driver->status == 'active')
                                <p class="card-text f-11 text-lightest">@lang('app.lastLogin')

                                    @if (!is_null($driver->last_login))
                                        {{ $driver->last_login->timezone(company()->timezone)->translatedFormat(company()->date_format . ' ' . company()->time_format) }}
                                    @else
                                        --
                                    @endif
                                </p>

                            @else
                                <p class="card-text f-12 text-lightest">
                                    <x-status :value="__('app.inactive')" color="red" />
                                </p>
                            @endif --}}

                            {{-- @if ($showFullProfile)
                                <div class="card-footer bg-white border-top-grey pl-0">
                                    <div class="d-flex flex-wrap justify-content-between">
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 text-capitalize"
                                                for="usr">@lang('app.openTasks')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $driver->open_tasks_count }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 text-capitalize"
                                                for="usr">@lang('app.menu.projects')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $driver->member_count }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 text-capitalize"
                                                for="usr">@lang('modules.employees.hoursLogged')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $hoursLogged }}</p>
                                        </span>
                                        <span>
                                            <label class="f-11 text-dark-grey mb-12 text-capitalize"
                                                for="usr">@lang('app.menu.tickets')</label>
                                            <p class="mb-0 f-18 f-w-500">{{ $driver->agents_count }}</p>
                                        </span>
                                    </div>
                                </div>
                            @endif --}}
                        </x-cards.user>
{{-- 
                        @if ($driver->employeeDetail->about_me != '')
                            <x-cards.data :title="__('app.about')" class="mt-4">
                                <div>{{ $driver->employeeDetail->about_me }}</div>
                            </x-cards.data>
                        @endif --}}


                        <x-cards.data :title="__('modules.client.profileInfo')" class=" mt-4">
                            {{-- <x-cards.data-row :label="__('modules.employees.employeeId')"
                                :value="(!is_null($driver->employeeDetail) && !is_null($driver->employeeDetail->employee_id)) ? ($driver->employeeDetail->employee_id) : '--'" />

                            <x-cards.data-row :label="__('modules.employees.fullName')"
                                :value="$driver->name" />

                            <x-cards.data-row :label="__('app.designation')"
                                :value="(!is_null($driver->employeeDetail) && !is_null($driver->employeeDetail->designation)) ? ($driver->employeeDetail->designation->name) : '--'" />

                            <x-cards.data-row :label="__('app.department')"
                                :value="(isset($driver->employeeDetail) && !is_null($driver->employeeDetail->department) && !is_null($driver->employeeDetail->department)) ? ($driver->employeeDetail->department->team_name) : '--'" />

                            <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                                <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                                    @lang('modules.employees.gender')</p>
                                <p class="mb-0 text-dark-grey f-14 w-70">
                                    <x-gender :gender='$driver->gender' />
                                </p>
                            </div> --}}


                            {{-- @php
                                $currentyearJoiningDate = \Carbon\Carbon::parse(now(company()->timezone)->year.'-'.$driver->employeeDetail->joining_date->translatedFormat('m-d'));
                                if ($currentyearJoiningDate->copy()->endOfDay()->isPast()) {
                                    $currentyearJoiningDate = $currentyearJoiningDate->addYear();
                                }
                                $diffInHoursJoiningDate = now(company()->timezone)->floatDiffInHours($currentyearJoiningDate, false);
                            @endphp

                            <x-cards.data-row :label="__('modules.employees.workAnniversary')" :value="(!is_null($driver->employeeDetail) && !is_null($driver->employeeDetail->joining_date)) ? (($diffInHoursJoiningDate > -23 && $diffInHoursJoiningDate <= 0) ? __('app.today') : $currentyearJoiningDate->longRelativeToNowDiffForHumans()) : '--'" /> --}}

                            <x-cards.data-row :label="__('modules.employees.dateOfBirth')"
                                              :value="(!is_null($driver->date_of_birth) && !is_null($driver->date_of_birth)) ? $driver->date_of_birth->translatedFormat('d F') : '--'" />

                            @if ($showFullProfile)
                                <x-cards.data-row :label="__('app.email')" :value="$driver->email" />

                                <x-cards.data-row :label="__('app.mobile')"
                                :value="$driver->work_mobile_with_phonecode" />

                                {{-- <x-cards.data-row :label="__('modules.employees.slackUsername')"
                                    :value="(isset($driver->employeeDetail) && !is_null($driver->employeeDetail->slack_username)) ? '@'.$driver->employeeDetail->slack_username : '--'" /> --}}

                                <x-cards.data-row :label="__('modules.drivers.iqamaNumber')"
                                    :value="$driver->iqaama_number ?? '--'" />

                                <x-cards.data-row :label="__('modules.drivers.absherNumber')"
                                    :value="$driver->absher_number ?? '--'" />

                                <x-cards.data-row :label="__('modules.drivers.sponsorship')"
                                    :value="$driver->sponsorship ?? '--'" />

                                <x-cards.data-row :label="__('modules.drivers.sponsorshipID')"
                                    :value="$driver->sponsorship_id ?? '--'" />

                                <x-cards.data-row :label="__('modules.drivers.insurancePolicyNumber')"
                                    :value="$driver->insurance_policy_number ?? '--'" />

                                <x-cards.data-row :label="__('modules.drivers.joiningDate')"
                                    :value="$driver->created_at->translatedFormat(company()->date_format) />


                                {{-- Custom fields data --}}
                                <x-forms.custom-field-show :fields="$fields" :model="$driver"></x-forms.custom-field-show>

                            @endif

                        </x-cards.data>


                    </div>

                    <div class="col-xl-5 col-lg-6 col-md-6">

                        {{-- @if ($showFullProfile)
                             <x-cards.data class="mb-4" :title="__('modules.appreciations.appreciation')">
                                @forelse ($driver->appreciationsGrouped as $item)
                                <div class="float-left position-relative mb-2" style="width: 50px" data-toggle="tooltip" data-original-title="@if(isset($item->award->title)){{  $item->award->title }} @endif">
                                    @if(isset($item->award->awardIcon->icon))
                                        <x-award-icon :award="$item->award" />
                                    @endif
                                    <span class="position-absolute badge badge-secondary rounded-circle border-additional-grey appreciation-count">{{ $item->no_of_awards }}</span>
                                </div>
                                @empty
                                    <x-cards.no-record icon="medal" :message="__('messages.noRecordFound')" />
                                @endforelse
                            </x-cards.data>
                        @endif --}}

                        @if ($showFullProfile)
                            {{-- <div class="row">
                                @if (in_array('attendance', user_modules()))
                                    <div class="col-xl-6 col-sm-12 mb-4">
                                        <x-cards.widget :title="__('modules.dashboard.lateAttendanceMark')"
                                            :value="$lateAttendance" :info="__('modules.dashboard.thisMonth')"
                                            icon="map-marker-alt" />
                                    </div>
                                @endif
                                @if (in_array('leaves', user_modules()))
                                    <div class="col-xl-6 col-sm-12 mb-4">
                                        <x-cards.widget :title="__('modules.dashboard.leavesTaken')" :value="$leavesTaken"
                                            :info="__('modules.dashboard.thisMonth')" icon="sign-out-alt" />
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                @if (in_array('tasks', user_modules()))
                                    <div class="col-md-12 mb-4">
                                        <x-cards.data :title="__('app.menu.tasks')" padding="false">
                                            <x-pie-chart id="task-chart" :labels="$taskChart['labels']"
                                                :values="$taskChart['values']" :colors="$taskChart['colors']" height="250"
                                                width="300" />
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
                            </div> --}}
                        @endif

                    </div>
                </div>
            </div>
            <!--  USER CARDS END -->

        </div>
        <!-- ROW END -->
    </div>
</div>
