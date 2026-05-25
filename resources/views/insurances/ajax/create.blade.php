<style>
    .mt{
        margin-top: -4px;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-insurance-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.insurance.addTitle')</h4>
                <div class="row p-20">

                    <div class="col-lg-4 col-md-6 employee-select">
                        <x-forms.label class="" fieldId="employee"
                            :fieldLabel="__('app.employee')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="employee"
                                id="employee" data-live-search="true">
                                <option value="">--</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker
                            fieldId="issue_date" fieldRequired="true"
                            :fieldLabel="__('modules.insurance.issue_date')"
                            fieldName="issue_date"
                            :fieldPlaceholder="__('placeholders.issue_date')"
                            minlength="10"
                            maxlength="10"
                        />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker
                            fieldId="expiry_date" fieldRequired="true"
                            :fieldLabel="__('modules.insurance.expiry_date')"
                            fieldName="expiry_date"
                            :fieldPlaceholder="__('placeholders.expiry_date')"
                            minlength="10"
                            maxlength="10"
                        />
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="company_name" fieldRequired="true" :fieldLabel="__('app.company_name')" fieldName="company_name"
                             :fieldPlaceholder="__('placeholders.company')">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="policy_no" fieldRequired="true" :fieldLabel="__('app.policy_no')" fieldName="policy_no"
                             :fieldPlaceholder="__('placeholders.policy_no')">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6">
                       <x-forms.text fieldId="class" fieldRequired="true" :fieldLabel="__('app.class')" fieldName="class"
                             :fieldPlaceholder="__('placeholders.class')">
                        </x-forms.text>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="status"
                            :fieldLabel="__('app.status')" >
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="status"
                                id="status" data-live-search="true">
                                <option value="active">Active</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </x-forms.input-group>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-insurance-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('insurance.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>

    $( document ).ready(function() {
        $(".select-picker").selectpicker();

        datepicker('#issue_date', {
            position: 'bl',
            ...datepickerConfig
        });

        datepicker('#expiry_date', {
            position: 'bl',
            ...datepickerConfig
        });

        // $('#type').change(function(){
        //     var value = $(this).val();
        //     if(value == 'employee') {
        //         $('.employee-select').removeClass('d-none');
        //         $('.driver-select').addClass('d-none');
        //     } else if(value == 'driver') {
        //         $('.driver-select').removeClass('d-none');
        //         $('.employee-select').addClass('d-none');
        //     } else {
        //         $('.driver-select').addClass('d-none');
        //         $('.employee-select').addClass('d-none');
        //     }
        // });

    });

    $('#save-insurance-form').click(function() {
        var url = "{{ route('insurance.store') }}";
        $.easyAjax({
            url: url,
            container: '#save-insurance-data-form',
            type: "POST",
            data: $('#save-insurance-data-form').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-insurance-form",
            success: function(response) {
                if (response.status == 'success') {
                    $('#employee_department').html(response.data);
                    $('#employee_department').selectpicker('refresh');
                    $(MODAL_LG).modal('hide');
                    window.location.href = response.redirectUrl
                }
            }
        })
    });

</script>
