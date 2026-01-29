@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="update-driver-data-form" method="PUT">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.branches.branchDetails')
                </h4>

                <div class="row  p-20">
                    <div class="col-md-4">
                        <x-forms.text fieldId="name" :fieldLabel="__('modules.branches.branchName')"
                            fieldName="name" fieldRequired="true"
                            :fieldPlaceholder="__('modules.branches.branchName')" :fieldValue="$branch->name">
                        </x-forms.text>
                        {{-- <x-forms.select
                            fieldId="name"
                            :fieldLabel="__('modules.branches.branchName')"
                            fieldName="name">
                            @foreach (\App\Enums\BranchName::cases() as $branchName)
                                <option value="{{ $branchName }}" @selected($branch->name == $branchName)>{{ $branchName->label() }}</option>
                            @endforeach
                        </x-forms.select> --}}
                    </div>


                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="registration_date" :fieldLabel="__('modules.branches.registrationDate')" fieldName="registration_date"
                            fieldRequired="true" :fieldPlaceholder="__('modules.branches.registrationDate')"
                            :fieldValue="$branch->registration_date->format(company()->date_format)" />
                    </div>

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.branches.drivers')
                </h4>

                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.select2-ajax  fieldId="driver_ids" fieldName="driver_ids[]"
                            :fieldLabel="__('modules.drivers.driver')" :route="route('get.driver-ajax')"
                            :placeholder="__('placeholders.searchForDrivers')" multiple>
                        </x-forms.select2-ajax>
                    </div>
                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="update-branch-form" class="mr-3" icon="check">
                        @lang('app.update')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-branch-form"
                        icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel class="border-0 " data-dismiss="modal">@lang('app.cancel')
                    </x-forms.button-cancel>

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
        datepicker('#registration_date', {
            position: 'bl',
            maxDate: new Date(),
            ...datepickerConfig
        });

        $('#update-branch-form').click(function() {

            const url = "{{ route('branches.update', $branch->id) }}";
            var data = $('#update-driver-data-form').serialize();
            updateBranch(data, url, "#update-branch-form");

        });

        function updateBranch(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#update-driver-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = '{{ route('branches.index') }}';
                    }

                }
            });
        }

        var selectedDrivers = @json($branch->drivers()->get([ 'id', 'name' ]));

        selectedDrivers.forEach(function(driver){
            var newOption = new Option(driver.name, driver.id, true, true);
            $('#driver_ids').append(newOption).trigger('change');
        });

       $('#driver_ids').val(selectedDrivers.map(d => d.id)).trigger('change');

        init(RIGHT_MODAL);
    });

    $('.cropper').on('dropify.fileReady', function(e) {
        var inputId = $(this).find('input').attr('id');
        var url = "{{ route('cropper', ':element') }}";
        url = url.replace(':element', inputId);
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    })
</script>
