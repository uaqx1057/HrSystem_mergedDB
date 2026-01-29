@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row mt-4">
    <div class="col-sm-12">
        <x-form id="update-driver-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.drivers.banking')
                </h4>

                <div class="row  p-20">
                    <div class="col-md-4">
                        <x-forms.text fieldId="stc_pay" :fieldLabel="__('modules.drivers.stcPay')"
                            fieldName="stc_pay" :fieldPlaceholder="__('modules.drivers.stcPay')" :fieldValue="$driver->stc_pay">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="bank_name" :fieldLabel="__('modules.drivers.bankName')"
                            fieldName="bank_name" :fieldPlaceholder="__('modules.drivers.bankName')" :fieldValue="$driver->bank_name">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="iban" :fieldLabel="__('modules.drivers.iban')"
                            fieldName="iban" :fieldPlaceholder="__('modules.drivers.iban')" :fieldValue="$driver->iban">
                        </x-forms.text>
                    </div>
                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="update-driver-form" class="mr-3" icon="check">
                        @lang('app.update')
                    </x-forms.button-primary>
                    
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
<script>
    $(document).ready(function() {
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
                data: {...data, '_method': 'PUT', '_token': "{{ csrf_token() }}" },
                success: function(response) {
                    if (response.status == 'success') {
                            window.location.reload();
                    }
                }
            });
        }
    });
</script>
