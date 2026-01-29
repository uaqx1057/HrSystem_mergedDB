@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-driver-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.drivers.personalDetails')
                </h4>

                <div class="col-lg-3">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                        :fieldLabel="__('modules.drivers.image')" fieldName="image" fieldId="image"
                        fieldHeight="119" :popover="__('messages.fileFormat.ImageFile')" />
                </div>

                <div class="row  p-20">
                    <div class="col-md-4">
                        <x-forms.select2-ajax  fieldId="branch_id" fieldName="branch_id"
                            :fieldLabel="__('modules.drivers.branch')" :route="route('get.branch-ajax')"
                            :placeholder="__('placeholders.searchForBranches')" fieldRequired="true">
                        </x-forms.select2-ajax>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="name" :fieldLabel="__('modules.drivers.name')"
                            fieldName="name" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.nameInfo')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.select fieldId="driver_type_id" :fieldLabel="__('modules.drivers.driver_type_id')"
                            fieldName="driver_type_id" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.driver_type_id')">
                            @foreach ($driver_types as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>



                    <div class="col-md-4">
                        <x-forms.text fieldId="driver_id" :fieldLabel="__('modules.drivers.driverId')"
                            fieldName="driver_id" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.driverId')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="iqaama_number" :fieldLabel="__('modules.drivers.iqamaNumber')"
                            fieldName="iqaama_number" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.iqamaNumber')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="iqaama_expiry_date" :fieldLabel="__('modules.drivers.iqamaExpiry')"
                            fieldName="iqaama_expiry_date" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.iqamaExpiry')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="date_of_birth" :fieldLabel="__('modules.drivers.dateOfBirth')"
                            fieldName="date_of_birth" fieldRequired="true" :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="absher_number" fieldId="absher_number"
                        fieldLabel="{{ __('modules.drivers.absherNumber') }}" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.absherNumber')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldName="sponsorship" fieldId="sponsorship"
                        fieldLabel="{{ __('modules.drivers.sponsorship') }}" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.sponsorship')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="sponsorship_id" fieldId="sponsorship_id"
                        fieldLabel="{{ __('modules.drivers.sponsorshipID') }}" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.sponsorshipID')" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="license_expiry_date" :fieldLabel="__('modules.drivers.licenseExpiry')"
                            fieldName="license_expiry_date" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.licenseExpiry')" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="insurance_policy_number" :fieldLabel="__('modules.drivers.insurancePolicyNumber')"
                            fieldName="insurance_policy_number" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.insurancePolicyNumber')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="insurance_expiry_date" :fieldLabel="__('modules.drivers.insuranceExpiry')"
                            fieldName="insurance_expiry_date" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.insuranceExpiry')" />
                    </div>


                    <div class="col-lg-4 col-md-6 vehicle_monthly_cost_div">
                        <x-forms.number fieldId="vehicle_monthly_cost" :fieldLabel="__('modules.driverTypes.vehicle_monthly_cost')"
                            fieldName="vehicle_monthly_cost" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.vehicle_monthly_cost')" fieldValue="1247"/>
                    </div>

                    <div class="col-lg-4 col-md-6 mobile_data_div">
                        <x-forms.number fieldId="mobile_data" :fieldLabel="__('modules.driverTypes.mobile_data')"
                            fieldName="mobile_data" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.mobile_data')" fieldValue="100"/>
                    </div>

                    <div class="col-lg-4 col-md-6 fuel_div">
                        <x-forms.number fieldId="fuel" :fieldLabel="__('modules.driverTypes.fuel')"
                            fieldName="fuel" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.mobile_data')" fieldValue="100"/>
                    </div>

                    <div class="col-lg-4 col-md-6 gprs_div">
                        <x-forms.number fieldId="gprs" :fieldLabel="__('modules.driverTypes.gprs')"
                            fieldName="gprs" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.mobile_data')" fieldValue="100"/>
                    </div>

                    <div class="col-lg-4 col-md-6 government_cost_div">
                        <x-forms.number fieldId="government_cost" :fieldLabel="__('modules.driverTypes.government_cost')"
                            fieldName="government_cost" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.government_cost')" fieldValue="880"/>
                    </div>

                    <div class="col-lg-4 col-md-6 accommodation_div">
                        <x-forms.number fieldId="accommodation" :fieldLabel="__('modules.driverTypes.accommodation')"
                            fieldName="accommodation" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.accommodation')" fieldValue="200"/>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.drivers.remarks')"
                                fieldName="remarks" fieldId="remarks" :fieldPlaceholder="__('modules.drivers.remarks')">
                            </x-forms.textarea>
                        </div>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.drivers.contact')
                </h4>

                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="email" :fieldLabel="__('modules.drivers.emailAddress')"
                            fieldName="email" fieldRequired="true" :fieldPlaceholder="__('placeholders.email')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6">
                        <x-forms.label class="my-3" fieldId="work_mobile_no"
                            :fieldLabel="__('app.workMobile')" fieldRequired="true"></x-forms.label>
                        <x-forms.input-group style="margin-top:-4px">


                            <x-forms.select fieldId="work_mobile_country_code" fieldName="work_mobile_country_code"
                                search="true">

                                @foreach ($countries as $item)
                                    <option data-tokens="{{ $item->name }}"
                                            data-content="{{$item->flagSpanCountryCode()}}"
                                            value="{{ $item->phonecode }}"  @selected($item->phonecode == config('app.DEFAULT_PHONE_CODE'))  >{{ $item->phonecode }}
                                    </option>
                                @endforeach
                            </x-forms.select>

                            <input type="tel" class="form-control height-35 f-14" placeholder="@lang('placeholders.mobile')"
                                name="work_mobile_no" id="work_mobile_no">
                        </x-forms.input-group>
                    </div>

                </div>

                {{-- <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.drivers.address')
                </h4>

                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.select fieldId="country" :fieldLabel="__('app.nationality')" fieldName="nationality" search="true">
                            @foreach ($countries as $item)
                                <option data-tokens="{{ $item->iso3 }}" data-phonecode = "{{$item->phonecode}}"
                                    data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nicename }}"
                                    value="{{ $item->id }}">{{ $item->nicename }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="address" :fieldLabel="__('modules.drivers.address')"
                            fieldName="address" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.address')">
                        </x-forms.text>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.drivers.documents')
                </h4>

                <div class="row p-20">

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.drivers.bankDetails')
                </h4>

                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.number fieldName="stc_pay" fieldId="stc_pay"
                        fieldLabel="{{ __('modules.drivers.stcPay') }}" :fieldPlaceholder="__('modules.drivers.stcPay')" />
                    </div>

                    <div class="col-md-6">
                        <x-forms.text fieldId="bank_name" :fieldLabel="__('modules.drivers.bankName')"
                            fieldName="bank_name" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.bankName')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6">
                        <x-forms.text fieldId="iban" :fieldLabel="__('modules.drivers.iban')"
                            fieldName="iban" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.iban')">
                        </x-forms.text>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.drivers.employment')
                </h4>

                <div class="row p-20">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="department" :fieldLabel="__('modules.drivers.department')"
                            fieldName="department" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.department')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="job_position" :fieldLabel="__('modules.drivers.jobPosition')"
                            fieldName="job_position" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.jobPosition')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="date_of_joining" :fieldLabel="__('modules.drivers.dateOfJoining')"
                            fieldName="date_of_joining" :fieldPlaceholder="__('modules.drivers.dateOfJoining')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="contract_period_in_years" fieldId="contract_period_in_years"
                        fieldLabel="{{ __('modules.drivers.contractPeriod') }}" :fieldPlaceholder="__('placeholders.contractPeriodInYears')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="basic_salary" fieldId="basic_salary"
                        fieldLabel="{{ __('modules.drivers.basicSalary') }}" :fieldPlaceholder="__('modules.drivers.basicSalary')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="housing_allowance" fieldId="housing_allowance"
                        fieldLabel="{{ __('modules.drivers.housingAllowance') }}" :fieldPlaceholder="__('modules.drivers.housingAllowance')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="transportation_allowance" fieldId="transportation_allowance"
                        fieldLabel="{{ __('modules.drivers.transportationAllowance') }}" :fieldPlaceholder="__('modules.drivers.transportationAllowance')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="performance_allowance" fieldId="performance_allowance"
                        fieldLabel="{{ __('modules.drivers.performanceAllowance') }}" :fieldPlaceholder="__('modules.drivers.performanceAllowance')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="other_allowance" fieldId="other_allowance"
                        fieldLabel="{{ __('modules.drivers.otherAllowance') }}" :fieldPlaceholder="__('modules.drivers.otherAllowance')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="total_salary" fieldId="total_salary"
                        fieldLabel="{{ __('modules.drivers.totalSalary') }}" :fieldPlaceholder="__('modules.drivers.totalSalary')" />
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.drivers.vehicle')
                </h4>

                <div class="row p-20">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="vehicle_name" :fieldLabel="__('modules.drivers.vehicleName')"
                            fieldName="vehicle_name" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.vehicleName')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="vehicle_model" :fieldLabel="__('modules.drivers.vehicleModel')"
                            fieldName="vehicle_model" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.vehicleModel')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="vehicle_car_plate" :fieldLabel="__('modules.drivers.vehicleCarPlate')"
                            fieldName="vehicle_car_plate" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.vehicleCarPlate')">
                        </x-forms.text>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.drivers.projects')
                </h4>

                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.number fieldName="hunger_id" fieldId="hungerId"
                        fieldLabel="{{ __('modules.drivers.hungerId') }}" :fieldPlaceholder="__('modules.drivers.hungerId')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="jahez_id" fieldId="jahez_id"
                        fieldLabel="{{ __('modules.drivers.jahezId') }}" :fieldPlaceholder="__('modules.drivers.jahezId')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="speed_logistic_id" fieldId="speed_logistic_id"
                        fieldLabel="{{ __('modules.drivers.speedLogisticId') }}" :fieldPlaceholder="__('modules.drivers.speedLogisticId')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="speed_kitchen_id" fieldId="speed_kitchen_id"
                        fieldLabel="{{ __('modules.drivers.speedKitchenId') }}" :fieldPlaceholder="__('modules.drivers.speedKitchenId')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="eCommerceId" fieldId="eCommerceId"
                        fieldLabel="{{ __('modules.drivers.eCommerceId') }}" :fieldPlaceholder="__('modules.drivers.eCommerceId')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="other_project_id" fieldId="other_project_id"
                        fieldLabel="{{ __('modules.drivers.otherProjectId') }}" :fieldPlaceholder="__('modules.drivers.otherProjectId')" />
                    </div>
                </div> --}}

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-driver-form" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-driver-form" icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel class="border-0 " data-dismiss="modal">@lang('app.cancel')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
@if (function_exists('sms_setting') && sms_setting()->telegram_status)
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
@endif
<script>

    $(document).ready(function() {

        $('#driver_type_id').change(function(){
            changeInputFields($(this).val());
        });

        changeInputFields($('#driver_type_id').val());
        function changeInputFields(driver_type_id){
            $.easyAjax({
                url: "{{ route('drivers.get-driver-type') }}",
                type: "GET",
                data: {id: driver_type_id},
                success: function(response) {

                    const fields = response.fields.split(',');
                     ['vehicle_monthly_cost', 'mobile_data', 'accommodation', 'government_cost'].forEach(field => $(`.${field}_div`).addClass('d-none'));
                    fields.forEach(field => $(`.${field}_div`).removeClass('d-none'));
                }
            });
        }

        datepicker('#date_of_birth', {
            position: 'bl',
            maxDate: new Date(),
            ...datepickerConfig
        });

        datepicker('#iqaama_expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#license_expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#insurance_expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });

        $('#save-more-driver-form').click(function() {

            $('#add_more').val(true);

            const url = "{{ route('drivers.store') }}";
            var data = $('#save-driver-data-form').serialize();
            saveDriver(data, url, "#save-more-driver-form");


        });

        $('#save-driver-form').click(function() {

            const url = "{{ route('drivers.store') }}";
            var data = $('#save-driver-data-form').serialize();
            saveDriver(data, url, "#save-driver-form");

        });

        function saveDriver(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#save-driver-data-form',
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
                        }
                        else if(response.add_more == true) {

                            var right_modal_content = $.trim($(RIGHT_MODAL_CONTENT).html());

                            if(right_modal_content.length) {

                                $(RIGHT_MODAL_CONTENT).html(response.html.html);
                                $('#add_more').val(false);
                            }
                            else {

                                $('.content-wrapper').html(response.html.html);
                                init('.content-wrapper');
                                $('#add_more').val(false);
                            }

                        }
                        else {

                            window.location.href = response.redirectUrl;

                        }

                        if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                            showTable();
                        }

                    }

                }
            });
        }

        $('#country').change(function(){
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
</script>
