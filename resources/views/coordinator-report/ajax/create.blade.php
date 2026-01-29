@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-coordinator-report-data-form">
            <input type="hidden" name="report_unique" value="1">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.businesses.businessDetails')
                </h4>

                <div class="row  p-20">
                    <div class="col-md-4">
                        <x-forms.select fieldId="business_id" :fieldLabel="__('modules.coordinator-report.business')"
                                    fieldName="business_id" fieldRequired="true">
                            @foreach ($businesses as $business)
                            <option value="{{ $business->id }}" @selected($loop->first)>{{ $business->name }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-4">

                        <x-forms.select2-ajax fieldRequired="true" fieldId="driver_id" fieldName="driver_id"
                                              :fieldLabel="__('modules.drivers.driver')"
                                              :route="route('get.linked-driver-ajax')"
                                              :placeholder="__('placeholders.searchForDrivers')"
                        ></x-forms.select2-ajax>
                    </div>

                    <div class="col-md-4">
                        <x-forms.datepicker fieldId="report_date" :fieldLabel="__('modules.coordinator-report.date')"
                            fieldName="report_date"
                            fieldRequired="true"
                            :fieldPlaceholder="__('modules.coordinator-report.date')"
                            :fieldValue="\Carbon\Carbon::now()->format(company()->date_format)" />
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.coordinator-report.report')
                </h4>

                <div class="row p-20" id="report-fields">

                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <x-form-actions>
                    <x-forms.button-primary id="save-coordinator-report-form" class="mr-3" icon="check">
                        @lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-secondary class="mr-3" id="save-more-driver-project-form" icon="check-double">@lang('app.saveAddMore')
                    </x-forms.button-secondary>
                    <x-forms.button-cancel class="border-0 " data-dismiss="modal">@lang('app.cancel')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {
        let businesses = @json($businesses);

        datepicker('#report_date', {
            ...datepickerConfig,
            position: 'bl'
        });

        const businessFieldsHtml = (fields) => {
            const documentFields = fields.filter(f => f.type === 'DOCUMENT');
            const otherFields = fields.filter(f => f.type !== 'DOCUMENT');

            let html = otherFields.map(field => {
                return `
                    <div class="col-md-4" id="field-wrapper-${field.id}">
                        <input type="hidden" name="fields[${field.id}][field_id]" value="${field.id}" />
                        ${
                            field.type == 'TEXT'
                            ? `<x-forms.text fieldLabel="${field.name}"
                                    fieldName="fields[${field.id}][value]" fieldId="fields[${field.id}][value]" fieldRequired="${field.required}"
                                    fieldPlaceholder="${field.name}">
                                </x-forms.text>`
                            :  `<x-forms.number fieldLabel="${field.name}"
                                    fieldName="fields[${field.id}][value]" fieldId="fields[${field.id}][value]" fieldRequired="${field.required}"
                                    fieldPlaceholder="${field.name}">
                                </x-forms.number>`
                        }

                    </div>`;
            }).join('\n');

            // Add Break
            html += `<div class="col-12"> <div class="row">`;

            html += documentFields.map(field => {
                return `
                    <div class="col-md-4" id="field-wrapper-${field.id}">
                        <input type="hidden" name="fields[${field.id}][field_id]" value="${field.id}" />
                        <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                    fieldLabel="${field.name}" fieldName="fields[${field.id}][value][]"
                                    fieldId="fields[${field.id}][value]">
                        </x-forms.file-multiple>
                    </div>`;
            }).join('\n');

            html += "</div></div>"

            return html;
        };

        $('#save-more-driver-project-form').click(function() {

            $('#add_more').val(true);

            const url = "{{ route('coordinator-report.store') }}";
            var data = $('#save-coordinator-report-data-form').serialize();
            saveBusiness(data, url, "#save-more-driver-project-form");
        });

        function renderBusinessFields() {
            const businessId = $('#business_id').val();
            const business = businesses.find(b => b.id == businessId);

            const html = businessFieldsHtml(business.fields);
            $('#report-fields').html(html);
        }

        $('#business_id').on('change', renderBusinessFields);

        renderBusinessFields();


        $('#save-coordinator-report-form').click(function() {

            const url = "{{ route('coordinator-report.store') }}";
            var data = $('#save-coordinator-report-data-form').serialize();
            saveBusiness(data, url, "#save-coordinator-report-form");

        });

        function saveBusiness(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#save-coordinator-report-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        }
                        else if(response.add_more == true) {

                            var right_modal_content = $.trim($(RIGHT_MODAL_CONTENT).html());

                            if(right_modal_content.length) {

                                $(RIGHT_MODAL_CONTENT).html(response.html.html);
                                $('#add_more').val(false);
                            }
                            else {

                                $('.content-wrapper').html(response.html.html);
                                init('.content-wrapper');
                                $('#add_more').val(false);
                            }

                        }
                        else {

                            window.location.href = response.redirectUrl;

                        }

                        if (typeof showTable !== 'undefined' && typeof showTable === 'function') {
                            showTable();
                        }

                    }

                }
            });
        }

        init(RIGHT_MODAL);
    });
</script>
