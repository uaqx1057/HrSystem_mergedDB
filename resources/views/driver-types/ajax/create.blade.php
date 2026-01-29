@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-driver-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.driverTypes.personalDetails')
                </h4>


                <div class="row  p-20">
                    <div class="col-md-4">

                        <x-forms.text fieldId="name" :fieldLabel="__('modules.drivers.name')"
                            fieldName="name" fieldRequired="true"
                            :fieldPlaceholder="__('modules.drivers.nameInfo')">
                        </x-forms.text>

                        <x-forms.checkbox
                            :fieldLabel="__('modules.driverTypes.vehicle_monthly_cost')"
                            fieldName="fields[]" fieldRequired="true"
                            fieldValue="vehicle_monthly_cost"
                            fieldId="vehicle_monthly_cost"
                            >
                        </x-forms.checkbox>

                        <x-forms.checkbox
                            :fieldLabel="__('modules.driverTypes.mobile_data')"
                            fieldName="fields[]" fieldRequired="true"
                            fieldValue="mobile_data"
                            fieldId="mobile_data"
                            >
                        </x-forms.checkbox>

                        <x-forms.checkbox

                            :fieldLabel="__('modules.driverTypes.accommodation')"
                            fieldName="fields[]"
                            fieldRequired="true"
                            fieldValue="accommodation"
                            fieldId="accommodation"
                            >
                        </x-forms.checkbox>

                        <x-forms.checkbox

                            :fieldLabel="__('modules.driverTypes.government_cost')"
                            fieldName="fields[]"
                            fieldRequired="true"
                            fieldValue="government_cost"
                            fieldId="government_cost"
                            >
                        </x-forms.checkbox>

                        <x-forms.checkbox

                            :fieldLabel="__('modules.driverTypes.fuel')"
                            fieldName="fields[]"
                            fieldRequired="true"
                            fieldValue="fuel"
                            fieldId="fuel"
                            >
                        </x-forms.checkbox>

                        <x-forms.checkbox

                            :fieldLabel="__('modules.driverTypes.gprs')"
                            fieldName="fields[]"
                            fieldRequired="true"
                            fieldValue="gprs"
                            fieldId="gprs"
                            >
                        </x-forms.checkbox>


                    </div>
                    <div class="col-md-12">
                        <x-forms.toggle-switch class="mr-0 mr-lg-12"
                            :fieldLabel="__('modules.driverTypes.markAsFreelancer')"
                            fieldName="is_freelancer"
                            fieldId="is_freelancer"/>
                    </div>
                </div>

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

        $('#save-more-driver-form').click(function() {

            $('#add_more').val(true);

            const url = "{{ route('driver-types.store') }}";
            var data = $('#save-driver-data-form').serialize();
            saveDriver(data, url, "#save-more-driver-form");


        });

        $('#save-driver-form').click(function() {
            const url = "{{ route('driver-types.store') }}";
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
