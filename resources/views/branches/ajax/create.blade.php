@php
    $addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-branch-data-form">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.branches.branchDetails')
                </h4>

                <div class="row  p-20">
                    <div class="col-md-4">
                        <x-forms.text fieldId="name" :fieldLabel="__('modules.branches.branchName')"
                            fieldName="name" fieldRequired="true"
                            :fieldPlaceholder="__('modules.branches.branchName')">
                        </x-forms.text>
                        {{-- <x-forms.select fieldId="name" :fieldLabel="__('modules.branches.branchName')" fieldName="name">
                            @foreach (\App\Enums\BranchName::cases() as $branchName)
                                <option value="{{ $branchName }}">{{ $branchName->label() }}</option>
                            @endforeach
                        </x-forms.select> --}}
                    </div>


                    <div class="col-lg-4 col-md-6">
                        <x-forms.datepicker fieldId="registration_date" :fieldLabel="__('modules.branches.registrationDate')" fieldName="registration_date"
                            fieldRequired="true" :fieldPlaceholder="__('modules.branches.registrationDate')" />
                    </div>

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.branches.drivers')
                </h4>

                <div class="row p-20">
                    <div class="col-md-4">
                        <x-forms.select2-ajax  fieldId="driver_id" fieldName="driver_ids[]"
                            :fieldLabel="__('modules.drivers.driver')" :route="route('get.driver-ajax')"
                            :placeholder="__('placeholders.searchForDrivers')" multiple></x-forms.select2-ajax>
                    </div>
                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-branch-form" class="mr-3" icon="check">
                        @lang('app.save')
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

        $('#save-more-branch-form').click(function() {

            $('#add_more').val(true);

            const url = "{{ route('branches.store') }}";
            var data = $('#save-branch-data-form').serialize();
            saveBranch(data, url, "#save-more-branch-form");


        });

        $('#save-branch-form').click(function() {

            const url = "{{ route('branches.store') }}";
            var data = $('#save-branch-data-form').serialize();
            saveBranch(data, url, "#save-branch-form");

        });

        function saveBranch(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#save-branch-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else if (response.add_more == true) {

                            var right_modal_content = $.trim($(RIGHT_MODAL_CONTENT).html());

                            if (right_modal_content.length) {

                                $(RIGHT_MODAL_CONTENT).html(response.html.html);
                                $('#add_more').val(false);
                            } else {

                                $('.content-wrapper').html(response.html.html);
                                init('.content-wrapper');
                                $('#add_more').val(false);
                            }

                        } else {

                            window.location.href = response.redirectUrl;

                        }

                        if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                            showTable();
                        }

                    }

                }
            });
        }


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
