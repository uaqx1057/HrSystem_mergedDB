@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-driver-business-data-form" method="PUT">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.businesses.businessDetails')
                </h4>

                <div class="row  p-20">
                    <div class="col-md-6">
                        <x-forms.select fieldId="business_id" fieldName="business_id" :fieldLabel="__('modules.businesses.business')" readonly>
                            <option
                                selected
                                value="{{ $business->id }}">
                                {{ $business->name }}
                            </option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-6">
                        <x-forms.text fieldId="platform_id" :fieldLabel="__('modules.businesses.platformID')"
                            fieldName="platform_id" fieldRequired="true"
                            :fieldPlaceholder="__('modules.businesses.platformID')" :fieldValue="$business->pivot->platform_id">
                        </x-forms.text>
                    </div>
                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="edit-driver-business-form" class="mr-3" icon="check">
                        @lang('app.update')
                    </x-forms.button-primary>
                    <x-forms.button-cancel class="border-0 " data-dismiss="modal">@lang('app.cancel')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script src="{{ asset('vendor/jquery/tagify.min.js') }}"></script>
<script>
    $(document).ready(function() {

        $('#edit-more-driver-business-form').click(function() {

            $('#add_more').val(true);

            const url = "{{ route('drivers.businesses.update', [ $driver->id, $business->id ]) }}";
            var data = $('#edit-driver-business-data-form').serialize();
            updateBusinessDriver(data, url, "#edit-more-driver-business-form");


        });

        $('#edit-driver-business-form').click(function() {
            const url = "{{ route('drivers.businesses.update', [ $driver->id, $business->id ]) }}";
            var data = $('#edit-driver-business-data-form').serialize();
            updateBusinessDriver(data, url, "#edit-driver-business-form");

        });

        function updateBusinessDriver(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#edit-driver-business-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        }

        $('#country').change(function(){
            var phonecode = $(this).find(':selected').data('phonecode');
            $('#country_phonecode').val(phonecode);
            $('.select-picker').selectpicker('refresh');
        });


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
