@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row mt-4">
    <div class="col-sm-12">
        <x-form id="update-driver-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.drivers.employment')
                </h4>

                <div class="row  p-20">
                    <div class="col-md-4">
                        <x-forms.text fieldId="department" :fieldLabel="__('modules.drivers.department')"
                            fieldName="department" :fieldPlaceholder="__('modules.drivers.department')" :fieldValue="$driver->department">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="job_position" :fieldLabel="__('modules.drivers.jobPosition')"
                            fieldName="job_position" :fieldPlaceholder="__('modules.drivers.jobPosition')" :fieldValue="$driver->job_position">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="contract_period_in_months" :fieldLabel="__('modules.drivers.contractPeriod')"
                            fieldName="contract_period_in_months" :fieldPlaceholder="__('modules.drivers.contractPeriodInfo')" :fieldValue="$driver->contract_period_in_months">
                        </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="joining_date" :fieldLabel="__('modules.drivers.joiningDate')"
                            fieldName="joining_date" :fieldPlaceholder="__('modules.drivers.joiningDate')" 
                            :fieldValue="$driver->joining_date ? $driver->joining_date->timezone(company()->timezone)->format(company()->date_format) : null" />
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="basic_salary" :fieldLabel="__('modules.drivers.basicSalary')"
                            fieldName="basic_salary" :fieldPlaceholder="__('modules.drivers.basicSalary')" :fieldValue="$driver->basic_salary">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="housing_allowance" :fieldLabel="__('modules.drivers.housingAllowance')"
                            fieldName="housing_allowance" :fieldPlaceholder="__('modules.drivers.housingAllowance')" :fieldValue="$driver->housing_allowance">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="transportation_allowance" :fieldLabel="__('modules.drivers.transportationAllowance')"
                            fieldName="transportation_allowance" :fieldPlaceholder="__('modules.drivers.transportationAllowance')" :fieldValue="$driver->transportation_allowance">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="performance_allowance" :fieldLabel="__('modules.drivers.performanceAllowance')"
                            fieldName="performance_allowance" :fieldPlaceholder="__('modules.drivers.performanceAllowance')" :fieldValue="$driver->performance_allowance">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="other_allowance" :fieldLabel="__('modules.drivers.otherAllowance')"
                            fieldName="other_allowance" :fieldPlaceholder="__('modules.drivers.otherAllowance')" :fieldValue="$driver->other_allowance">
                        </x-forms.text>
                    </div>

                    <div class="col-md-4">
                        <x-forms.text fieldId="total_salary" :fieldLabel="__('modules.drivers.totalSalary')"
                            fieldName="total_salary" :fieldPlaceholder="__('modules.drivers.totalSalary')" :fieldValue="$driver->total_salary">
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
@if (function_exists('sms_setting') && sms_setting()->telegram_status)
    <script src="{{ asset('vendor/jquery/clipboard.min.js') }}"></script>
@endif
<script>
    $(document).ready(function() {
        datepicker('#joining_date', {
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
