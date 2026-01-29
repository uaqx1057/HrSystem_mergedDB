

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form method="PUT" id="save-driver-data-form">
            <input type="hidden" name="id" value="{{ $receiptVoucher->id }}">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.invoiceDetails')
                </h4>
                <div class="row p-20">
                    {{-- Start: Invoice Number --}}
                    <div class="col-md-2">
                        <x-forms.number fieldId="voucher_number" :fieldLabel="__('modules.invoices.voucherNumber')"
                            fieldName="voucher_number" fieldRequired="true" fieldReadOnly="true"
                            :fieldPlaceholder="__('modules.invoices.voucherNumber')" :fieldValue="$receiptVoucher->voucher_number">
                        </x-forms.number>
                    </div>
                    {{-- End: Invoice Number --}}
                </div>
                <hr class="m-0 border-top-grey">
                <div class="row p-20">

                    {{-- Start: Invoice Date --}}
                    <div class="col-2">
                        <x-forms.datepicker fieldId="voucher_date" :fieldLabel="__('modules.invoices.voucherDate')"
                            fieldName="voucher_date" fieldRequired="true" :fieldPlaceholder="__('modules.invoices.voucherDate')" :fieldValue="$receiptVoucher->voucher_date->format(company()->date_format)"/>
                    </div>
                    {{-- End: Invoice Date --}}

                    {{-- Start: Driver ID --}}
                    <div class="col-6">
                        <x-forms.select2-ajax  fieldId="driver_id" fieldName="driver_id"
                            :fieldLabel="__('app.received_from_driver')" :route="route('get.driver-ajax')"
                            :placeholder="__('placeholders.searchForDrivers')" fieldRequired="true" :isEdit="true" :idToSelect="$receiptVoucher->driver_id">
                        </x-forms.select2-ajax>
                    </div>
                    {{-- End: Driver ID --}}

                    {{-- Start: Total Amount --}}
                    <div class="col-4">
                        <x-forms.number fieldName="total_amount" fieldId="total_amount"
                        fieldLabel="{{ __('modules.payments.totalAmount') }}" fieldRequired="true" :fieldPlaceholder="__('modules.payments.totalAmount')" :fieldValue="$receiptVoucher->total_amount"/>
                    </div>
                    {{-- End: Total Amount --}}

                </div>
                <hr class="m-0 border-top-grey">
                <div class="row  p-20">


                    {{-- Start: City --}}
                    <div class="col-md-2">
                        <x-forms.text fieldId="city" :fieldLabel="__('modules.stripeCustomerAddress.city')"
                            fieldName="city" fieldReadOnly="true"
                            :fieldPlaceholder="__('modules.stripeCustomerAddress.city')" :fieldValue="$receiptVoucher->driver->branch->name">
                        </x-forms.text>
                    </div>
                    {{-- End: City --}}


                    {{-- Start: Driver Name --}}
                    <div class="col-md-2">
                        <x-forms.text fieldId="driver_name" :fieldLabel="__('modules.drivers.driverName')"
                            fieldName="driver_name" fieldReadOnly="true"
                            :fieldPlaceholder="__('modules.drivers.driverName')" :fieldValue="$receiptVoucher->driver->name">
                        </x-forms.text>
                    </div>
                    {{-- End: Driver Name --}}

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
                        fieldLabel="{{ __('modules.businesses.other_business') }}" :fieldPlaceholder="__('modules.businesses.other_business')" :fieldValue="$receiptVoucher->other_business"/>
                    </div>
                    {{-- End: Other Business --}}

                    {{-- Start: Start Date --}}
                    <div class="col-md-2">
                        <x-forms.datepicker fieldId="start_date" :fieldLabel="__('modules.invoices.startDate')"
                            fieldName="start_date" fieldRequired="true" :fieldPlaceholder="__('placeholders.date')" :fieldValue="$receiptVoucher->start_date->format(company()->date_format)"/>
                    </div>
                    {{-- End: Start Date --}}

                    {{-- Start: End Date --}}
                    <div class="col-md-2">
                        <x-forms.datepicker fieldId="end_date" :fieldLabel="__('modules.invoices.endDate')"
                            fieldName="end_date" fieldRequired="true" :fieldPlaceholder="__('placeholders.date')" :fieldValue="$receiptVoucher->end_date->format(company()->date_format)"/>
                    </div>
                    {{-- End: End Date --}}

                    {{-- Start: Wallet Amount--}}
                    <div class="col-md-4">
                        <x-forms.number fieldName="wallet_amount" fieldId="wallet_amount"
                        fieldLabel="{{ __('modules.invoices.walletAmount') }}" fieldRequired="true" :fieldPlaceholder="__('modules.invoices.walletAmount')" :fieldValue="$receiptVoucher->wallet_amount"/>
                    </div>
                    {{-- End: Wallet Amount--}}

                    {{-- Start: Status --}}
                    <div class="col-md-2">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')"
                            fieldName="status"
                            :fieldPlaceholder="__('app.status')">
                            <option value="received" @selected($receiptVoucher->status == 'received')>received</option>
                            <option value="signed" @selected($receiptVoucher->status == 'signed')>signed</option>
                            <option value="deposited" @selected($receiptVoucher->status == 'deposited')>deposited</option>
                            <option value="approved" @selected($receiptVoucher->status == 'approved')>approved</option>
                        </x-forms.select>
                    </div>
                    {{-- End: Status --}}
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-driver-form" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel class="border-0 " href="{{ route('receipt-voucher.index') }}">@lang('app.cancel')
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
            const business_id = $(this).val();
            handleBusinessChange(business_id);
        });

        $('#driver_id').on('change', function(){
            const driver_id = $(this).val();
            handleDriverChange(driver_id);
        });
        const driver = @json($receiptVoucher->driver);

        if (driver) {
            const option = new Option(driver.iqaama_number, driver.id, true, true);
            $('#driver_id').append(option).val(driver.id).trigger('change');
            handleDriverChange(driver.id);
        }

        const business = @json($receiptVoucher->business);
        if (business) {
            handleBusinessChange(business.id);
        }

        function handleBusinessChange(business_id){
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

        }

        function handleDriverChange(driver_id){

            if(driver_id){
                var url = "{{route('invoices.get-driver', ':id')}}",
                url = (driver_id) ? url.replace(':id', driver_id) : url.replace(':id', null);
                $.easyAjax({
                    url : url,
                    type : "GET",
                    container: '#saveInvoiceForm',
                    blockUI: true,
                    success: function (response) {
                        $('#driver_name').val(response?.name);
                        $('#city').val(response?.branch?.name);

                        if(business){
                            let options = '<option>--</option>';
                            response?.businesses?.forEach(currentbusiness => {
                                options += `<option value="${currentbusiness.id}" selected="${currentbusiness.id == business?.id}">${currentbusiness.name}</option>`;
                            });
                            $('#business_id').html(options).selectpicker('refresh');
                        }
                    }
                });
            }else{
                $('#driver_name').val('');
                $('#city').val('');
                $('#business_id').html('').selectpicker('refresh');

            }


        }

        datepicker('#voucher_date', {
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
            const url = "{{ route('receipt-voucher.update', $receiptVoucher->id) }}";
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
                    console.log(response)
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
                            window.location = "{{route('receipt-voucher.index')}}";



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
</script>

