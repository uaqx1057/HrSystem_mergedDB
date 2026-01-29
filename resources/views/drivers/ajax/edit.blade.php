@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="update-driver-data-form" method="PUT">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.drivers.personalDetails')
                </h4>

                <div class="col-lg-3">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                        :fieldLabel="__('modules.drivers.image')" fieldName="image" :fieldValue="$driver->image_url" fieldId="image"
                        fieldHeight="119" :popover="__('messages.fileFormat.ImageFile')" />
                </div>

                <div class="row  p-20">
                    <div class="col-md-4">
                        <x-forms.select2-ajax  fieldId="branch_id" fieldName="branch_id"
                            :fieldLabel="__('modules.drivers.branch')" :route="route('get.branch-ajax')"
                            :placeholder="__('placeholders.searchForBranches')">
                        </x-forms.select2-ajax>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="name" :fieldLabel="__('modules.drivers.name')"
                            fieldName="name" :fieldValue="$driver->name" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.nameInfo')">
                        </x-forms.text>
                    </div>

                     <div class="col-md-4">
                        <x-forms.select fieldId="driver_type_id" :fieldLabel="__('modules.drivers.driver_type_id')"
                            fieldName="driver_type_id" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.driver_type_id')">
                            @foreach ($driver_types as $item)
                                <option value="{{ $item->id }}" @selected($driver->driver_type_id == $item->id)>{{ $item->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="driver_id" :fieldLabel="__('modules.drivers.driverId')"
                            fieldName="driver_id" :fieldValue="$driver->driver_id" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.driverId')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="iqaama_number" :fieldLabel="__('modules.drivers.iqamaNumber')"
                            fieldName="iqaama_number" :fieldValue="$driver->iqaama_number" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.iqamaNumber')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="iqaama_expiry_date" :fieldLabel="__('modules.drivers.iqamaExpiry')"
                            fieldName="iqaama_expiry_date" :fieldValue="$driver->iqaama_expiry_date ? $driver->iqaama_expiry_date->format(company()->date_format) : null" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.iqamaExpiry')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="date_of_birth" :fieldLabel="__('modules.drivers.dateOfBirth')"
                            fieldName="date_of_birth" :fieldValue="$driver->date_of_birth ? $driver->date_of_birth->format(company()->date_format) : null" fieldRequired="true" :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="absher_number" :fieldValue="$driver->absher_number" fieldId="absher_number"
                        fieldLabel="{{ __('modules.drivers.absherNumber') }}" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.absherNumber')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldName="sponsorship" :fieldValue="$driver->sponsorship" fieldId="sponsorship"
                        fieldLabel="{{ __('modules.drivers.sponsorship') }}" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.sponsorship')" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.number fieldName="sponsorship_id" :fieldValue="$driver->sponsorship_id" fieldId="sponsorship_id"
                        fieldLabel="{{ __('modules.drivers.sponsorshipID') }}" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.sponsorshipID')" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="license_expiry_date" :fieldLabel="__('modules.drivers.licenseExpiry')"
                            fieldName="license_expiry_date" :fieldValue="$driver->license_expiry_date ? $driver->license_expiry_date->format(company()->date_format) : null" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.licenseExpiry')" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="insurance_policy_number" :fieldLabel="__('modules.drivers.insurancePolicyNumber')"
                            fieldName="insurance_policy_number" :fieldValue="$driver->insurance_policy_number" fieldRequired="true" :fieldPlaceholder="__('modules.drivers.insurancePolicyNumber')">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="insurance_expiry_date" :fieldLabel="__('modules.drivers.insuranceExpiry')"
                            fieldName="insurance_expiry_date" :fieldValue="$driver->insurance_expiry_date ? $driver->insurance_expiry_date->format(company()->date_format) : null"  fieldRequired="true" :fieldPlaceholder="__('modules.drivers.insuranceExpiry')" />
                    </div>

                    <div class="col-lg-4 col-md-6 vehicle_monthly_cost_div">
                        <x-forms.number fieldId="vehicle_monthly_cost"
                            :fieldValue="$driver->vehicle_monthly_cost" :fieldLabel="__('modules.driverTypes.vehicle_monthly_cost')"
                            fieldName="vehicle_monthly_cost" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.vehicle_monthly_cost')" />
                    </div>

                    <div class="col-lg-4 col-md-6 mobile_data_div">
                        <x-forms.number fieldId="mobile_data" :fieldValue="$driver->mobile_data" :fieldLabel="__('modules.driverTypes.mobile_data')"
                            fieldName="mobile_data" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.mobile_data')" />
                    </div>

                    <div class="col-lg-4 col-md-6 fuel_div">
                        <x-forms.number fieldId="fuel" :fieldValue="$driver->fuel" :fieldLabel="__('modules.driverTypes.fuel')"
                            fieldName="fuel" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.fuel')" />
                    </div>

                    <div class="col-lg-4 col-md-6 gprs_div">
                        <x-forms.number fieldId="gprs" :fieldValue="$driver->gprs" :fieldLabel="__('modules.driverTypes.gprs')"
                            fieldName="gprs" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.gprs')" />
                    </div>

                    <div class="col-lg-4 col-md-6 government_cost_div">
                        <x-forms.number fieldId="government_cost" :fieldValue="$driver->government_cost" :fieldLabel="__('modules.driverTypes.government_cost')"
                            fieldName="government_cost" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.government_cost')" />
                    </div>

                    <div class="col-lg-4 col-md-6 accommodation_div">
                        <x-forms.number fieldId="accommodation" :fieldValue="$driver->accommodation" :fieldLabel="__('modules.driverTypes.accommodation')"
                            fieldName="accommodation" fieldRequired="true" :fieldPlaceholder="__('modules.driverTypes.accommodation')" />
                    </div>

                    <div class="col-md-4">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.drivers.remarks')"
                                fieldName="remarks" :fieldValue="$driver->remarks" fieldId="remarks" :fieldPlaceholder="__('modules.drivers.remarks')">
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
                            fieldName="email" :fieldValue="$driver->email" fieldRequired="true" :fieldPlaceholder="__('placeholders.email')">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6">
                        <x-forms.label class="my-3" fieldId="work_mobile_no"
                            :fieldLabel="__('app.workMobile')" fieldRequired="true"></x-forms.label>
                        <x-forms.input-group style="margin-top:-4px">


                            <x-forms.select fieldId="work_mobile_country_code" fieldName="work_mobile_country_code" :fieldValue="$driver->work_mobile_country_code"
                                search="true">

                                @foreach ($countries as $item)
                                    <option data-tokens="{{ $item->name }}"
                                            data-content="{{$item->flagSpanCountryCode()}}"
                                            value="{{ $item->phonecode }}"  @selected($item->phonecode == $driver->work_mobile_country_code)  >{{ $item->phonecode }}
                                    </option>
                                @endforeach
                            </x-forms.select>

                            <input type="tel"
                                class="form-control height-35 f-14"
                                placeholder="@lang('placeholders.mobile')"
                                name="work_mobile_no"
                                id="work_mobile_no"
                                value="{{ $driver->work_mobile_no }}">
                        </x-forms.input-group>
                    </div>

                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="update-driver-form" class="mr-3" icon="check">
                        @lang('app.update')
                    </x-forms.button-primary>
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

        $('#update-driver-form').click(function() {

            const url = "{{ route('drivers.update', $driver->id) }}";
            var data = $('#update-driver-data-form').serialize();
            updateDriver(data, url, "#update-driver-form");

        });

        function updateDriver(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#update-driver-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = '{{ route('drivers.index') }}';
                    }

                }
            });
        }

        $('#country').change(function(){
            var phonecode = $(this).find(':selected').data('phonecode');
            $('#country_phonecode').val(phonecode);
            $('.select-picker').selectpicker('refresh');
        });

        const driverBranch = @json($driver->branch()->first([ 'id', 'name' ]));
        if (driverBranch) {
            const option = new Option(driverBranch.name, driverBranch.id, true, true);
            $('#branch_id').append(option).val(driverBranch.id).trigger('change');
        }


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
