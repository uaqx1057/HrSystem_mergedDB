<div class="row">
    <div class="col-sm-12">
        <x-form id="save-employee-bank-account-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.add')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.select fieldId="employee_id" :fieldLabel="__('app.employee')" fieldName="employee_id" fieldRequired="true" search="true">
                            <option value="">@lang('app.select')...</option>
                            @foreach ($employees as $employee)
                                <x-user-option :user="$employee" />
                            @endforeach
                        </x-forms.select>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="bank_name" :fieldLabel="__('app.bankName')" fieldName="bank_name" fieldRequired="true" :fieldPlaceholder="__('placeholders.name')"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="iban_number" :fieldLabel="__('app.ibanNumber')" fieldName="iban_number" fieldRequired="true" :fieldPlaceholder="__('app.ibanNumber')"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="account_number" :fieldLabel="__('app.accountNumber')" fieldName="account_number" :fieldPlaceholder="__('app.accountNumber')"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="swift_code" :fieldLabel="__('app.swiftCode')" fieldName="swift_code" :fieldPlaceholder="__('app.swiftCode')"></x-forms.text>
                    </div>
                    <div class="col-md-6 mt-4">
                        <x-forms.checkbox fieldId="is_main_account" fieldName="is_main_account" :fieldLabel="__('app.mainAccount')" :fieldValue="true"></x-forms.checkbox>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-employee-bank-account-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('employee-bank-accounts.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#save-employee-bank-account-form').click(function () {
            const url = '{{ route('employee-bank-accounts.store') }}';
            $.easyAjax({
                url: url,
                container: '#save-employee-bank-account-data-form',
                type: 'POST',
                disableButton: true,
                blockUI: true,
                buttonSelector: '#save-employee-bank-account-form',
                data: $('#save-employee-bank-account-data-form').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
