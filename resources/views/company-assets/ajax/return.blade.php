<div class="row">
    <div class="col-sm-12">
        <form action="{{ route('company-assets.return.store', $asset->id) }}" method="POST"
            enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $assignment->id }}">
            <input type="hidden" name="employee_id" value="{{ $employeeId ?? $assignment->employee_id ?? '' }}">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.returnCompanyAsset')</h4>
                <div class="row p-20">
                    <div class="col-lg-12 text-right">
                        <a href="{{ !empty($employeeId) ? route('employees.show', [$employeeId, 'tab' => 'company-assets']) : route('company-assets.show', $asset->id) }}" class="btn btn-sm btn-primary">Back</a>
                    </div>

                    <div class="col-lg-12">
                        <div class="row">
                            <div class="col-md-6">
                                <x-forms.label class="" fieldId="employee" :fieldLabel="__('app.employee')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="employee" id="employee"
                                        data-live-search="true" disabled>
                                        <option value="">--</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}" {{ $employee->id == $assignment->employee_id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <input type="hidden" name="qty" value="{{ $assignment->qty }}">

                            <div class="col-md-6">
                                <x-forms.label class="" fieldId="serial_no"
                                    :fieldLabel="__('app.serialNo')" fieldRequired="true">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="serial_no"
                                        id="serial_no" data-live-search="true" disabled>
                                        <option value="">--</option>
                                        @foreach ($serials as $serial)
                                            <option value="{{ $serial->serial_no }}" {{ ($assignment->serial_no == $serial->serial_no) ? 'selected' : '' }}>{{ $serial->serial_no }}</option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>
                        </div>

                    </div>

                    <div class="col-lg-12 mt-3">
                        <a href="{{ route('company-assets.return-pdf', $assignment->id) }}" target="_blank"
                            rel="noopener noreferrer">
                            Download Return Form
                        </a>
                    </div>

                    <div class="col-lg-12">
                        <x-forms.file :fieldLabel="__('app.returnSignature')" fieldName="return_document" fieldId="return_document"
                            allowedFileExtensions="pdf png jpg jpeg svg" />
                        @error('return_document')
                            <div class="invalid-feedback d-block mt-1" style="color: red">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>

                <div class="pl-3 pb-2">
                    <input type="submit" class="btn btn-primary rounded" value="Save">
                    <x-forms.button-cancel :link="!empty($employeeId) ? route('employees.show', [$employeeId, 'tab' => 'company-assets']) : route('company-assets.show', $asset->id)" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </div>

            </div>
        </form>

    </div>
</div>
