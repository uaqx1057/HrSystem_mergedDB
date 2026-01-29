@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="update-coordinator-report-data-form" method="PUT">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.coordinator-report.coordinatorReport')
                </h4>

                <div class="p-20">
                    <div class="row">
                        @foreach ($fields->whereIn('type', [ 'TEXT', 'INTEGER' ]) as $field)
                            <div class="col-md-4" id="field-wrapper-{{ $field->id }}">
                                <input type="hidden" name="fields[{{ $field->id }}][field_id]" value="{{ $field->id }}" />
                                @if ($field == 'TEXT')
                                    <x-forms.text fieldLabel="{{ $field->name }}"
                                        fieldName="fields[{{ $field->id }}][value]" fieldId="fields[{{ $field->id }}][value]" fieldRequired="{{ $field->required }}"
                                        fieldPlaceholder="{{ $field->name }}">
                                    </x-forms.text>
                                @elseif($field == 'INTEGER')
                                    <x-forms.number fieldLabel="{{ $field->name }}"
                                        fieldName="fields[{{ $field->id }}][value]" fieldId="fields[{{ $field->id }}][value]" fieldRequired="{{ $field->required }}"
                                        fieldPlaceholder="{{ $field->name }}">
                                    </x-forms.number>
                                @endif

                            </div>
                        @endforeach
                    </div>

                    <div class="row">
                        @foreach ($fields->whereIn('type', [ 'DOCUMENT' ]) as $field)
                            <div class="col-md-4" id="field-wrapper-{{ $field->id }}">
                                <input type="hidden" name="fields[{{ $field->id }}][field_id]" value="{{ $field->id }}" />
                                <x-forms.file class="mr-0 mr-lg-2 mr-md-2"
                                            fieldLabel="{{ $field->name }}" fieldName="fields[{{ $field->id }}][value]"
                                            fieldId="fields[{{ $field->id }}][value]">
                                </x-forms.file>
                            </div>
                        @endforeach
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-coordinator-report-form" class="mr-3" icon="check">
                        @lang('app.update')
                    </x-forms.button-primary>
                    <x-forms.button-cancel class="border-0 " data-dismiss="modal">@lang('app.cancel')
                    </x-forms.button-cancel>

                </x-form-actions>
            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function() {

        $('#update-coordinator-report-form').click(function() {
            const url = "{{ route('coordinator-report.update', $coordinator_report->id) }}";
            var data = $('#update-coordinator-report-data-form').serialize();
            updateBusiness(data, url, "#update-coordinator-report-form");

        });

        function updateBusiness(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#update-coordinator-report-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = '{{ route('coordinator-report.index') }}';
                    }

                }
            });
        }

        init(RIGHT_MODAL);
    });
</script>
