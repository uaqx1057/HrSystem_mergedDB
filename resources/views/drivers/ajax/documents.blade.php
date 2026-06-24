<!-- IQAMA ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">
        @if(is_null($driver_iqama))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="iqama">
                @lang('modules.drivers.addIqama')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.iqamaDetails')">

            @if($driver_iqama)
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item edit-document"  data-tab="iqama"
                                    href="javascript:;">@lang('app.edit')</a>
                        </div>

                    </div>
                </x-slot>

                <x-cards.data-row :label="__('modules.drivers.expiryDate')" :value=" $driver_iqama->expires_at  ? $driver_iqama->expires_at->format(company()->date_format) : '--'" />
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($driver_iqama->original_name)
                            @php
                                $file_url =  route('driver-documents.preview', $driver_iqama->id);
                            @endphp
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $file_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
                        @else
                        --
                        @endif

                    </p>
                </div>

            @else
                <x-cards.no-record-found-list colspan="5"/>
            @endif
        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- IQAMA ROW END -->

<!-- LICENSE ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">
        @if(is_null($driver_license))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="license">
                @lang('modules.drivers.addLicense')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.licenseDetails')">

            @if($driver_license)
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item edit-document"  data-tab="license"
                                    href="javascript:;">@lang('app.edit')</a>
                        </div>

                    </div>
                </x-slot>

                <x-cards.data-row :label="__('modules.drivers.expiryDate')" :value=" $driver_license->expires_at  ? $driver_license->expires_at->format(company()->date_format) : '--'" />
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($driver_license->original_name)
                            @php
                                $file_url =  route('driver-documents.preview', $driver_license->id);
                            @endphp
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $file_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
                        @else
                        --
                        @endif

                    </p>
                </div>

            @else
                <x-cards.no-record-found-list colspan="5"/>
            @endif
        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- LICENSE ROW END -->

<!-- MEDICAL ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">
        @if(is_null($driver_medical))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="medical">
                @lang('modules.drivers.addMedical')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.medicalDetails')">

            @if($driver_medical)
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item edit-document"  data-tab="medical"
                                    href="javascript:;">@lang('app.edit')</a>
                        </div>

                    </div>
                </x-slot>

                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($driver_medical->original_name)
                            @php
                                $file_url =  route('driver-documents.preview', $driver_medical->id);
                            @endphp
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $file_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
                        @else
                        --
                        @endif

                    </p>
                </div>

            @else
                <x-cards.no-record-found-list colspan="5"/>
            @endif
        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- MEDICAL ROW END -->

<!-- SIM-FORM ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">
        @if(is_null($driver_contract))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="sim-form">
                @lang('modules.drivers.addSimForm')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.simFormDetails')">

            @if($driver_contract)
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item edit-document"  data-tab="sim-form"
                                    href="javascript:;">@lang('app.edit')</a>
                        </div>

                    </div>
                </x-slot>

                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($driver_contract->original_name)
                            @php
                                $file_url =  route('driver-documents.preview', $driver_contract->id);
                            @endphp
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $file_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
                        @else
                        --
                        @endif

                    </p>
                </div>

            @else
                <x-cards.no-record-found-list colspan="5"/>
            @endif
        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- SIM-FORM ROW END -->

<!-- MOBILE-FORM ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">
        @if(is_null($driver_mobile))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="mobile-form">
                @lang('modules.drivers.addMobileForm')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.mobileFormDetails')">

            @if($driver_mobile)
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item edit-document"  data-tab="mobile-form"
                                    href="javascript:;">@lang('app.edit')</a>
                        </div>

                    </div>
                </x-slot>

                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($driver_mobile->original_name)
                            @php
                                $file_url =  route('driver-documents.preview', $driver_mobile->id);
                            @endphp
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $file_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
                        @else
                        --
                        @endif

                    </p>
                </div>

            @else
                <x-cards.no-record-found-list colspan="5"/>
            @endif
        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- MOBILE-FORM ROW END -->

<!-- OTHER DOCUMENT ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">
        @if(is_null($driver_other))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="other-document">
                @lang('modules.drivers.addOtherDocument')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.otherDocumentDetails')">

            @if($driver_other)
                <x-slot name="action">
                    <div class="dropdown">
                        <button class="btn f-14 px-0 py-0 text-dark-grey dropdown-toggle" type="button"
                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                                <a class="dropdown-item edit-document"  data-tab="other-document"
                                    href="javascript:;">@lang('app.edit')</a>
                        </div>

                    </div>
                </x-slot>

                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($driver_other->original_name)
                            @php
                                $file_url =  route('driver-documents.preview', $driver_other->id);
                            @endphp
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $file_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
                        @else
                        --
                        @endif

                    </p>
                </div>

            @else
                <x-cards.no-record-found-list colspan="5"/>
            @endif
        </x-cards.data>
    </div>
    <!--  USER CARDS END -->
</div>
<!-- OTHER DOCUMENT ROW END -->

<script>

    // Iqama Start
    $('.add-document, .edit-document').click(function(){
        const tab = $(this).attr('data-tab');
        var url = `{{ route('drivers.edit', $driver->id) }}?tab=${tab}`;
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    $('body').on('click', '.delete-iqama', function () {
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {

                var url = "{{ route('drivers.update', $driver->id) }}";
                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function (response) {
                        if (response.status == "success") {
                            window.location.reload();
                        }
                    }
                });
            }
        });
    });
    // Iqama End

</script>
