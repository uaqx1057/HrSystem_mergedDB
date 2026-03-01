<style>
    .mt {
        margin-top: -4px;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-insurance-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.insurance.editTitle')</h4>
                <div class="row p-20">
                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="type"
                            :fieldLabel="__('app.type')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="type"
                                id="type" data-live-search="true">
                                <option value="">--</option>
                                <option @if ($insurance->employee_id) selected @endif value="employee">Employee</option>
                                <option @if ($insurance->driver_id) selected @endif value="driver">Driver</option>
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-lg-4 col-md-6 employee-select {{ !empty($insurance?->employee_id) ? '' : 'd-none' }}">
                        <x-forms.label class="my-3" fieldId="employee" :fieldLabel="__('app.employee')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="employee" id="employee"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        @if ($insurance->employee_id == $employee->id) selected @endif>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-lg-4 col-md-6 driver-select {{ !empty($insurance?->driver_id) ? '' : 'd-none' }}">
                        <x-forms.label class="my-3" fieldId="driver"
                            :fieldLabel="__('app.driver')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="driver"
                                id="driver" data-live-search="true">
                                <option value="">--</option>
                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}"
                                        @if ($insurance->driver_id == $driver->id) selected @endif>{{ $driver->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="issue_date"  fieldRequired="true" :fieldLabel="__('modules.insurance.issue_date')" fieldName="issue_date"
                            :fieldPlaceholder="__('placeholders.issue_date')" :fieldValue="optional($insurance->issue_date)->format(company()->date_format)" minlength="10" maxlength="10" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="expiry_date" fieldRequired="true" :fieldLabel="__('modules.insurance.expiry_date')" fieldName="expiry_date"
                            :fieldPlaceholder="__('placeholders.expiry_date')" :fieldValue="optional($insurance->expiry_date)->format(company()->date_format)" minlength="10" maxlength="10" />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="designation_name" fieldRequired="true" :fieldLabel="__('app.company_name')" fieldName="company_name"
                            :fieldPlaceholder="__('placeholders.company')" :fieldValue="$insurance->company">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="designation_name" fieldRequired="true" :fieldLabel="__('app.policy_no')" fieldName="policy_no"
                            :fieldPlaceholder="__('placeholders.policy_no')" :fieldValue="$insurance->policy_no">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="designation_name" fieldRequired="true" :fieldLabel="__('app.class')" fieldName="class" :fieldPlaceholder="__('placeholders.class')"
                            :fieldValue="$insurance->class">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="status"
                            :fieldLabel="__('app.status')" >
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="status"
                                id="status" data-live-search="true">
                                <option @if ($insurance->status == 'active') selected @endif value="active">Active</option>
                                <option @if ($insurance->status == 'cancelled') selected @endif value="cancelled">Cancelled</option>
                            </select>
                        </x-forms.input-group>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-insurance-form" class="mr-3" icon="check">@lang('app.update')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('insurance.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {
        $(".select-picker").selectpicker();

        @php $issueDate = $insurance->issue_date; @endphp
        datepicker('#issue_date', {
            position: 'bl',
            @if ($issueDate)
                dateSelected: new Date("{{ str_replace('-', '/', $issueDate) }}"),
            @endif
            ...datepickerConfig
        });

        @php $expiryDate = $insurance->expiry_date; @endphp
        datepicker('#expiry_date', {
            position: 'bl',
            @if ($expiryDate)
                dateSelected: new Date("{{ str_replace('-', '/', $expiryDate) }}"),
            @endif
            ...datepickerConfig
        });

        $('#type').change(function(){
            var value = $(this).val();
            if(value == 'employee') {
                $('.employee-select').removeClass('d-none');
                $('.driver-select').addClass('d-none');
            } else if(value == 'driver') {
                $('.driver-select').removeClass('d-none');
                $('.employee-select').addClass('d-none');
            } else {
                $('.driver-select').addClass('d-none');
                $('.employee-select').addClass('d-none');
            }
        });
    });

    $('#save-insurance-form').click(function() {

        const url = "{{ route('insurance.update', $insurance->id) }}";

        $.easyAjax({
            url: url,
            container: '#save-insurance-data-form',
            type: "PUT",
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-department-form",
            data: $('#save-insurance-data-form').serialize(),
            success: function(response) {
                window.location.href = response.redirectUrl;
            }
        });
    });

    init(RIGHT_MODAL);
</script>
