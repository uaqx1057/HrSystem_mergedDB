<style>
    .mt{
        margin-top: -4px;
    }
</style>

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-air-ticket-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.airTicket.addTitle')</h4>
                <div class="row p-20">

                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="" fieldId="employee"
                            :fieldLabel="__('app.employee')" fieldRequired="true">
                        </x-forms.label>
                        @if (!in_array('admin', $assignRole) && count($employees) > 0)
                        <input type="hidden" value="{{ user()->id }}" name="employee">
                        @endif
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="employee"
                                id="employee" data-live-search="true" @if (!in_array('admin', $assignRole)) disabled @endif>
                                <option value="">--</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}" @if ($employee->id == user()->id) selected @endif>{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>


                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker
                            fieldId="date" fieldRequired="true"
                            :fieldLabel="__('modules.airTicket.date')"
                            fieldName="date"
                            :fieldPlaceholder="__('placeholders.date')"
                            minlength="10"
                            maxlength="10"
                        />
                    </div>

                    @if (in_array('admin', $assignRole))
                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status" search="true">
                            <option value="pending">@lang('app.pending')</option>
                            <option value="approved">@lang('app.approved')</option>
                        </x-forms.select>
                    </div>
                    @endif

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-air-ticket-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('air-tickets.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>

    $( document ).ready(function() {
        $(".select-picker").selectpicker();

        datepicker('#date', {
            position: 'bl',
            ...datepickerConfig
        });

    });

    $('#save-air-ticket-form').click(function() {
        var url = "{{ route('air-tickets.store') }}";
        $.easyAjax({
            url: url,
            container: '#save-air-ticket-data-form',
            type: "POST",
            data: $('#save-air-ticket-data-form').serialize(),
            disableButton: true,
            blockUI: true,
            buttonSelector: "#save-air-ticket-form",
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
