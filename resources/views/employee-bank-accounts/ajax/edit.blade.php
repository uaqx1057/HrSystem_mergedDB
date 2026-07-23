<div class="row">
    <div class="col-sm-12">
        <x-form id="update-employee-bank-account-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.edit')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        @if (in_array(user()->permission('edit_advance_salary'), ['all', 'added', 'both','branch']))
                            <x-forms.label class="" fieldId="employee_id" :fieldLabel="__('app.employee')" fieldRequired="true">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="employee_id" id="employee_id"
                                    data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" @if ($account->employee_id == $employee->id) selected @endif>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        @else
                            <input type="hidden" value="{{ user()->id }}" name="employee_id">
                            <x-forms.text fieldId="basic_salary" :fieldLabel="__('app.employee')" fieldName="basic_salary"
                                fieldRequired="true" :fieldPlaceholder="__('placeholders.basic_salary')" :fieldValue="user()->name" :fieldReadOnly="true">
                            </x-forms.text>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="bank_name" :fieldLabel="__('app.bankName')" fieldName="bank_name" fieldRequired="true" :fieldValue="$account->bank_name" :fieldPlaceholder="__('placeholders.name')"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="iban_number" :fieldLabel="__('app.ibanNumber')" fieldName="iban_number" fieldRequired="true" :fieldValue="$account->iban_number" :fieldPlaceholder="__('app.ibanNumber')"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="account_number" :fieldLabel="__('app.accountNumber')" fieldName="account_number" :fieldValue="$account->account_number" :fieldPlaceholder="__('app.accountNumber')"></x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="swift_code" :fieldLabel="__('app.swiftCode')" fieldName="swift_code" :fieldValue="$account->swift_code" :fieldPlaceholder="__('app.swiftCode')"></x-forms.text>
                    </div>
                    <div class="col-md-6 mt-4">
                        <x-forms.checkbox fieldId="is_main_account" fieldName="is_main_account" :fieldLabel="__('app.mainAccount')" :fieldValue="true" :checked="$account->is_main_account == true ? true : false"></x-forms.checkbox>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-employee-bank-account-form" class="mr-3" icon="check">@lang('app.update')</x-forms.button-primary>
                    <x-forms.button-cancel :link="route('employee-bank-accounts.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#update-employee-bank-account-form').click(function () {
            const url = '{{ route('employee-bank-accounts.update', $account->id) }}';
            $.easyAjax({
                url: url,
                container: '#update-employee-bank-account-data-form',
                type: 'POST',
                disableButton: true,
                blockUI: true,
                buttonSelector: '#update-employee-bank-account-form',
                data: $('#update-employee-bank-account-data-form').serialize() + '&_method=PUT',
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
