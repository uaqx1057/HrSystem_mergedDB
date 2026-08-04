@php
    $addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

{{-- ── MULTISTEP PROGRESS BAR ── --}}
<style>
    .ms-steps-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        padding: 24px 20px 20px;
        /* background: #fff;  */
        border-bottom: 1px solid #4b4e69;
        flex-wrap: wrap;
    }
    .ms-step {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: default;
    }
    .ms-step-circle {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 2px solid #dee2e6;
        /* background: #fff; */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        font-weight: 600;
        color: #adb5bd;
        transition: all .25s;
        flex-shrink: 0;
    }
    .ms-step-label {
        font-size: 12px;
        color: #adb5bd;
        font-weight: 500;
        white-space: nowrap;
        transition: color .25s;
    }
    .ms-step.active .ms-step-circle {
        border-color: #ffffff;
        background: @if(!user()->dark_theme)
            radial-gradient(circle at 14% 18%, rgba(217, 119, 6, 0.16), transparent 22%),
            radial-gradient(circle at 84% 20%, rgba(5, 150, 105, 0.26), transparent 24%),
            radial-gradient(circle at 70% 82%, rgba(6, 182, 212, 0.18), transparent 22%),
            linear-gradient(135deg, #010e09 0%, #021810 38%, #031f14 100%);
        @else
            #ffffff;
        @endif
        color: @if(!user()->dark_theme)
            #ffffff;
        @else
            #181c34;
        @endif
    }
    .ms-step.active .ms-step-label {
        color: @if(!user()->dark_theme)
            #021810;
        @else
            #ffffff;
        @endif
    }
    .ms-step.done .ms-step-circle {
        border-color: #021810;
        background: @if(!user()->dark_theme)
            radial-gradient(circle at 14% 18%, rgba(217, 119, 6, 0.16), transparent 22%),
            radial-gradient(circle at 84% 20%, rgba(5, 150, 105, 0.26), transparent 24%),
            radial-gradient(circle at 70% 82%, rgba(6, 182, 212, 0.18), transparent 22%),
            linear-gradient(135deg, #010e09 0%, #021810 38%, #031f14 100%);
        @else
            #ffffff;
        @endif
        color: @if(!user()->dark_theme)
            #ffffff;
        @else
            #181c34;
        @endif
    }
    .ms-step.done .ms-step-label {
        color: @if(!user()->dark_theme)
            #021810;
        @else
            #ffffff;
        @endif
    }
    .ms-step-line {
        height: 2px;
        width: 40px;
        background: #dee2e6;
        flex-shrink: 0;
        transition: background .25s;
    }
    .ms-step-line.done {
        background: @if(!user()->dark_theme)
            radial-gradient(circle at 14% 18%, rgba(217, 119, 6, 0.16), transparent 22%),
            radial-gradient(circle at 84% 20%, rgba(5, 150, 105, 0.26), transparent 24%),
            radial-gradient(circle at 70% 82%, rgba(6, 182, 212, 0.18), transparent 22%),
            linear-gradient(135deg, #010e09 0%, #021810 38%, #031f14 100%);
        @else
            #ffffff;
        @endif
    }

    .form-step { display: none; }
    .form-step.active { display: block; }

    .ms-nav-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 20px;
        /* border-top: 1px solid #4b4e69; */
        /* background: #fff; */
    }
    .ms-nav-buttons .left-btns { display: flex; gap: 8px; }
    .ms-nav-buttons .right-btns { display: flex; gap: 8px; }
    .datepicker-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236c757d'%3E%3Cpath d='M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM5 7V6h14v1H5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 18px 18px;
        padding-right: 34px !important;
        cursor: pointer !important;
        caret-color: transparent; /* hides the text cursor since typing is blocked anyway */
    }

    .datepicker-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%236c757d'%3E%3Cpath d='M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM5 7V6h14v1H5z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 10px center;
        background-size: 18px 18px;
        padding-right: 34px !important;
        cursor: pointer !important;
        caret-color: transparent;
    }

    /* override Bootstrap's greyed-out readonly look */
    .datepicker-input,
    .datepicker-input:read-only,
    .datepicker-input[readonly] {
        background-color: #fff !important;
        opacity: 1 !important;
        color: #212529 !important;
    }
    @if(user()->dark_theme)
    .datepicker-input,
    .datepicker-input:read-only,
    .datepicker-input[readonly] {
        background-color: #1e2136 !important; /* replace with your actual dark input bg */
        color: #ffffff !important;
        border-color: #4b4e69 !important;
    }
    .datepicker-input {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23adb5bd'%3E%3Cpath d='M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11zM5 7V6h14v1H5z'/%3E%3C/svg%3E");
    }
    @endif

    .step5-card {
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #ffffff;
        padding: 16px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.05);
    }

    .step5-subtitle {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 12px;
    }

    .allowance-row,
    .bank-account-row {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #f8fafc;
        padding-top: 10px;
        padding-bottom: 10px;
    }

    .step5-action-btn {
        border-radius: 8px;
        padding: 6px 12px;
        font-weight: 600;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-employee-data-form">
            @if(request('candidate_id'))
                <input type="hidden" name="candidate_id" value="{{ request('candidate_id') }}">
            @endif
            <div class="add-client bg-white rounded">

                {{-- ── STEP INDICATORS ── --}}
                <div class="ms-steps-wrapper" id="ms-steps-wrapper">
                    <div class="ms-step active" data-step="1">
                        <div class="ms-step-circle">1</div>
                        <span class="ms-step-label">Account Details</span>
                    </div>
                    <div class="ms-step-line" id="line-1"></div>
                    <div class="ms-step" data-step="2">
                        <div class="ms-step-circle">2</div>
                        <span class="ms-step-label">Documents</span>
                    </div>
                    <div class="ms-step-line" id="line-2"></div>
                    <div class="ms-step" data-step="3">
                        <div class="ms-step-circle">3</div>
                        <span class="ms-step-label">Personal & Contact</span>
                    </div>
                    <div class="ms-step-line" id="line-3"></div>
                    <div class="ms-step" data-step="4">
                        <div class="ms-step-circle">4</div>
                        <span class="ms-step-label">Other Details</span>
                    </div>
                    <div class="ms-step-line" id="line-4"></div>
                    <div class="ms-step" data-step="5">
                        <div class="ms-step-circle">5</div>
                        <span class="ms-step-label">Allowances</span>
                    </div>
                </div>

                {{-- ══════════════════════════════════════
                     STEP 1 — Account Details
                ══════════════════════════════════════ --}}
                <div class="form-step active" id="form-step-1">
                    <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey form-heading-background">
                        @lang('modules.employees.accountDetails')</h4>
                    <div class="row p-20">
                        <div class="col-lg-9">
                            <div class="row">
                                <div class="col-lg-4 col-md-6">
                                    <x-forms.text fieldId="employee_id" :fieldLabel="__('modules.employees.employeeId')" fieldName="employee_id"
                                        :fieldValue="!$checkifExistEmployeeId ? $lastEmployeeID + 1 : ''" fieldRequired="true" :fieldPlaceholder="__('modules.employees.employeeIdInfo')" :popover="__('modules.employees.employeeIdHelp')">
                                    </x-forms.text>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <x-forms.select fieldId="salutation" fieldName="salutation" :fieldLabel="__('modules.client.salutation')">
                                        <option value="">--</option>
                                        @foreach ($salutations as $salutation)
                                            <option value="{{ $salutation->value }}">{{ $salutation->label() }}</option>
                                        @endforeach
                                    </x-forms.select>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <x-forms.text fieldId="name" :fieldLabel="__('modules.employees.employeeName')" fieldName="name" fieldRequired="true" :fieldValue="$candidate?->name ?? ''"
                                        :fieldPlaceholder="__('placeholders.name')">
                                    </x-forms.text>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <x-forms.text fieldId="email" :fieldLabel="__('modules.employees.employeeEmail')" fieldName="email" fieldRequired="true" :fieldValue="$candidate?->email ?? ''"
                                        :fieldPlaceholder="__('placeholders.email')">
                                    </x-forms.text>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <x-forms.datepicker fieldId="date_of_birth" :fieldLabel="__('modules.employees.dateOfBirth')" fieldName="date_of_birth"
                                        :fieldPlaceholder="__('placeholders.date')" />
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <x-forms.label class="" fieldId="category_id" :fieldLabel="__('app.designation')"
                                        fieldRequired="true">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="designation"
                                            id="employee_designation" data-live-search="true">
                                            <option value="">--</option>
                                            @foreach ($designations as $designation)
                                                <option value="{{ $designation->id }}" @selected(strcasecmp($candidate?->designation ?? '', $designation->name) === 0)>{{ $designation->name }}</option>
                                            @endforeach
                                        </select>
                                    </x-forms.input-group>
                                </div>
                                <div class="col-lg-4 col-md-6">
                                    <x-forms.label class="" fieldId="category_id" :fieldLabel="__('app.department')"
                                        fieldRequired="true">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="department"
                                            id="employee_department" data-live-search="true">
                                            <option value="">--</option>
                                            @foreach ($teams as $team)
                                                <option value="{{ $team->id }}">{{ $team->team_name }}</option>
                                            @endforeach
                                        </select>
                                    </x-forms.input-group>
                                </div>
                                @if (in_array(user()->permission('add_employees'), ['all', 'added']))
                                <div class="col-lg-4 col-md-6">
                                    <x-forms.label class="" fieldId="branch_id" :fieldLabel="__('app.branchName')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="form-control select-picker" name="branch_id"
                                            id="employee_branch" data-live-search="true">
                                            <option value="">--</option>
                                            @foreach ($branches as $branch)
                                                <option value="{{ $branch->id }}" @selected(($candidate?->branch_id) == $branch->id)>{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                    </x-forms.input-group>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                :fieldLabel="__('modules.profile.profilePicture')" fieldName="image" fieldId="image" fieldHeight="119" :popover="__('messages.fileFormat.ImageFile')" />
                        </div>
                    </div>

                    {{-- Step 1 nav --}}
                    <div class="ms-nav-buttons">
                        <div class="left-btns"></div>
                        <div class="right-btns">
                            <button type="button" class="btn btn-primary ms-next-btn" data-next="2">
                                @lang('app.next') &nbsp;<i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════
                     STEP 2 — Documents
                ══════════════════════════════════════ --}}
                <div class="form-step" id="form-step-2">
                    <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey form-heading-background">
                        @lang('modules.employees.documentDetails')</h4>
                    <div class="row p-20">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-lg-3 col-md-6 ">
                                    <x-forms.select fieldId="employee_type" :fieldLabel="__('modules.employees.employeeType')" fieldName="employee_type" fieldRequired="true">
                                        <option value="expat">@lang('modules.employees.expat')</option>
                                        <option value="saudi">@lang('modules.employees.saudi')</option>
                                    </x-forms.select>
                                </div>

                                {{-- EXPAT ONLY: Iqama --}}
                                <div class="col-lg-3 col-md-6  expat-only-field">
                                    <x-forms.text fieldId="iqama_no" :fieldLabel="__('modules.employees.Iqama No')" fieldName="iqama_no" fieldRequired="true"
                                        :fieldPlaceholder="__('placeholders.iqama')">
                                    </x-forms.text>
                                </div>
                                <div class="col-lg-3 col-md-6  expat-only-field">
                                    <x-forms.text fieldId="iqama_profession" :fieldLabel="__('modules.employees.iqama_profession')" fieldName="iqama_profession"
                                        fieldRequired="true" :fieldPlaceholder="__('placeholders.iqama_profession')">
                                    </x-forms.text>
                                </div>
                                <div class="col-lg-3 col-md-6  expat-only-field">
                                    <x-forms.datepicker fieldId="iqama_expiry_date" fieldRequired="true" :fieldLabel="__('modules.employees.iqama_expiry_date')"
                                        fieldName="iqama_expiry_date" :fieldPlaceholder="__('placeholders.iqama_expiry_date')" minlength="10" maxlength="10" />
                                </div>

                                {{-- SAUDI ONLY: National ID --}}
                                <div class="col-lg-3 col-md-6  saudi-only-field d-none">
                                    <x-forms.text fieldId="national_id" :fieldLabel="__('modules.employees.national_id')" fieldName="national_id" fieldRequired="true"
                                        :fieldPlaceholder="__('placeholders.national_id')">
                                    </x-forms.text>
                                </div>
                                <div class="col-lg-3 col-md-6  saudi-only-field d-none">
                                    <x-forms.datepicker fieldId="national_id_expiry_date" :fieldLabel="__('modules.employees.national_id_expiry_date')"
                                        fieldName="national_id_expiry_date" :fieldPlaceholder="__('placeholders.national_id_expiry_date')" minlength="10" maxlength="10" fieldRequired="true" />
                                </div>

                                {{-- PASSPORT: both types, required for expat only --}}
                                <div class="col-lg-3 col-md-6 ">
                                    <x-forms.text fieldId="passport_no" :fieldLabel="__('modules.employees.passport_no')" fieldName="passport_no" fieldRequired="true"
                                        :fieldPlaceholder="__('placeholders.passport_no')">
                                    </x-forms.text>
                                </div>
                                <div class="col-lg-3 col-md-6 ">
                                    <x-forms.datepicker fieldId="passport_expiry_date" :fieldLabel="__('modules.employees.passport_expiry_date')" fieldName="passport_expiry_date"
                                        :fieldPlaceholder="__('placeholders.passport_expiry_date')" />
                                </div>

                                <div class="col-lg-3 col-md-6 ">
                                    <x-forms.select fieldId="sponsor_kafala" :fieldLabel="__('modules.employees.Sponsor / kafala')" fieldName="sponsor_kafala" search="true">
                                        <option value="">--</option>
                                        @foreach ($companies as $company)
                                            <option value="{{ $company->id }}">{{ $company->company_name }}</option>
                                        @endforeach
                                    </x-forms.select>
                                </div>

                                {{-- NEW: Probation Time --}}
                                <div class="col-lg-3 col-md-6 ">
                                    <x-forms.text fieldId="probation_time" :fieldLabel="__('modules.employees.probation_time')" fieldName="probation_time" :fieldPlaceholder="__('placeholders.probation_time')">
                                    </x-forms.text>
                                </div>

                            </div>
                        </div>
                        <div class="col-lg-12">

                            <div class="row">
                                <div class="col-lg-3 col-md-6    expat-only-field">
                                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                        :fieldLabel="__('modules.employees.iqama_image')" fieldName="iqama_image" fieldId="iqama_image"
                                        fieldRequired="true" />
                                </div>

                                <div class="col-lg-3 col-md-6   saudi-only-field d-none">
                                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                        :fieldLabel="__('modules.employees.national_id_image')" fieldName="national_id_image" fieldId="national_id_image"  />
                                </div>

                                <div class="col-lg-3 col-md-6   ">
                                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                        :fieldLabel="__('modules.employees.passport_image')" fieldName="passport_image" fieldId="passport_image"  />
                                </div>

                                {{-- EXPAT ONLY: Qiwa Contract File --}}
                                <div class="col-lg-3 col-md-6   expat-only-field">
                                    <x-forms.file allowedFileExtensions="pdf png jpg jpeg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                        :fieldLabel="__('modules.employees.qiva_contract')" fieldName="qiva_contract" fieldId="qiva_contract" />
                                </div>

                                {{-- NEW: Company Contract File --}}
                                <div class="col-lg-3 col-md-6  ">
                                    <x-forms.file allowedFileExtensions="pdf png jpg jpeg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                        :fieldLabel="__('modules.employees.company_contract')" fieldName="company_contract" fieldId="company_contract" />
                                </div>

                            </div>


                        </div>
                    </div>

                    {{-- Step 2 nav --}}
                    <div class="ms-nav-buttons">
                        <div class="left-btns">
                            <button type="button" class="btn btn-secondary ms-prev-btn" data-prev="1">
                                <i class="fa fa-arrow-left"></i> &nbsp;@lang('app.previous')
                            </button>
                        </div>
                        <div class="right-btns">
                            <button type="button" class="btn btn-primary ms-next-btn" data-next="3">
                                @lang('app.next') &nbsp;<i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════
                     STEP 3 — Personal & Contact
                ══════════════════════════════════════ --}}
                <div class="form-step" id="form-step-3">
                    <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey form-heading-background">
                        @lang('modules.employees.personalContactDetails')</h4>
                    <div class="row p-20">
                        @php
                            $showButton = true;
                        @endphp
                        <div class="col-lg-3">
                            <x-forms.text fieldId="password" :fieldLabel="__('modules.employees.password')" fieldName="password" fieldRequired="true"
                                :fieldPlaceholder="__('placeholders.password')" :fieldValue="old('password')" :showButton="$showButton">
                            </x-forms.text>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country" search="true">
                                @foreach ($countries as $item)
                                    <option data-tokens="{{ $item->iso3 }}" data-phonecode="{{ $item->phonecode }}"
                                        data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nicename }}"
                                        @selected(($candidate?->country_id ?? null) ? ((int) $candidate->country_id === (int) $item->id) : (strtoupper($item->iso) === 'SA'))
                                        value="{{ $item->id }}">{{ $item->nicename }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <x-forms.label class="" fieldId="mobile" :fieldLabel="__('app.mobile')"></x-forms.label>
                            <x-forms.input-group style="margin-top:-4px">
                                <x-forms.select fieldId="country_phonecode" fieldName="country_phonecode" search="true">
                                    @foreach ($countries as $item)
                                        <option data-tokens="{{ $item->name }}"
                                            data-content="{{ $item->flagSpanCountryCode() }}"
                                            @selected(($candidate?->country_phonecode ?? null) ? ((string) $candidate->country_phonecode === (string) $item->phonecode) : (strtoupper($item->iso) === 'SA'))
                                            value="{{ $item->phonecode }}">{{ $item->phonecode }}
                                        </option>
                                    @endforeach
                                </x-forms.select>
                                <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                    name="mobile" id="mobile" value="{{ $candidate?->mobile ?? '' }}">
                            </x-forms.input-group>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="gender" :fieldLabel="__('modules.employees.gender')" fieldName="gender">
                                <option value="male">@lang('app.male')</option>
                                <option value="female">@lang('app.female')</option>
                                <option value="others">@lang('app.others')</option>
                            </x-forms.select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <x-forms.datepicker fieldId="joining_date" :fieldLabel="__('modules.employees.joiningDate')" fieldName="joining_date"
                                :fieldPlaceholder="__('placeholders.date')" fieldRequired="true" :fieldValue="now(company()->timezone)->format(company()->date_format)" />
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <x-forms.text fieldId="basic_salary" :fieldLabel="__('modules.employees.basic_salary')" fieldName="basic_salary"
                                fieldRequired="false" :fieldPlaceholder="__('placeholders.basic_salary')">
                            </x-forms.text>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="reporting_to" :fieldLabel="__('modules.employees.reportingTo')" fieldName="reporting_to"
                                :fieldPlaceholder="__('placeholders.date')" search="true">
                                <option value="">--</option>
                                @foreach ($employees as $item)
                                    <x-user-option :user="$item" />
                                @endforeach
                            </x-forms.select>
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="role" :fieldLabel="__('app.role')" fieldName="role">
                                @foreach ($roles as $role)
                                    <option {{ $role->name == 'employee' ? 'selected' : '' }}
                                        value="{{ $role->id }}">{{ $role->display_name }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('modules.employees.vehicle_allocation')</label>
                                <div class="d-flex">
                                    <x-forms.radio fieldId="vehicle_allocation_yes" :fieldLabel="__('app.yes')" fieldValue="yes"
                                        fieldName="vehicle_allocation" checked="true">
                                    </x-forms.radio>
                                    <x-forms.radio fieldId="vehicle_allocation_no" :fieldLabel="__('app.no')" fieldValue="no"
                                        fieldName="vehicle_allocation">
                                    </x-forms.radio>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 vehicle-div">
                            <x-forms.select fieldId="vehicle_id" :fieldLabel="__('modules.employees.vehicle')" fieldName="vehicle_id" search="true">
                                <option value="">--</option>
                                @foreach ($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}">{{ $vehicle->registration_number }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group my-3">
                                <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.address')" fieldName="address"
                                    fieldId="address" fieldPlaceholder="Saudia Arabia" fieldValue="Saudi Arabia">
                                </x-forms.textarea>
                            </div>
                        </div>

                    </div>

                    {{-- Step 3 nav --}}
                    <div class="ms-nav-buttons">
                        <div class="left-btns">
                            <button type="button" class="btn btn-secondary ms-prev-btn" data-prev="2">
                                <i class="fa fa-arrow-left"></i> &nbsp;@lang('app.previous')
                            </button>
                        </div>
                        <div class="right-btns">
                            <button type="button" class="btn btn-primary ms-next-btn" data-next="4">
                                @lang('app.next') &nbsp;<i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════
                     STEP 4 — Other Details
                ══════════════════════════════════════ --}}
                <div class="form-step" id="form-step-4">
                    <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey form-heading-background">
                        @lang('modules.client.clientOtherDetails')</h4>
                    <div class="row p-20">
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group">
                                <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('modules.client.clientCanLogin')</label>
                                <div class="d-flex">
                                    <x-forms.radio fieldId="login-yes" :fieldLabel="__('app.yes')" fieldName="login"
                                        fieldValue="enable" checked="true">
                                    </x-forms.radio>
                                    <x-forms.radio fieldId="login-no" :fieldLabel="__('app.no')" fieldValue="disable"
                                        fieldName="login"></x-forms.radio>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="form-group ">
                                <label class="f-14 text-dark-grey mb-12 w-100" for="usr">@lang('modules.emailSettings.emailNotifications')</label>
                                <div class="d-flex">
                                    <x-forms.radio fieldId="notification-yes" :fieldLabel="__('app.yes')" fieldValue="yes"
                                        fieldName="email_notifications" checked="true">
                                    </x-forms.radio>
                                    <x-forms.radio fieldId="notification-no" :fieldLabel="__('app.no')" fieldValue="no"
                                        fieldName="email_notifications">
                                    </x-forms.radio>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <x-forms.label class="" fieldId="slack_username" :fieldLabel="__('modules.employees.linkedinUsername')"></x-forms.label>
                            <x-forms.input-group>
                                <input type="text" class="form-control height-35 f-14" name="slack_username"
                                    id="slack_username">
                            </x-forms.input-group>
                        </div>

                        @if (function_exists('sms_setting') && sms_setting()->telegram_status)
                            <div class="col-md-6">
                                <x-forms.number fieldName="telegram_user_id" fieldId="telegram_user_id"
                                    fieldLabel="<i class='fab fa-telegram'></i> {{ __('sms::modules.telegramUserId') }}"
                                    :popover="__('sms::modules.userIdInfo')" />
                                <p class="text-bold text-danger">
                                    @lang('sms::modules.telegramBotNameInfo')
                                </p>
                                <p class="text-bold"><span
                                        id="telegram-link-text">https://t.me/{{ sms_setting()->telegram_bot_name }}</span>
                                    <a href="javascript:;" class="btn-copy btn-secondary f-12 rounded p-1 py-2 ml-1"
                                        data-clipboard-target="#telegram-link-text">
                                        <i class="fa fa-copy mx-1"></i>@lang('app.copy')</a>
                                    <a href="https://t.me/{{ sms_setting()->telegram_bot_name }}" target="_blank"
                                        class="btn-secondary f-12 rounded p-1 py-2 ml-1">
                                        <i class="fa fa-copy mx-1"></i>@lang('app.openInNewTab')</a>
                                </p>
                            </div>
                        @endif

                        <div class="col-lg-3 col-md-6">
                            <x-forms.datepicker fieldId="probation_end_date" :fieldLabel="__('modules.employees.probationEndDate')"
                                fieldName="probation_end_date" :fieldPlaceholder="__('placeholders.date')" :popover="__('messages.probationEndDate')" />
                        </div>

                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="employment_type" :fieldLabel="__('modules.employees.employmentType')" fieldName="employment_type"
                                :fieldPlaceholder="__('placeholders.date')">
                                <option value="">--</option>
                                <option value="full_time">@lang('app.fullTime')</option>
                                <option value="part_time">@lang('app.partTime')</option>
                                <option value="on_contract">@lang('app.onContract')</option>
                                <option value="internship">@lang('app.internship')</option>
                                <option value="trainee">@lang('app.trainee')</option>
                            </x-forms.select>
                        </div>
                        <div class="col-lg-3 col-md-6 d-none internship-date">
                            <x-forms.datepicker fieldId="internship_end_date" :fieldLabel="__('modules.employees.internshipEndDate')"
                                fieldName="internship_end_date" :fieldPlaceholder="__('placeholders.date')" />
                        </div>
                        <div class="col-lg-3 col-md-6 d-none contract-date">
                            <x-forms.datepicker fieldId="contract_end_date" :fieldLabel="__('modules.employees.contractEndDate')"
                                fieldName="contract_end_date" :fieldPlaceholder="__('placeholders.date')" />
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="marital_status" :fieldLabel="__('modules.employees.maritalStatus')" fieldName="marital_status">
                                @foreach (\App\Enums\MaritalStatus::cases() as $status)
                                    <option value="{{ $status->value }}">{{ $status->label() }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>

                        {{-- Dependants (hidden until married) --}}
                        <div class="col-lg-3 col-md-6 d-none dependant">
                            <x-forms.text fieldId="no_of_dependants" :fieldLabel="__('modules.employees.no_of_dependants')" fieldName="no_of_dependants"
                                :fieldPlaceholder="__('placeholders.no_of_dependants')">
                            </x-forms.text>
                        </div>
                        <div class="col-md-12 d-none dependant-rows-wrapper">
                            <hr>
                            <h6 class="f-15 font-weight-bold mb-3">@lang('modules.employees.dependants')</h6>
                            <div id="dependant-rows" class="p-3"></div>
                            <button type="button" id="add-dependant-btn" class="btn btn-outline-primary btn-sm mt-2">
                                <i class="fa fa-plus mr-1"></i> @lang('modules.employees.addDependant')
                            </button>
                        </div>

                        <input type="hidden" name="add_more" value="false" id="add_more" />
                    </div>

                    <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                    {{-- Step 4 nav --}}
                    <div class="ms-nav-buttons">
                        <div class="left-btns">
                            <button type="button" class="btn btn-secondary ms-prev-btn" data-prev="3">
                                <i class="fa fa-arrow-left"></i> &nbsp;@lang('app.previous')
                            </button>
                        </div>
                        <div class="right-btns">
                            <button type="button" class="btn btn-primary ms-next-btn" data-next="5">
                                @lang('app.next') &nbsp;<i class="fa fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- ══════════════════════════════════════
                     STEP 5 — Allowances & Save
                ══════════════════════════════════════ --}}
                <div class="form-step" id="form-step-5">
                    <div class="col-md-12 allowances-rows-wrapper p-20">
                        <div class="step5-card">
                            <h4 class="mb-1 f-21 font-weight-normal text-capitalize">@lang('modules.employees.allowances')</h4>
                            <div class="step5-subtitle">Add recurring allowance rows for payroll.</div>
                            <div id="allowances-rows"></div>
                            <button type="button" id="add-allowances-btn" class="btn btn-outline-primary btn-sm my-2 step5-action-btn">
                                <i class="fa fa-plus mr-1"></i> @lang('modules.employees.addAllowance')
                            </button>
                        </div>
                    </div>
                    <div class="col-md-12 p-20 pt-0">
                        <div class="step5-card">
                            <h4 class="mb-1 f-21 font-weight-normal text-capitalize">@lang('app.menu.employeebankaccount')</h4>
                            <div class="step5-subtitle">Keep payroll disbursement accounts in one place.</div>
                            <div id="bank-accounts-rows"></div>
                            <button type="button" id="add-bank-account-btn" class="btn btn-outline-primary btn-sm my-2 step5-action-btn">
                                <i class="fa fa-plus mr-1"></i> @lang('app.add') @lang('app.menu.employeebankaccount')
                            </button>
                        </div>
                    </div>

                    {{-- Step 5 nav + Save buttons --}}
                    <div class="ms-nav-buttons">
                        <div class="left-btns">
                            <button type="button" class="btn btn-secondary ms-prev-btn" data-prev="4">
                                <i class="fa fa-arrow-left"></i> &nbsp;@lang('app.previous')
                            </button>
                        </div>
                        <div class="right-btns">
                            <x-forms.button-primary id="save-employee-form" class="mr-3" icon="check">
                                @lang('app.save')
                            </x-forms.button-primary>
                            <x-forms.button-secondary class="mr-3" id="save-more-employee-form"
                                icon="check-double">@lang('app.saveAddMore')
                            </x-forms.button-secondary>
                            <x-forms.button-cancel class="border-0" data-dismiss="modal">@lang('app.cancel')
                            </x-forms.button-cancel>
                        </div>
                    </div>
                </div>

            </div>{{-- /.add-client --}}
        </x-form>
    </div>
</div>

@if (function_exists('sms_setting') && sms_setting()->telegram_status)
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
@endif

<script>
    function generatePassword(fieldId) {
        var length = 8,
            charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
            password = "";
        for (var i = 0, n = charset.length; i < length; ++i) {
            password += charset.charAt(Math.floor(Math.random() * n));
        }
        document.getElementById(fieldId).value = password;
    }
</script>

<script>
    $(document).ready(function() {
        function lockDateField(id) {
            $('#' + id)
                .addClass('datepicker-input')
                .attr('placeholder', 'DD-MM-YYYY')
                .attr('readonly', true)
                .on('keydown paste', function (e) { e.preventDefault(); });
        }

        ['iqama_expiry_date', 'national_id_expiry_date', 'passport_expiry_date', 'joining_date',
        'probation_end_date', 'date_of_birth', 'internship_end_date', 'contract_end_date'
        ].forEach(lockDateField);
        // ── MULTISTEP NAVIGATION ──────────────────────────────
        var currentStep = 1;
        var totalSteps  = 5;

        function goToStep(step) {
            // hide all steps
            $('.form-step').removeClass('active');
            $('#form-step-' + step).addClass('active');

            // update indicators
            for (var s = 1; s <= totalSteps; s++) {
                var $step = $('[data-step="' + s + '"]');
                var $line = $('#line-' + s);
                $step.removeClass('active done');
                $line.removeClass('done');

                if (s < step) {
                    $step.addClass('done');
                    $line.addClass('done');
                } else if (s === step) {
                    $step.addClass('active');
                }
            }

            currentStep = step;

            // scroll to top of form
            $('html, body').animate({
                scrollTop: $('#ms-steps-wrapper').offset().top - 60
            }, 200);
        }

        function validateStep(step) {
            var ok = true;

            if (step === 1) {
                var name = $.trim($('#name').val());
                if (name === '') {
                    highlightError('#name', 'Employee name is required.', false);
                    ok = false;
                }

                if(ok == true){
                    var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,})?$/;
                    var email = $.trim($('#email').val());
                    if (email === '') {
                        highlightError('#email', 'Email is required.', false);
                        ok = false;
                    } else if (!emailReg.test(email)) {
                        highlightError('#email', 'Please enter a valid email.', false);
                        ok = false;
                    }
                }

                if(ok == true){
                    var designation = $.trim($('#employee_designation').val());
                    if (designation === '') {
                        highlightError('#employee_designation', 'Designation is required.', true);
                        ok = false;
                    }
                }

                if(ok == true){
                    var department = $.trim($('#employee_department').val());
                    if (department === '') {
                        highlightError('#employee_department', 'Department is required.', true);
                        ok = false;
                    }
                }
            }

            if (step === 2) {


            }

            if (step === 3) {
                var password = $.trim($('#password').val());
                if (password === '') {
                    highlightError('#password', 'Password is required.', false);
                    ok = false;
                }
            }

            return ok;
        }

        function highlightError(selector, msg, parent) {
            if(parent == true){
                $(selector).parent('.select-picker').addClass('is-invalid');
            } else{
                $(selector).addClass('is-invalid');
            }
            showAlert(msg);
            setTimeout(function () {
                if(parent == true){
                    $(selector).parent('.select-picker').removeClass('is-invalid');
                } else{
                    $(selector).removeClass('is-invalid');
                }
            }, 3000);
        }

        function showAlert(msg) {
            Swal.fire({
                icon: 'error', text: msg,
                toast: true, position: 'top-end', timer: 3000,
                timerProgressBar: true, showConfirmButton: false,
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
            });
        }

        // Next buttons
        $(document).on('click', '.ms-next-btn', function() {
            var next = parseInt($(this).data('next'));
            var current = next - 1;
            if (!validateStep(current)) return;
            goToStep(next);
        });

        // Previous buttons
        $(document).on('click', '.ms-prev-btn', function() {
            var prev = parseInt($(this).data('prev'));
            goToStep(prev);
        });
        // ─────────────────────────────────────────────────────

        // ── DATEPICKERS ───────────────────────────────────────
        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, { position: 'bl', ...datepickerConfig });
        });

        datepicker('#iqama_expiry_date',         { position: 'bl', ...datepickerConfig });
        datepicker('#national_id_expiry_date',    { position: 'bl', ...datepickerConfig });
        datepicker('#passport_expiry_date',       { position: 'bl', ...datepickerConfig });
        datepicker('#joining_date',               { position: 'bl', ...datepickerConfig });
        datepicker('#probation_end_date',         { position: 'bl', ...datepickerConfig });
        datepicker('#date_of_birth',              { position: 'bl', maxDate: new Date(), ...datepickerConfig });
        datepicker('#internship_end_date',        { position: 'bl', ...datepickerConfig });
        datepicker('#contract_end_date',          { position: 'bl', ...datepickerConfig });

        // ── EMPLOYEE TYPE (Saudi / Expat) ─────────────────────
        function toggleEmployeeTypeFields() {
            var isSaudi = $('#employee_type').val() === 'saudi';

            $('.expat-only-field').toggleClass('d-none', isSaudi);
            $('.expat-only-field').find('input, select, textarea').prop('disabled', isSaudi);

            $('.saudi-only-field').toggleClass('d-none', !isSaudi);
            $('.saudi-only-field').find('input, select, textarea').prop('disabled', !isSaudi);

            $('#iqama_no, #iqama_profession').prop('required', !isSaudi);
            $('#national_id').prop('required', isSaudi);

            // Passport is required for expats, optional for Saudis.
            $('#passport_no').prop('required', !isSaudi);
            $('label[for="passport_no"] sup').toggle(!isSaudi);
        }

        $('#employee_type').change(toggleEmployeeTypeFields);
        toggleEmployeeTypeFields();

        // ── MARITAL STATUS ────────────────────────────────────
        $('#marital_status').change(function() {
            var value = $(this).val();
            if (value == '{{ \App\Enums\MaritalStatus::Married->value }}') {
                $('.dependant').removeClass('d-none');
                $('.dependant-rows-wrapper').removeClass('d-none');
            } else {
                $('.dependant').addClass('d-none');
                $('.dependant-rows-wrapper').addClass('d-none');
                $('#dependant-rows').empty();
            }
        });

        // ── DEPENDANT ROWS LOGIC ──────────────────────────────
        var maxDependants  = 0;
        var addedDependants = 0;

        $('#no_of_dependants').on('input', function() {
            maxDependants   = parseInt($(this).val()) || 0;
            addedDependants = $('#dependant-rows .dependant-row').length;
            updateAddButton();
        });

        function updateAddButton() {
            if (addedDependants < maxDependants) {
                $('#add-dependant-btn').removeClass('d-none');
            } else {
                $('#add-dependant-btn').addClass('d-none');
            }
        }

        function validateDependants() {
            var maritalStatus = $('#marital_status').val();
            var isMarried = maritalStatus == '{{ \App\Enums\MaritalStatus::Married->value }}';
            if (!isMarried) return true;

            var rows = $('#dependant-rows .dependant-row');
            if (rows.length === 0) return true;

            var allValid = true;
            rows.each(function() {
                var nameInput = $(this).find('input[name$="[name]"]');
                if (nameInput.val().trim() === '') {
                    nameInput.addClass('is-invalid');
                    allValid = false;
                } else {
                    nameInput.removeClass('is-invalid');
                }
            });

            if (!allValid) {
                Swal.fire({
                    icon: 'error', text: 'All dependants name are required.',
                    toast: true, position: 'top-end', timer: 3000,
                    timerProgressBar: true, showConfirmButton: false,
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                });
            }
            return allValid;
        }

        function addDependantRow() {
            var idx = addedDependants;
            var row = `
                <div class="row dependant-row border rounded p-2 mb-2" data-index="${idx}">
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="f-14 text-dark-grey">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control height-35 f-14"
                               name="dependants[${idx}][name]" placeholder="Name" required>
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="f-14 text-dark-grey">Iqama No</label>
                        <input type="text" class="form-control height-35 f-14"
                               name="dependants[${idx}][iqama_no]" placeholder="Iqama No">
                    </div>
                    <div class="col-lg-3 col-md-6 mb-2">
                        <label class="f-14 text-dark-grey">Relation <span class="text-danger">*</span></label>
                        <input type="text" class="form-control height-35 f-14"
                               name="dependants[${idx}][relation]" placeholder="e.g. Spouse, Child" required>
                    </div>
                    <div class="col-lg-3 col-md-5 mb-2">
                        <label class="f-14 text-dark-grey">Date of Birth</label>
                        <input type="text" id="dep_dob_${idx}"
                               class="form-control height-35 f-14 dependant-dob"
                               name="dependants[${idx}][date_of_birth]" placeholder="DD-MM-YYYY" autocomplete="off">
                    </div>
                    <div class="col-lg-1 col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-dependant-btn">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>`;
            $('#dependant-rows').append(row);

            datepicker('#dep_dob_' + idx, {
                position: 'bl',
                maxDate: new Date(),
                ...datepickerConfig
            });

            addedDependants++;
            updateAddButton();
        }

        $('#add-dependant-btn').on('click', function() { addDependantRow(); });

        $(document).on('click', '.remove-dependant-btn', function() {
            $(this).closest('.dependant-row').remove();
            addedDependants = $('#dependant-rows .dependant-row').length;
            $('#dependant-rows .dependant-row').each(function(i) {
                $(this).find('[name]').each(function() {
                    var n = $(this).attr('name').replace(/\[\d+\]/, '[' + i + ']');
                    $(this).attr('name', n);
                });
            });
            updateAddButton();
        });

        // ── ALLOWANCE ROWS LOGIC ──────────────────────────────
        var allowanceIndex = 0;

        function addAllowanceRow() {
            var row = `
        <div class="row allowance-row p-2 mb-2" data-index="${allowanceIndex}">
            <div class="col-lg-4 col-md-6 mb-2">
                <label class="f-14 text-dark-grey">@lang('modules.employees.allowanceName') <span class="text-danger">*</span></label>
                <input type="text" class="form-control height-35 f-14"
                       name="allowances[${allowanceIndex}][name]" placeholder="@lang('placeholders.allowanceName')" required>
            </div>
            <div class="col-lg-4 col-md-6 mb-2">
                <label class="f-14 text-dark-grey">@lang('modules.employees.allowanceAmount') <span class="text-danger">*</span></label>
                <input type="number" class="form-control height-35 f-14"
                       name="allowances[${allowanceIndex}][amount]" placeholder="@lang('placeholders.allowanceAmount')" min="0" step="0.01" required>
            </div>
            <div class="col-lg-1 col-md-1 mb-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-allowance-btn">
                    <i class="fa fa-times"></i>
                </button>
            </div>
        </div>`;
            $('#allowances-rows').append(row);
            allowanceIndex++;
        }

        $('#add-allowances-btn').on('click', function() { addAllowanceRow(); });

        $(document).on('click', '.remove-allowance-btn', function() {
            $(this).closest('.allowance-row').remove();
            $('#allowances-rows .allowance-row').each(function(i) {
                $(this).find('[name]').each(function() {
                    var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + i + ']');
                    $(this).attr('name', newName);
                });
                allowanceIndex = i + 1;
            });
            if ($('#allowances-rows .allowance-row').length === 0) {
                allowanceIndex = 0;
            }
        });

        function validateAllowances() {
            var allValid = true;
            $('#allowances-rows .allowance-row').each(function() {
                var nameInput   = $(this).find('input[name$="[name]"]');
                var amountInput = $(this).find('input[name$="[amount]"]');
                if (nameInput.val().trim() === '') {
                    nameInput.addClass('is-invalid'); allValid = false;
                } else { nameInput.removeClass('is-invalid'); }
                if (amountInput.val().trim() === '' || parseFloat(amountInput.val()) < 0) {
                    amountInput.addClass('is-invalid'); allValid = false;
                } else { amountInput.removeClass('is-invalid'); }
            });
            if (!allValid) {
                Swal.fire({
                    icon: 'error', text: 'All allowance fields (Name and Amount) are required.',
                    toast: true, position: 'top-end', timer: 3000,
                    timerProgressBar: true, showConfirmButton: false,
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                });
            }
            return allValid;
        }

        // ── BANK ACCOUNT ROWS LOGIC ───────────────────────────
        var bankAccountIndex = 0;

        function addBankAccountRow() {
            var row = `
            <div class="row bank-account-row p-2 mb-2" data-index="${bankAccountIndex}">
                <div class="col-lg-10">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-2 px-1">
                            <label class="f-14 text-dark-grey">@lang('app.bankName') <span class="text-danger">*</span></label>
                            <input type="text" class="form-control height-35 f-14"
                                name="bank_accounts[${bankAccountIndex}][bank_name]"
                                placeholder="@lang('app.bankName')" required>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2 px-1">
                            <label class="f-14 text-dark-grey">@lang('app.ibanNumber') <span class="text-danger">*</span></label>
                            <input type="text" class="form-control height-35 f-14"
                                name="bank_accounts[${bankAccountIndex}][iban_number]"
                                placeholder="@lang('app.ibanNumber')" required>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2 px-1">
                            <label class="f-14 text-dark-grey">@lang('app.accountNumber')</label>
                            <input type="text" class="form-control height-35 f-14"
                                name="bank_accounts[${bankAccountIndex}][account_number]"
                                placeholder="@lang('app.accountNumber')">
                        </div>
                        <div class="col-lg-3 col-md-6 mb-2 px-1">
                            <label class="f-14 text-dark-grey">@lang('app.swiftCode')</label>
                            <input type="text" class="form-control height-35 f-14"
                                name="bank_accounts[${bankAccountIndex}][swift_code]"
                                placeholder="@lang('app.swiftCode')">
                        </div>
                    </div>
                </div>

                <div class="col-lg-2">
                    <div class="row mt-4">
                        <div class="col-lg-9 col-md-9 px-1 d-flex align-items-center">
                            <div class="form-check">
                                <input class="form-check-input main-bank-checkbox" type="checkbox" name="bank_accounts[${bankAccountIndex}][is_main_account]" value="1">
                                <label class="form-check-label f-12 pt-1 ml-2">@lang('app.mainAccount')</label>
                            </div>
                        </div>

                        <div class="col-lg-3 col-md-3 px-1 d-flex align-items-end">
                            <button type="button" class="btn btn-danger btn-sm remove-bank-account-btn">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>`;

            $('#bank-accounts-rows').append(row);
            bankAccountIndex++;
        }

        $('#add-bank-account-btn').on('click', function() { addBankAccountRow(); });

        $(document).on('change', '.main-bank-checkbox', function () {
            if ($(this).is(':checked')) {
                $('.main-bank-checkbox').not(this).prop('checked', false);
            }
        });

        $(document).on('click', '.remove-bank-account-btn', function() {
            $(this).closest('.bank-account-row').remove();
            $('#bank-accounts-rows .bank-account-row').each(function(i) {
                $(this).attr('data-index', i);
                $(this).find('[name]').each(function() {
                    var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + i + ']');
                    $(this).attr('name', newName);
                });
            });
            bankAccountIndex = $('#bank-accounts-rows .bank-account-row').length;
        });

        function validateBankAccounts() {
            var allValid = true;
            $('#bank-accounts-rows .bank-account-row').each(function() {
                var bankNameInput = $(this).find('input[name$="[bank_name]"]');
                var ibanInput = $(this).find('input[name$="[iban_number]"]');
                var accountNumberInput = $(this).find('input[name$="[account_number]"]');
                var swiftCodeInput = $(this).find('input[name$="[swift_code]"]');
                var hasAnyValue = bankNameInput.val().trim() !== '' || ibanInput.val().trim() !== '' || accountNumberInput.val().trim() !== '' || swiftCodeInput.val().trim() !== '';

                if (!hasAnyValue) {
                    bankNameInput.removeClass('is-invalid');
                    ibanInput.removeClass('is-invalid');
                    return;
                }

                if (bankNameInput.val().trim() === '') {
                    bankNameInput.addClass('is-invalid');
                    allValid = false;
                } else {
                    bankNameInput.removeClass('is-invalid');
                }

                if (ibanInput.val().trim() === '') {
                    ibanInput.addClass('is-invalid');
                    allValid = false;
                } else {
                    ibanInput.removeClass('is-invalid');
                }
            });

            if (!allValid) {
                Swal.fire({
                    icon: 'error', text: 'Bank Name and IBAN are required for each employee bank account row.',
                    toast: true, position: 'top-end', timer: 3000,
                    timerProgressBar: true, showConfirmButton: false,
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                });
            }

            return allValid;
        }

        function getStepForField(fieldName) {
            if (!fieldName) return 1;
            if (fieldName.startsWith('allowances.') || fieldName.startsWith('bank_accounts.')) return 5;
            if (fieldName.startsWith('dependants.') || ['login', 'email_notifications', 'slack_username', 'telegram_user_id', 'status', 'probation_end_date', 'employment_type', 'marital_status', 'no_of_dependants', 'internship_end_date', 'contract_end_date', 'notice_period_start_date', 'notice_period_end_date'].includes(fieldName)) return 4;
            if (['password', 'country', 'mobile', 'country_phonecode', 'gender', 'joining_date', 'basic_salary', 'reporting_to', 'locale', 'role', 'vehicle_allocation', 'vehicle_id', 'address', 'date_of_birth'].includes(fieldName)) return 3;
            if (['employee_type', 'iqama_no', 'iqama_profession', 'iqama_expiry_date', 'iqama_image', 'national_id', 'national_id_expiry_date', 'national_id_image', 'passport_no', 'passport_expiry_date', 'passport_image', 'sponsor_kafala', 'sponsorship_transfer_date', 'probation_time', 'qiva_contract', 'company_contract'].includes(fieldName)) return 2;
            return 1;
        }

        function showValidationErrorList(xhr) {
            var errorBag = xhr && xhr.responseJSON && xhr.responseJSON.errors ? xhr.responseJSON.errors : null;
            if (!errorBag) return;

            var firstField = Object.keys(errorBag)[0] || null;
            var firstStep = getStepForField(firstField);
            goToStep(firstStep);

            var listItems = [];
            Object.keys(errorBag).forEach(function (field) {
                errorBag[field].forEach(function (message) {
                    listItems.push('<li>' + $('<div/>').text(message || '').html() + '</li>');
                });
            });

            if (!listItems.length) return;

            Swal.fire({
                icon: 'error',
                title: 'Please fix the following errors',
                html: '<ul class="text-left pl-3 mb-0">' + listItems.join('') + '</ul>',
                showConfirmButton: true,
                confirmButtonText: '@lang('app.ok')'
            });
        }

        // ── EMPLOYMENT TYPE ───────────────────────────────────
        $('#employment_type').change(function() {
            var value = $(this).val();
            $('.contract-date').toggleClass('d-none', value !== 'on_contract');
            $('.internship-date').toggleClass('d-none', value !== 'internship');
        });

        // ── SAVE BUTTONS ──────────────────────────────────────
        $('#save-more-employee-form').click(function() {
            if (!validateDependants()) return;
            if (!validateAllowances()) return;
            if (!validateBankAccounts()) return;
            $('#add_more').val(true);
            const url = "{{ route('employees.store') }}";
            var data = $('#save-employee-data-form').serialize();
            saveEmployee(data, url, "#save-more-employee-form");
        });

        $('#save-employee-form').click(function() {
            if (!validateDependants()) return;
            if (!validateAllowances()) return;
            if (!validateBankAccounts()) return;
            const url = "{{ route('employees.store') }}";
            var data = $('#save-employee-data-form').serialize();
            saveEmployee(data, url, "#save-employee-form");
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
                success: function(response) {
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
                },
                error: function(xhr) {
                    showValidationErrorList(xhr);
                }
            });
        }

        // ── MISC ──────────────────────────────────────────────
        $('#random_password').click(function() {
            const randPassword = Math.random().toString(36).substr(2, 8);
            $('#password').val(randPassword);
        });

        $('#designation-setting-add').click(function() {
            const url = "{{ route('designations.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('.department-setting').click(function() {
            const url = "{{ route('departments.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#country').change(function() {
            var phonecode = $(this).find(':selected').data('phonecode');
            $('#country_phonecode').val(phonecode);
            $('.select-picker').selectpicker('refresh');
        });

        function toggleVehicleDiv() {
            if ($('input[name="vehicle_allocation"]:checked').val() === 'yes') {
                $('.vehicle-div').show();
            } else {
                $('.vehicle-div').hide();
                $('#vehicle_id').val('').trigger('change'); // Optional: reset selected vehicle
            }
        }

        // Run on page load
        toggleVehicleDiv();

        // Run when radio button changes
        $('input[name="vehicle_allocation"]').on('change', function () {
            toggleVehicleDiv();
        });

        init(RIGHT_MODAL);
    });

    $('.cropper').on('dropify.fileReady', function(e) {
        var inputId = $(this).find('input').attr('id');
        var url = "{{ route('cropper', ':element') }}";
        url = url.replace(':element', inputId);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    @if (function_exists('sms_setting') && sms_setting()->telegram_status)
        var clipboard = new ClipboardJS('.btn-copy');
        clipboard.on('success', function(e) {
            Swal.fire({
                icon: 'success', text: '@lang('app.urlCopied')',
                toast: true, position: 'top-end', timer: 3000,
                timerProgressBar: true, showConfirmButton: false,
                customClass: { confirmButton: 'btn btn-primary' },
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
            });
        });
    @endif
</script>
