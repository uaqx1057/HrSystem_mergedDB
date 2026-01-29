
<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="modal-header">
    <h5 class="modal-title">@lang('app.addLicense')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="save-license-data-form" method="PUT" class="ajax-form">
            <div class="row">
                <div class="col-lg-3">
                    <x-forms.datepicker fieldId="license_expiry_date" :fieldValue="$driver->license_expiry_date"
                                        :fieldLabel="__('modules.drivers.expiryDate')" fieldName="license_expiry_date"
                                        :fieldValue="$driver->license_expiry_date ? $driver->license_expiry_date->format(company()->date_format) : \Carbon\Carbon::now(company()->timezone)->format(company()->date_format)"
                                        :fieldPlaceholder="__('placeholders.date')"/>
                </div>

                <div class="col-lg-12">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg pdf doc docx" class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('modules.drivers.license')" fieldName="license"
                        :fieldValue="$driver->license ? $driver->license_url : '' "
                        fieldId="file">
                    </x-forms.file>
                </div>
            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-license-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    datepicker('#license_expiry_date', {
        position: 'bl',
        ...datepickerConfig
    });

    $('#save-license-form').click(function(){
        $.easyAjax({
                url: "{{ route('drivers.update', $driver->id) }}",
                container: '#save-license-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: 'save-license-form',
                file: true,
                data: $('#save-license-data-form').serialize(),
                success: function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        });
    });

    init(MODAL_LG);
</script>
