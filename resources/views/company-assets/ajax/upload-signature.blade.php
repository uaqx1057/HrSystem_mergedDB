@php
    $backUrl = !empty($employeeId)
        ? route('employees.show', [$employeeId, 'tab' => 'company-assets'])
        : route('company-assets.show', $asset->id);
@endphp

<div class="row">
    <div class="col-sm-12">
        <form action="{{ route('company-assets.store-signature', $asset->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $assignment->id }}">
            <input type="hidden" name="employee_id" value="{{ $employeeId ?? $assignment->employee_id ?? '' }}">

            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header form-heading-background border-bottom-grey d-flex justify-content-between align-items-center p-20">
                    <h4 class="mb-0 f-18 font-weight-normal text-capitalize">@lang('app.signature')</h4>
                    <a href="{{ $backUrl }}" class="btn btn-sm btn-secondary">
                        <i class="fa fa-arrow-left mr-1"></i> @lang('app.back')
                    </a>
                </div>

                <div class="card-body">
                    {{-- Context summary --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <x-cards.data-row :label="__('app.employee')" :value="$assignment->employee->name ?? '--'" />
                        </div>
                        <div class="col-md-4">
                            <x-cards.data-row :label="__('app.name')" :value="$asset->name ?: '--'" />
                        </div>
                        <div class="col-md-4">
                            <x-cards.data-row :label="__('app.serialNo')" :value="$assignment->serial_no ?: '--'" />
                        </div>
                    </div>

                    <p class="f-13 text-dark-grey mb-3">
                        Upload the asset handover form signed by the employee (PDF or image). Saving it approves the
                        assignment and issues the asset.
                    </p>

                    <div class="row">
                        <div class="col-lg-12">
                            <x-forms.file :fieldLabel="__('app.signature')" fieldName="signature" fieldId="signature"
                                          allowedFileExtensions="pdf png jpg jpeg svg" />
                            @error('signature')
                                <div class="invalid-feedback d-block mt-1" style="color: red">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-white border-top-grey">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-check mr-1"></i> @lang('app.save')
                    </button>
                    <x-forms.button-cancel :link="$backUrl" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </div>
            </div>
        </form>
    </div>
</div>
