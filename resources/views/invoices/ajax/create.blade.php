@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-driver-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.invoiceDetails')
                </h4>
                <div class="row p-20">
                    {{-- Start: Invoice Number --}}
                    <div class="col-md-2">
                        <x-forms.number fieldId="invoice_number" :fieldLabel="__('modules.invoices.invoiceNumber')"
                            fieldName="invoice_number" fieldRequired="true" fieldReadOnly="true"
                            :fieldPlaceholder="__('modules.invoices.invoiceNumber')" :fieldValue="$lastInvoice">
                        </x-forms.number>
                    </div>
                    {{-- End: Invoice Number --}}
                </div>
                <hr class="m-0 border-top-grey">
                <div class="row p-20">

                    {{-- Start: Invoice Date --}}
                    <div class="col-2">
                        <x-forms.datepicker fieldId="invoice_date" :fieldLabel="__('modules.invoices.invoiceDate')"
                            fieldName="invoice_date" fieldRequired="true" :fieldPlaceholder="__('modules.invoices.invoiceDate')" />
                    </div>
                    {{-- End: Invoice Date --}}

                    {{-- Start: Driver ID --}}
                    <div class="col-6">
                        <x-forms.select2-ajax  fieldId="driver_id" fieldName="driver_id"
                            :fieldLabel="__('app.received_from_driver')" :route="route('get.driver-ajax')"
                            :placeholder="__('placeholders.searchForDrivers')" fieldRequired="true">
                        </x-forms.select2-ajax>
                    </div>
                    {{-- End: Driver ID --}}

                    {{-- Start: Total Amount --}}
                    <div class="col-4">
                        <x-forms.number fieldName="total_amount" fieldId="total_amount"
                        fieldLabel="{{ __('modules.payments.totalAmount') }}" fieldRequired="true" :fieldPlaceholder="__('modules.payments.totalAmount')" />
                    </div>
                    {{-- End: Total Amount --}}

                </div>
                <hr class="m-0 border-top-grey">
                <div class="row  p-20">


                    {{-- Start: City --}}
                    <div class="col-md-2">
                        <x-forms.text fieldId="city" :fieldLabel="__('modules.stripeCustomerAddress.city')"
                            fieldName="city" fieldReadOnly="true"
                            :fieldPlaceholder="__('modules.stripeCustomerAddress.city')">
                        </x-forms.text>
                    </div>
                    {{-- End: City --}}


                    {{-- Start: Iqama Number --}}
                    <div class="col-md-2">
                        <x-forms.text fieldId="iqaama_number" :fieldLabel="__('modules.drivers.iqamaNumber')"
                            fieldName="iqaama_number" fieldReadOnly="true"
                            :fieldPlaceholder="__('modules.drivers.iqamaNumber')">
                        </x-forms.text>
                    </div>
                    {{-- End: Iqama Number --}}

                    {{-- Start: Businesses --}}
                    <div class="col-md-2">
                        <x-forms.select fieldId="business_id" :fieldLabel="__('app.menu.businesses')"
                            fieldName="business_id"
                            :fieldPlaceholder="__('app.menu.businesses')">

                        </x-forms.select>
                    </div>
                    {{-- End: Businesses --}}

                    {{-- Start: Account Number --}}
                    <div class="col-md-2">
                        <x-forms.text fieldId="account_number" :fieldLabel="__('modules.bankaccount.accountNumber')"
                            fieldName="account_number" fieldReadOnly="true"
                            :fieldPlaceholder="__('modules.bankaccount.accountNumber')">
                        </x-forms.text>
                    </div>
                    {{-- End: Account Number --}}

                    {{-- Start: Other Business --}}
                    <div class="col-md-4">
                        <x-forms.text fieldName="other_business" fieldId="other_business"
                        fieldLabel="{{ __('modules.businesses.other_business') }}" :fieldPlaceholder="__('modules.businesses.other_business')" />
                    </div>
                    {{-- End: Other Business --}}

                    {{-- Start: Start Date --}}
                    <div class="col-md-2">
                        <x-forms.datepicker fieldId="start_date" :fieldLabel="__('modules.invoices.startDate')"
                            fieldName="start_date" fieldRequired="true" :fieldPlaceholder="__('placeholders.date')" />
                    </div>
                    {{-- End: Start Date --}}

                    {{-- Start: End Date --}}
                    <div class="col-md-2">
                        <x-forms.datepicker fieldId="end_date" :fieldLabel="__('modules.invoices.endDate')"
                            fieldName="end_date" fieldRequired="true" :fieldPlaceholder="__('placeholders.date')" />
                    </div>
                    {{-- End: End Date --}}

                    {{-- Start: Wallet Amount--}}
                    <div class="col-md-4">
                        <x-forms.number fieldName="wallet_amount" fieldId="wallet_amount"
                        fieldLabel="{{ __('modules.invoices.walletAmount') }}" fieldRequired="true" :fieldPlaceholder="__('modules.invoices.walletAmount')" />
                    </div>
                    {{-- End: Wallet Amount--}}

                    {{-- Start: Status --}}
                    <div class="col-md-2">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')"
                            fieldName="status"
                            :fieldPlaceholder="__('app.status')">
                            <option value="paid">Paid</option>
                            <option value="unpaid">Unpaid</option>
                            <option value="partial">Partial</option>
                            <option value="canceled">Canceled</option>
                            <option value="draft">Draft</option>
                        </x-forms.select>
                    </div>
                    {{-- End: Status --}}
                </div>



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

<script>

    $(document).ready(function() {
        $('#business_id').on('change', function(){
            var business_id = $(this).val();
            var driver_id = $('#driver_id').val();
            if(business_id){
                const url = "{{route('invoices.get-driver-business-info')}}";
                $.easyAjax({
                    url: url,
                    type: "GET",
                    data: { driver_id, business_id },
                    blockUI: true,
                    success: function(response) {
                        $('#account_number').val(response?.platform_id);
                    }
                });
            }else{
                $('#account_number').val('');
            }
        });

        $('#driver_id').on('change', function(){
            var driver_id = $(this).val();
            if(driver_id){
                var url = "{{route('invoices.get-driver', ':id')}}",
                url = (driver_id) ? url.replace(':id', driver_id) : url.replace(':id', null);
                $.easyAjax({
                    url : url,
                    type : "GET",
                    container: '#saveInvoiceForm',
                    blockUI: true,
                    success: function (response) {
                        $('#iqaama_number').val(response?.iqaama_number);
                        $('#city').val(response?.branch?.name);

                        let options = '<option>--</option>';
                        response?.businesses?.forEach(business => {
                            console.log(business)
                            options += `<option value="${business.id}">${business.name}</option>`;
                        });
                        $('#business_id').html(options).selectpicker('refresh');
                    }
                });
            }else{
                $('#iqaama_number').val('');
                $('#city').val('');
                $('#business_id').html('').selectpicker('refresh');

            }

        });



        datepicker('#invoice_date', {
            position: 'bl',
            maxDate: new Date(),
            ...datepickerConfig
        });

        datepicker('#start_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#end_date', {
            position: 'bl',
            ...datepickerConfig
        });



        $('#save-more-driver-form').click(function() {

            $('#add_more').val(true);

            const url = "{{ route('invoices.store') }}";
            var data = $('#save-driver-data-form').serialize();
            saveDriver(data, url, "#save-more-driver-form");


        });

        $('#save-driver-form').click(function() {

            const url = "{{ route('invoices.store') }}";
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
                            // window.location.reload();
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

