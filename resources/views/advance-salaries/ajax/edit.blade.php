<style>
    .mt{
        margin-top: -4px;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-advance-salary-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.advanceSalary.editTitle')</h4>
                <div class="row p-20">

                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="" fieldId="employee"
                            :fieldLabel="__('app.employee')" fieldRequired="true">
                        </x-forms.label>
                        @if (count($assignRole) < 2)
                        <input type="hidden" value="{{ $advanceSalary->employee_id }}" name="employee">
                        @endif
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="employee"
                                id="employee" data-live-search="true" @if (count($assignRole) < 2) disabled @endif>
                                <option value="">--</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        @if ($advanceSalary->employee_id == $employee->id) selected @endif>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text fieldId="advance_salary" :fieldLabel="__('modules.advanceSalary.amount')" fieldName="advance_salary"
                                fieldRequired="true" :fieldPlaceholder="__('placeholders.advance_salary')" :fieldValue="$advanceSalary->advance_salary">
                            </x-forms.text>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker
                            fieldId="date" fieldRequired="true"
                            :fieldLabel="__('modules.advanceSalary.date')"
                            fieldName="date"
                            :fieldPlaceholder="__('placeholders.date')"
                            minlength="10"
                            maxlength="10"
                        />
                    </div>

                    @if (count($assignRole) > 1)
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status"
                            search="true">
                            <option @if ($advanceSalary->status == 'approved') selected @endif value="approved">@lang('app.approved')</option>
                            <option @if ($advanceSalary->status == 'pending') selected @endif value="pending">@lang('app.pending')</option>
                            <option @if ($advanceSalary->status == 'rejected') selected @endif value="rejected">@lang('app.rejected')</option>
                        </x-forms.select>
                    </div>
                    @endif

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-advance-salary-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('advance-salaries.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>

    $( document ).ready(function() {
        $(".select-picker").selectpicker();

        @php $date = $advanceSalary->date; @endphp
        datepicker('#date', {
            position: 'bl',
            @if ($date)
                dateSelected: new Date("{{ str_replace('-', '/', $date) }}"),
            @endif
            ...datepickerConfig
        });

    });

    $('#save-advance-salary-form').click(function() {
        const url = "{{ route('advance-salaries.update', $advanceSalary->id) }}";
        $.easyAjax({
            url: url,
            container: '#save-advance-salary-data-form',
            type: "PUT",
            data: $('#save-advance-salary-data-form').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-advance-salary-form",
            success: function(response) {
                if (response.status == 'success') {
                    window.location.href = response.redirectUrl
                }
            }
        })
    });

</script>
