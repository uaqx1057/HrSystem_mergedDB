<!-- IQAMA ROW START -->
<div class="row">
    <!--  USER CARDS START -->
    <div class="col-xl-12 col-lg-12 col-md-12 mb-4 mb-xl-0 mb-lg-4 mb-md-0 mt-5">
        @if(is_null($driver->iqama))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="iqama">
                @lang('modules.drivers.addIqama')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.iqamaDetails')">

            @if($driver->iqama)
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

                <x-cards.data-row :label="__('modules.drivers.expiryDate')" :value=" $driver->iqaama_expiry_date  ? $driver->iqaama_expiry_date->format(company()->date_format) : '--'" />
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($driver->iqama)
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $driver->iqama_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
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
        @if(is_null($driver->license))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="license">
                @lang('modules.drivers.addLicense')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.licenseDetails')">

            @if($driver->license)
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

                <x-cards.data-row :label="__('modules.drivers.expiryDate')" :value=" $driver->license_expiry_date  ? $driver->license_expiry_date->format(company()->date_format) : '--'" />
                <div class="col-12 px-0 pb-3 d-block d-lg-flex d-md-flex">
                    <p class="mb-0 text-lightest f-14 w-30 d-inline-block text-capitalize">
                        @lang('modules.employees.scanCopy')</p>
                    <p class="mb-0 text-dark-grey f-14 w-70">
                        @if($driver->license)
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $driver->license_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
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
        @if(is_null($driver->medical))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="medical">
                @lang('modules.drivers.addMedical')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.medicalDetails')">

            @if($driver->medical)
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
                        @if($driver->medical)
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $driver->medical_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
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
        @if(is_null($driver->sim_form))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="sim-form">
                @lang('modules.drivers.addSimForm')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.simFormDetails')">

            @if($driver->sim_form)
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
                        @if($driver->sim_form)
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $driver->sim_form_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
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
        @if(is_null($driver->mobile_form))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="mobile-form">
                @lang('modules.drivers.addMobileForm')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.mobileFormDetails')">

            @if($driver->mobile_form)
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
                        @if($driver->mobile_form)
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $driver->mobile_form_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
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
        @if(is_null($driver->other_document))
             <x-forms.button-primary class="mr-3 add-document mb-3" icon="plus"  data-tab="other-document">
                @lang('modules.drivers.addOtherDocument')
            </x-forms.button-primary>
        @endif
        <x-cards.data :title="__('modules.drivers.otherDocumentDetails')">

            @if($driver->other_document)
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
                        @if($driver->other_document)
                            <a target="_blank" class="text-dark-grey"
                                href="{{ $driver->other_document_url }}"><i class="fa fa-external-link-alt"></i> <u>@lang('app.viewScanCopy')</u></a>
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
