
<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="modal-header">
    <h5 class="modal-title">@lang('app.addIqama')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="save-iqama-data-form" method="PUT" class="ajax-form">
            <div class="row">
                <div class="col-lg-3">
                    <x-forms.datepicker fieldId="iqaama_expiry_date" :fieldValue="$driver->iqaama_expiry_date"
                                        :fieldLabel="__('modules.drivers.expiryDate')" fieldName="iqaama_expiry_date"
                                        :fieldValue="$driver->iqaama_expiry_date ? $driver->iqaama_expiry_date->format(company()->date_format) : \Carbon\Carbon::now(company()->timezone)->format(company()->date_format)"
                                        :fieldPlaceholder="__('placeholders.date')"/>
                </div>
                @if ($driver_iqama)
                    <div class="col-lg-12">
                        <label for="">Current Document: </label>
                        <br>
                        <a href="{{ route('driver-documents.preview', $driver_iqama->id) }}" target="_blank"
                                            rel="noopener noreferrer">
                                            {{ $driver_iqama->original_name }}
                                        </a>
                    </div>
                @endif
                <div class="col-lg-12">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg pdf doc docx" class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('modules.drivers.iqama')" fieldName="iqama"
                        fieldId="file">
                    </x-forms.file>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label class="f-14 f-w-500">Note</label>
                        <textarea name="iqama_notes" rows="3" class="form-control" placeholder="Enter note">{{ $driver_iqama ? $driver_iqama->notes : '' }}</textarea>
                    </div>
                </div>



            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-iqama-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    datepicker('#iqaama_expiry_date', {
        position: 'bl',
        ...datepickerConfig
    });


    $('#save-iqama-form').click(function(){
        let hasExistingFile = {{ $driver_iqama ? 'true' : 'false' }};
        if($('#file').val().trim() === '' && !hasExistingFile){
            Swal.fire({
                icon: 'error',
                text: 'iqama is required.',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
            });
            return;
        }
        $.easyAjax({
                    url: "{{ route('drivers.update', $driver->id) }}",
                    container: '#save-iqama-data-form',
                    type: "POST",
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: 'save-iqama-form',
                    file: true,
                    data: $('#save-iqama-data-form').serialize(),
                    success: function (response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    }
            }
        });
    });

    init(MODAL_LG);
</script>
