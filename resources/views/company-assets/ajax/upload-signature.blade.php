<div class="row">
    <div class="col-sm-12">
        <form action="{{ route('company-assets.store-signature', $asset->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    Upload Signature</h4>
                <div class="row p-20">

                    {{-- <div class="col-md-12">
                        <label class="f-14 f-w-500">Signature</label>
                        <input type="file" class="form-control" name="signature" id="signature" accept=".png,.jpg,.jpeg,.pdf">
                        <!-- This block shows the error message -->
                        @error('signature')
                            <div class="invalid-feedback d-block mt-1" style="color: red">
                                {{ $message }}
                            </div>
                        @enderror
                    </div> --}}

                    <div class="col-lg-12">
                        <x-forms.file :fieldLabel="__('app.signature')" fieldName="signature" fieldId="signature" allowedFileExtensions="pdf png jpg jpeg svg" :popover="__('messages.fileFormat.multipleImageFile')" />
                        @error('signature')
                            <div class="invalid-feedback d-block mt-1" style="color: red">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>

                <div class="pl-3 pb-2">
                    <input type="submit" class="btn btn-primary rounded" value="Save">
                    <x-forms.button-cancel :link="route('company-assets.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </div>

            </div>
        </form>

    </div>
</div>
