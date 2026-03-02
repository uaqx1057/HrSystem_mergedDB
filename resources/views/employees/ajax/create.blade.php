@php
    $addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-employee-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.employees.accountDetails')</h4>
                <div class="row p-20">
                    <div class="col-lg-9">
                        <div class="row">
                            <div class="col-lg-2 col-md-3">
                                <x-forms.text fieldId="employee_id" :fieldLabel="__('modules.employees.employeeId')" fieldName="employee_id"
                                    :fieldValue="!$checkifExistEmployeeId ? $lastEmployeeID + 1 : ''" fieldRequired="true" :fieldPlaceholder="__('modules.employees.employeeIdInfo')" :popover="__('modules.employees.employeeIdHelp')">
                                </x-forms.text>
                            </div>
                            <div class="col-lg-2 col-md-3">
                                <x-forms.select fieldId="salutation" fieldName="salutation" :fieldLabel="__('modules.client.salutation')">
                                    <option value="">--</option>
                                    @foreach ($salutations as $salutation)
                                        <option value="{{ $salutation->value }}">{{ $salutation->label() }}</option>
                                    @endforeach
                                </x-forms.select>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <x-forms.text fieldId="name" :fieldLabel="__('modules.employees.employeeName')" fieldName="name" fieldRequired="true"
                                    :fieldPlaceholder="__('placeholders.name')">
                                </x-forms.text>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <x-forms.text fieldId="email" :fieldLabel="__('modules.employees.employeeEmail')" fieldName="email" fieldRequired="true"
                                    :fieldPlaceholder="__('placeholders.email')">
                                </x-forms.text>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <x-forms.datepicker fieldId="date_of_birth" :fieldLabel="__('modules.employees.dateOfBirth')" fieldName="date_of_birth"
                                    :fieldPlaceholder="__('placeholders.date')" />
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId="category_id" :fieldLabel="__('app.designation')"
                                    fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="designation"
                                        id="employee_designation" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($designations as $designation)
                                            <option value="{{ $designation->id }}">{{ $designation->name }}</option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>
                            <div class="col-lg-4 col-md-6">
                                <x-forms.label class="my-3" fieldId="category_id" :fieldLabel="__('app.department')"
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
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                            :fieldLabel="__('modules.profile.profilePicture')" fieldName="image" fieldId="image" fieldHeight="119" :popover="__('messages.fileFormat.ImageFile')" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="iqama_no" :fieldLabel="__('modules.employees.Iqama No')" fieldName="iqama_no" fieldRequired="true"
                            :fieldPlaceholder="__('placeholders.iqama')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="iqama_profession" :fieldLabel="__('modules.employees.iqama_profession')" fieldName="iqama_profession"
                            fieldRequired="true" :fieldPlaceholder="__('placeholders.iqama_profession')">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="iqama_expiry_date" :fieldLabel="__('modules.employees.iqama_expiry_date')" fieldName="iqama_expiry_date"
                            :fieldPlaceholder="__('placeholders.iqama_expiry_date')" minlength="10" maxlength="10" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                            :fieldLabel="__('modules.employees.iqama_image')" fieldName="iqama_image" fieldId="iqama_image" fieldHeight="119"
                            :popover="__('messages.fileFormat.iqama_image')" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="passport_no" :fieldLabel="__('modules.employees.passport_no')" fieldName="passport_no"
                            fieldRequired="true" :fieldPlaceholder="__('placeholders.passport_no')">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="passport_expiry_date" :fieldLabel="__('modules.employees.passport_expiry_date')"
                            fieldName="passport_expiry_date" :fieldPlaceholder="__('placeholders.passport_expiry_date')" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                            :fieldLabel="__('modules.employees.passport_image')" fieldName="passport_image" fieldId="passport_image" fieldHeight="119"
                            :popover="__('messages.fileFormat.passport_image')" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="sponsor_kafala." :fieldLabel="__('modules.employees.Sponsor / kafala')" fieldName="sponsor_kafala"
                            fieldRequired="true" :fieldPlaceholder="__('placeholders.sponsor_kafala')">
                        </x-forms.text>
                    </div>
                    @php
                        $showButton = true; // or false, depending on your condition
                    @endphp

                    <div class="col-lg-3">
                        <x-forms.text fieldId="password" :fieldLabel="__('modules.employees.password')" fieldName="password" fieldRequired="true"
                            :fieldPlaceholder="__('placeholders.password')" :fieldValue="old('password')" :showButton="$showButton">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country" search="true">
                            @foreach ($countries as $item)
                                <option data-tokens="{{ $item->iso3 }}" data-phonecode = "{{ $item->phonecode }}"
                                    data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nicename }}"
                                    value="{{ $item->id }}">{{ $item->nicename }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <x-forms.label class="my-3" fieldId="mobile" :fieldLabel="__('app.mobile')"></x-forms.label>
                        <x-forms.input-group style="margin-top:-4px">


                            <x-forms.select fieldId="country_phonecode" fieldName="country_phonecode" search="true">

                                @foreach ($countries as $item)
                                    <option data-tokens="{{ $item->name }}"
                                        data-content="{{ $item->flagSpanCountryCode() }}"
                                        value="{{ $item->phonecode }}">{{ $item->phonecode }}
                                    </option>
                                @endforeach
                            </x-forms.select>

                            <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                name="mobile" id="mobile">
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
                    <div class="col-lg-4 col-md-6">
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
                        <x-forms.select fieldId="locale" :fieldLabel="__('app.language')" fieldName="locale" search="true">
                            @foreach ($languages as $language)
                                <option {{ user()->locale == $language->language_code ? 'selected' : '' }}
                                    data-content="<span class='flag-icon flag-icon-{{ $language->flag_code == 'en' ? 'gb' : $language->flag_code }} flag-icon-squared'></span> {{ $language->language_name }}"
                                    value="{{ $language->language_code }}">{{ $language->language_name }}</option>
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
                        <div class="form-group my-3">
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
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.address')" fieldName="address"
                                fieldId="address" :fieldPlaceholder="__('placeholders.address')">
                            </x-forms.textarea>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.about')" fieldName="about_me"
                                fieldId="about_me" fieldPlaceholder="">
                            </x-forms.textarea>
                        </div>
                    </div>

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.client.clientOtherDetails')</h4>
                <div class="row p-20">
                    <div class="col-lg-3 col-md-6">
                        <div class="form-group my-3">
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
                        <div class="form-group my-3">
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
                        <x-forms.label class="my-3" fieldId="slack_username" :fieldLabel="__('modules.employees.linkedinUsername')"></x-forms.label>
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
                        <x-forms.datepicker fieldId="notice_period_start_date" :fieldLabel="__('modules.employees.noticePeriodStartDate')"
                            fieldName="notice_period_start_date" :fieldPlaceholder="__('placeholders.date')" :popover="__('messages.noticePeriodStartDate')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.datepicker fieldId="notice_period_end_date" :fieldLabel="__('modules.employees.noticePeriodEndDate')"
                            fieldName="notice_period_end_date" :fieldPlaceholder="__('placeholders.date')" :popover="__('messages.noticePeriodEndDate')" />
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

                    {{-- ▼▼▼ NEW: no_of_dependants (hidden until married) ▼▼▼ --}}
                    <div class="col-lg-3 col-md-6 d-none dependant">
                        <x-forms.text fieldId="no_of_dependants" :fieldLabel="__('modules.employees.no_of_dependants')" fieldName="no_of_dependants"
                            :fieldPlaceholder="__('placeholders.no_of_dependants')">
                        </x-forms.text>
                    </div>

                    {{-- ▼▼▼ NEW: dynamic dependant rows container ▼▼▼ --}}
                    <div class="col-md-12 d-none dependant-rows-wrapper">
                        <hr>
                        <h6 class="f-15 font-weight-bold mb-3">@lang('modules.employees.dependants')</h6>
                        <div id="dependant-rows"></div>
                        <button type="button" id="add-dependant-btn" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fa fa-plus mr-1"></i> @lang('modules.employees.addDependant')
                        </button>
                    </div>

                    <input type ="hidden" name="add_more" value="false" id="add_more" />

                </div>
                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                {{-- Add Allowance  --}}
                <div class="col-md-12 allowances-rows-wrapper">
                    <hr>
                    <h6 class="f-15 font-weight-bold mb-3">@lang('modules.employees.allowances')</h6>
                    <div id="allowances-rows"></div>
                    <button type="button" id="add-allowances-btn" class="btn btn-outline-primary btn-sm my-2">
                        <i class="fa fa-plus mr-1"></i> @lang('modules.employees.addAllowance')
                    </button>
                </div>
                <x-form-actions>
                    <x-forms.button-primary id="save-employee-form" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-employee-form"
                        icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel class="border-0 " data-dismiss="modal">@lang('app.cancel')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<!-- <script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script> -->
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

        $('.custom-date-picker').each(function(ind, el) {
            datepicker(el, {
                position: 'bl',
                ...datepickerConfig
            });
        });

        datepicker('#iqama_expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });
        datepicker('#passport_expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });
        datepicker('#joining_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#probation_end_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#notice_period_start_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#notice_period_end_date', {
            position: 'bl',
            ...datepickerConfig
        });


        datepicker('#date_of_birth', {
            position: 'bl',
            maxDate: new Date(),
            ...datepickerConfig
        });

        datepicker('#internship_end_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#contract_end_date', {
            position: 'bl',
            ...datepickerConfig
        });

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
        var maxDependants = 0;
        var addedDependants = 0;

        $('#no_of_dependants').on('input', function() {
            maxDependants = parseInt($(this).val()) || 0;
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

            if (!isMarried) return true; // No validation needed

            var rows = $('#dependant-rows .dependant-row');

            if (rows.length === 0) return true; // No rows added, skip

            var allValid = true;

            rows.each(function() {
                var nameInput = $(this).find('input[name$="[name]"]');
                var nameVal = nameInput.val().trim();

                if (nameVal === '') {
                    nameInput.addClass('is-invalid'); // highlight empty field
                    allValid = false;
                } else {
                    nameInput.removeClass('is-invalid');
                }
            });

            if (!allValid) {
                // alert('All dependants name are required.');
                Swal.fire({
                    icon: 'error',
                    text: 'All dependants name are required.',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
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
                    <div class="col-lg-2 col-md-5 mb-2">
                        <label class="f-14 text-dark-grey">Date of Birth</label>
                        <input type="text" id="dep_dob_${idx}"
                               class="form-control height-35 f-14 dependant-dob"
                               name="dependants[${idx}][date_of_birth]" placeholder="Date of Birth" autocomplete="off">
                    </div>
                    <div class="col-lg-1 col-md-1 mb-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-dependant-btn">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>`;
            $('#dependant-rows').append(row);

            // init datepicker on the new dob field
            datepicker('#dep_dob_' + idx, {
                position: 'bl',
                maxDate: new Date(),
                ...datepickerConfig
            });

            addedDependants++;
            updateAddButton();
        }

        $('#add-dependant-btn').on('click', function() {
            addDependantRow();
        });

        $(document).on('click', '.remove-dependant-btn', function() {
            $(this).closest('.dependant-row').remove();
            addedDependants = $('#dependant-rows .dependant-row').length;
            // re-index names
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

        $('#add-allowances-btn').on('click', function() {
            addAllowanceRow();
        });

        $(document).on('click', '.remove-allowance-btn', function() {
            $(this).closest('.allowance-row').remove();

            // Re-index remaining rows
            $('#allowances-rows .allowance-row').each(function(i) {
                $(this).find('[name]').each(function() {
                    var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + i +
                    ']');
                    $(this).attr('name', newName);
                });
                allowanceIndex = i + 1;
            });

            // Reset index if no rows left
            if ($('#allowances-rows .allowance-row').length === 0) {
                allowanceIndex = 0;
            }
        });

        function validateAllowances() {
            var allValid = true;

            $('#allowances-rows .allowance-row').each(function() {
                var nameInput = $(this).find('input[name$="[name]"]');
                var amountInput = $(this).find('input[name$="[amount]"]');

                if (nameInput.val().trim() === '') {
                    nameInput.addClass('is-invalid');
                    allValid = false;
                } else {
                    nameInput.removeClass('is-invalid');
                }

                if (amountInput.val().trim() === '' || parseFloat(amountInput.val()) < 0) {
                    amountInput.addClass('is-invalid');
                    allValid = false;
                } else {
                    amountInput.removeClass('is-invalid');
                }
            });

            if (!allValid) {
                // alert('All allowance fields (Name and Amount) are required.');
                Swal.fire({
                    icon: 'error',
                    text: 'All allowance fields (Name and Amount) are required.',
                    toast: true,
                    position: 'top-end',
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                });
            }

            return allValid;
        }

        $('#employment_type').change(function() {
            var value = $(this).val();
            if (value == 'on_contract') {
                $('.contract-date').removeClass('d-none');
            } else {
                $('.contract-date').addClass('d-none');
            }

            if (value == 'internship') {
                $('.internship-date').removeClass('d-none');
            } else {
                $('.internship-date').addClass('d-none');
            }
        })
        // var input = document.querySelector('input[name=tags]'),
        //     // init Tagify script on the above inputs
        //     tagify = new Tagify(input);

        $('#save-more-employee-form').click(function() {
            if (!validateDependants()) {
                return; // Stop form submission
            }
            if (!validateAllowances()) return; // ← add this line
            $('#add_more').val(true);

            const url = "{{ route('employees.store') }}";
            var data = $('#save-employee-data-form').serialize();
            saveEmployee(data, url, "#save-more-employee-form");


        });

        $('#save-employee-form').click(function() {

            if (!validateDependants()) {
                return; // Stop form submission
            }
            if (!validateAllowances()) return; // ← add this line
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

                }
            });
        }

        $('#random_password').click(function() {
            const randPassword = Math.random().toString(36).substr(2, 8);

            $('#password').val(randPassword);
        });

        $('#designation-setting-add').click(function() {
            const url = "{{ route('designations.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        })

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


        init(RIGHT_MODAL);
    });

    $('.cropper').on('dropify.fileReady', function(e) {
        var inputId = $(this).find('input').attr('id');
        var url = "{{ route('cropper', ':element') }}";
        url = url.replace(':element', inputId);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })

    @if (function_exists('sms_setting') && sms_setting()->telegram_status)
        var clipboard = new ClipboardJS('.btn-copy');

        clipboard.on('success', function(e) {
            Swal.fire({
                icon: 'success',
                text: '@lang('app.urlCopied')',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                },
                showClass: {
                    popup: 'swal2-noanimation',
                    backdrop: 'swal2-noanimation'
                },
            })
        });
    @endif
</script>
