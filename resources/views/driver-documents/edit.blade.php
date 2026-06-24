@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center form-heading-background">
            <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize">
                Edit Driver Document
            </h4>
        </div>

        <div class="bg-white rounded p-4">
            <form action="{{ route('driver-documents.update', $document->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row">

                    {{-- Driver --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Driver <sup class="text-danger">*</sup></label>
                            <select name="driver_id" class="form-control select-picker" data-live-search="true" required>
                                <option value="">-- Select Driver --</option>

                                @foreach ($drivers as $driver)
                                    <option value="{{ $driver->id }}"
                                        {{ (int) $document->driver_id === $driver->id ? 'selected' : '' }}>
                                        {{ $driver->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('driver_id')
                                <span class="text-danger f-12">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Document Type --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Document Type <sup class="text-danger">*</sup></label>

                            <select name="document_type" class="form-control select-picker" required>
                                <option value="">-- Select Type --</option>
                                @foreach (['iqama' => 'Iqama', 'passport' => 'Passport', 'visa' => 'Visa', 'license' => 'License', 'medical' => 'Medical', 'contract' => 'Contract','mobile' => 'Mobile', 'other' => 'Other'] as $value => $label)
                                    <option value="{{ $value }}"
                                        {{ $document->document_type === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('document_type')
                                <span class="text-danger f-12">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    @php
                        $file_url =  route('driver-documents.preview', $document->id);
                    @endphp

                    <div class="col-md-6">
                        <a href="{{ route('driver-documents.preview', $document->id) }}" target="_blank"
                            rel="noopener noreferrer">
                            Currenct Document
                        </a>
                        <x-forms.file fieldLabel="Upload Document" :fieldValue="($document->original_name ? $file_url : '')" fieldName="upload_document" fieldId="upload_document" allowedFileExtensions="txt pdf doc xls xlsx docx rtf png jpg jpeg svg" :popover="__('messages.fileFormat.multipleImageFile')"  />
                    </div>

                    {{-- Expire Date --}}
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Expire At</label>
                            <input type="date" name="expires_at" class="form-control height-35"
                                value="{{ optional($document->expires_at)->format('Y-m-d') }}">
                            @error('expires_at')
                                <span class="text-danger f-12">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Note --}}
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Note</label>
                            <textarea name="notes" rows="4" class="form-control" placeholder="Enter note">{{ $document->notes }}</textarea>
                            @error('notes')
                                <span class="text-danger f-12">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                </div>

                <div class="d-flex justify-content-start mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-1"></i>
                        Update Document
                    </button>
                    <a href="{{ route('driver-documents.index') }}" class="btn btn-secondary ml-2">
                        Cancel
                    </a>
                </div>

            </form>
        </div>
    </div>
@endsection
