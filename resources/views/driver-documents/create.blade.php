@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center form-heading-background">
            <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize">
                Add Driver Document
            </h4>

        </div>

        <div class="bg-white rounded p-4">
            <form action="{{ route('driver-documents.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    {{-- Driver --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Driver <sup class="text-danger">*</sup></label>
                            <select name="driver_id" class="form-control select-picker" data-live-search="true" required>
                                <option value="">-- Select Driver --</option>

                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}">
                                        {{ $driver->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Document Type --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Document Type <sup class="text-danger">*</sup></label>

                            <select name="document_type" class="form-control select-picker" required>
                                <option value="">-- Select Type --</option>
                                <option value="iqama">Iqama</option>
                                <option value="passport">Passport</option>
                                <option value="visa">Visa</option>
                                <option value="license">License</option>
                                <option value="medical">Medical</option>
                                <option value="contract">Contract</option>
                                <option value="mobile">Mobile</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <x-forms.file fieldLabel="Upload Document" fieldName="upload_document" fieldId="upload_document" allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" :popover="__('messages.fileFormat.multipleImageFile')" />
                    </div>

                    {{-- Expire Date --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Expire At</label>

                            <input type="date" name="expires_at" class="form-control height-35">
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Note</label>
                            <textarea name="notes" rows="4" class="form-control" placeholder="Enter note"></textarea>
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-start mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-1"></i>
                        Save Document
                    </button>

                </div>

            </form>
        </div>
    </div>
@endsection
