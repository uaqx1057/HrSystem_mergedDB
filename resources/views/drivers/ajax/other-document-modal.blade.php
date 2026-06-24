
<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="modal-header">
    <h5 class="modal-title">@lang('app.addOtherDocument')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
</div>
<div class="modal-body">
    <div class="portlet-body">
        <x-form id="save-other-document-data-form" method="PUT" class="ajax-form">
            <div class="row">
                @if ($driver_other)
                    <div class="col-lg-12">
                        <label for="">Current Document: </label>
                        <br>
                        <a href="{{ route('driver-documents.preview', $driver_other->id) }}" target="_blank"
                                            rel="noopener noreferrer">
                                            {{ $driver_other->original_name }}
                                        </a>
                    </div>
                @endif
                <div class="col-lg-12">
                    <x-forms.file allowedFileExtensions="png jpg jpeg svg pdf doc docx" class="mr-0 mr-lg-2 mr-md-2"
                        :fieldLabel="__('modules.drivers.otherDocument')" fieldName="other_document"
                        fieldId="file">
                    </x-forms.file>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label class="f-14 f-w-500">Note</label>
                        <textarea name="other_notes" rows="3" class="form-control" placeholder="Enter note">{{ $driver_other ? $driver_other->notes : '' }}</textarea>
                    </div>
                </div>

            </div>
        </x-form>
    </div>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-other-document-form" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>

    $('#save-other-document-form').click(function(){
        let hasExistingFile = {{ $driver_other ? 'true' : 'false' }};
        if($('#file').val().trim() === '' && !hasExistingFile){
            Swal.fire({
                icon: 'error',
                text: 'other document is required.',
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
                    container: '#save-other-document-data-form',
                    type: "POST",
                    disableButton: true,
                    blockUI: true,
                    buttonSelector: 'save-other-document-form',
                    file: true,
                    data: $('#save-other-document-data-form').serialize(),
                    success: function (response) {
                    if (response.status === 'success') {
                        window.location.reload();
                    }
            }
        });
    });

    init(MODAL_LG);
</script>
