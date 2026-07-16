<div class="row">
    <div class="col-sm-12">
        <div class="add-client bg-white rounded">
            <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                @lang('app.view')</h4>
            <div class="row p-20">
                <div class="col-md-6">
                    <p><strong>@lang('app.employee'):</strong> {{ $account->employee->name ?? '--' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>@lang('app.bankName'):</strong> {{ $account->bank_name }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>@lang('app.ibanNumber'):</strong> {{ $account->iban_number }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>@lang('app.accountNumber'):</strong> {{ $account->account_number ?? '--' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>@lang('app.swiftCode'):</strong> {{ $account->swift_code ?? '--' }}</p>
                </div>
                <div class="col-md-6">
                    <p><strong>@lang('app.mainAccount'):</strong> {{ $account->is_main_account ? __('app.yes') : __('app.no') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
