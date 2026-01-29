@php
$addDesignationPermission = user()->permission('add_designation');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/tagify.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="update-business-data-form" method="PUT">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.businesses.businessDetails')
                </h4>

                <div class="row  p-20">
                    <div class="col-md-4">
                        <x-forms.text fieldId="name" :fieldLabel="__('modules.businesses.name')"
                            fieldName="name" fieldRequired="true"
                            :fieldPlaceholder="__('modules.businesses.name')" :fieldValue="$business->name">
                        </x-forms.text>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.businesses.businessFields')
                </h4>


                <div class="row p-20" id="add-new-field">
                    @foreach ($business->fields->whereNotIn('name', \App\Models\Business::$presetFields) as $field)
                    <div class="col-md-4">
                        <x-forms.text fieldId="fields[{{ $field->id}}][name]" :fieldLabel="__('modules.businesses.name')"
                            fieldName="fields[{{ $field->id}}][name]" fieldRequired="true"
                            :fieldPlaceholder="__('modules.businesses.name')" :fieldValue="$field->name">
                        </x-forms.text>
                    </div>
                    <div class="col-md-12">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="hidden" name="fields[{{ $field->id}}][required]" value="0" />
                                <x-forms.checkbox
                                fieldId="fields[{{ $field->id}}][required]"
                                :fieldLabel="__('modules.businesses.required')"
                                fieldName="fields[{{ $field->id}}][required]"
                                :checked="$field->required"
                                :fieldValue="1" />
                            </div>
                            <div class="col-md-2">
                                <input type="hidden" name="fields[{{ $field->id}}][admin_only]" value="0" />
                                <x-forms.checkbox
                                fieldId="fields[{{ $field->id}}][admin_only]"
                                :fieldLabel="__('modules.businesses.adminOnly')"
                                fieldName="fields[{{ $field->id}}][admin_only]"
                                :checked="$field->admin_only"
                                :fieldValue="1" />
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <x-forms.custom-field :fields="$fields"></x-forms.custom-field>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.businesses.driverCalculation')
                </h4>



                <div id="newCalculationFields">
                    @foreach ($business->driver_calculations as $item)
                    <div class="row p-20 parent-container">
                        {{-- Begin:: Type Field --}}
                        <div class="col-md-3">
                                <x-forms.select fieldId="calculation_type" :fieldLabel="__('modules.businesses.type')"
                                fieldName="calculation_type[]" fieldRequired="false">
                                <option value="RANGE" @selected($item->type == 'RANGE')>{{ _('RANGE') }}</option>
                                <option value="FIXED" @selected($item->type == 'FIXED')>{{ _('FIXED') }}</option>
                            </x-forms.select>
                        </div>
                        {{-- End:: Type Field --}}


                        {{-- Begin:: Equal And Above --}}
                        {{-- <div class="col-md-3 equal_and_above_div
                            @if($item->type == 'RANGE')
                                d-none
                            @elseif ($item->type == 'EQUAL & ABOVE')
                            @elseif ($item->type == 'FIXED')
                                d-none
                            @endif
                        ">
                            <x-forms.number fieldId="equal_and_above" :fieldLabel="__('modules.businesses.equal_and_above')"
                                fieldName="equal_and_above[]" fieldRequired="false"
                                :fieldPlaceholder="__('modules.businesses.equal_and_above')" fieldValue="{{ $item->value }}">
                            </x-forms.number>
                        </div> --}}
                        {{-- End:: Equal And Above --}}


                        {{-- Begin:: Range From Field --}}
                        <div class="
                            col-md-3 range_from_div
                            @if($item->type == 'RANGE')

                            @elseif ($item->type == 'EQUAL & ABOVE')
                            d-none
                            @elseif ($item->type == 'FIXED')
                            d-none
                            @endif
                        ">
                            <x-forms.number fieldId="range_from" :fieldLabel="__('modules.businesses.rangeFrom')"
                                fieldName="range_from[]" fieldRequired="false"
                                :fieldPlaceholder="__('modules.businesses.rangeFrom')" fieldValue="{{ $item->from_value }}">
                            </x-forms.number>
                        </div>
                        {{-- End:: Range From Field --}}

                        {{-- Begin:: Range To Field --}}
                        <div class="
                            col-md-3 range_to_div
                            @if($item->type == 'RANGE')

                            @elseif ($item->type == 'EQUAL & ABOVE')
                            d-none
                            @elseif ($item->type == 'FIXED')
                            d-none
                            @endif
                        ">
                            <x-forms.number fieldId="range_to" :fieldLabel="__('modules.businesses.rangeTo')"
                                fieldName="range_to[]" fieldRequired="false"
                                :fieldPlaceholder="__('modules.businesses.rangeTo')" fieldValue="{{ $item->to_value }}">
                            </x-forms.number>
                        </div>
                        {{-- End:: Range To Field --}}

                        {{-- Begin:: Amount Field --}}
                        <div class="col-md-3">
                            <x-forms.number fieldId="amount_field" :fieldLabel="__('modules.businesses.amount')"
                                fieldName="amount_field[]" fieldRequired="false"
                                :fieldPlaceholder="__('modules.businesses.amount')" fieldValue="{{ $item->amount }}">
                            </x-forms.number>
                        </div>
                        {{-- Begin:: Amount Field --}}

                        <div class="col-12">
                            <button type="button" class="btn-primary rounded f-14 py-2 px-4 mt-4" id="remove_calculation_field">{{ _('Remove') }}</button>
                        </div>
                    </div>
                    @endforeach
                    <div class="row p-20 parent-container">
                        {{-- Begin:: Type Field --}}
                        <div class="col-md-3">
                                <x-forms.select fieldId="calculation_type" :fieldLabel="__('modules.businesses.type')"
                                fieldName="calculation_type[]" fieldRequired="false">
                                <option value="RANGE">{{ _('RANGE') }}</option>
                                {{-- <option value="EQUAL & ABOVE">{{ _('EQUAL & ABOVE') }}</option> --}}
                                <option value="FIXED">{{ _('FIXED') }}</option>
                            </x-forms.select>
                        </div>
                        {{-- End:: Type Field --}}

                        {{-- Begin:: Equal And Above --}}
                        {{-- <div class="col-md-3 equal_and_above_div d-none">
                            <x-forms.number fieldId="equal_and_above" :fieldLabel="__('modules.businesses.equal_and_above')"
                                fieldName="equal_and_above[]" fieldRequired="false"
                                :fieldPlaceholder="__('modules.businesses.equal_and_above')">
                            </x-forms.number>
                        </div> --}}
                        {{-- End:: Equal And Above --}}


                        {{-- Begin:: Range From Field --}}
                        <div class="col-md-3 range_from_div">
                            <x-forms.number fieldId="range_from" :fieldLabel="__('modules.businesses.rangeFrom')"
                                fieldName="range_from[]" fieldRequired="false"
                                :fieldPlaceholder="__('modules.businesses.rangeFrom')">
                            </x-forms.number>
                        </div>
                        {{-- End:: Range From Field --}}

                        {{-- Begin:: Range To Field --}}
                        <div class="col-md-3 range_to_div">
                            <x-forms.number fieldId="range_to" :fieldLabel="__('modules.businesses.rangeTo')"
                                fieldName="range_to[]" fieldRequired="false"
                                :fieldPlaceholder="__('modules.businesses.rangeTo')">
                            </x-forms.number>
                        </div>
                        {{-- End:: Range To Field --}}

                        {{-- Begin:: Amount Field --}}
                        <div class="col-md-3">
                            <x-forms.number fieldId="amount_field" :fieldLabel="__('modules.businesses.amount')"
                                fieldName="amount_field[]" fieldRequired="false"
                                :fieldPlaceholder="__('modules.businesses.amount')">
                            </x-forms.number>
                        </div>
                        {{-- Begin:: Amount Field --}}

                        <div class="col-12">
                            <button type="button" class="btn-primary rounded f-14 py-2 px-4 mt-4" id="add_calculation_field">{{ _('Add') }}</button>
                        </div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-business-form" class="mr-3" icon="check">
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

        const newCalculationFieldHtml = () => `
                <div class="row p-20 parent-container">
                    {{-- Begin:: Type Field --}}
                    <div class="col-md-3">
                            <x-forms.select fieldId="calculation_type" :fieldLabel="__('modules.businesses.type')"
                            fieldName="calculation_type[]" fieldRequired="false">
                            <option value="RANGE">{{ _('RANGE') }}</option>
                            <option value="FIXED">{{ _('FIXED') }}</option>
                        </x-forms.select>
                    </div>
                    {{-- End:: Type Field --}}

                    {{-- Begin:: Equal And Above --}}
                    <div class="col-md-3 equal_and_above_div d-none">
                        <x-forms.number fieldId="equal_and_above" :fieldLabel="__('modules.businesses.equal_and_above')"
                            fieldName="equal_and_above[]" fieldRequired="false"
                            :fieldPlaceholder="__('modules.businesses.equal_and_above')">
                        </x-forms.number>
                    </div>
                    {{-- End:: Equal And Above --}}

                    {{-- Begin:: Range From Field --}}
                    <div class="col-md-3 range_from_div">
                        <x-forms.number fieldId="range_from" :fieldLabel="__('modules.businesses.rangeFrom')"
                            fieldName="range_from[]" fieldRequired="false"
                            :fieldPlaceholder="__('modules.businesses.rangeFrom')">
                        </x-forms.number>
                    </div>
                    {{-- End:: Range From Field --}}

                    {{-- Begin:: Range To Field --}}
                    <div class="col-md-3 range_to_div">
                        <x-forms.number fieldId="range_to" :fieldLabel="__('modules.businesses.rangeTo')"
                            fieldName="range_to[]" fieldRequired="false"
                            :fieldPlaceholder="__('modules.businesses.rangeTo')">
                        </x-forms.number>
                    </div>
                    {{-- End:: Range To Field --}}

                    {{-- Begin:: Amount Field --}}
                    <div class="col-md-3">
                        <x-forms.number fieldId="amount_field" :fieldLabel="__('modules.businesses.amount')"
                            fieldName="amount_field[]" fieldRequired="false"
                            :fieldPlaceholder="__('modules.businesses.ars')">
                        </x-forms.number>
                    </div>
                    {{-- Begin:: Amount Field --}}

                    <div class="col-12">
                        <button type="button" class="btn-primary rounded f-14 py-2 px-4 mt-4" id="add_calculation_field">{{ _('Add') }}</button>
                    </div>
                </div>
        `;

        $('#update-business-form').click(function() {
            const url = "{{ route('businesses.update', $business->id) }}";
            var data = $('#update-business-data-form').serialize();
            updateBusiness(data, url, "#update-business-form");

        });


        $('body').on('change', '#calculation_type', function(){
            const calculation_type = $(this).val();
            const equalAndAboveDiv = $(this).closest('.parent-container').find('.equal_and_above_div');
            const rangeFromDiv = $(this).closest('.parent-container').find('.range_from_div');
            const rangeToDiv = $(this).closest('.parent-container').find('.range_to_div');

            // Validate calculation_type
            if (calculation_type === 'RANGE') {
                // Handle RANGE calculation type
                equalAndAboveDiv.addClass('d-none');
                rangeFromDiv.removeClass('d-none');
                rangeToDiv.removeClass('d-none');
            } else if (calculation_type === 'EQUAL & ABOVE') {
                // Handle EQUAL & ABOVE calculation type
                equalAndAboveDiv.removeClass('d-none');
                rangeFromDiv.addClass('d-none');
                rangeToDiv.addClass('d-none');
            } else if (calculation_type === 'FIXED') {
                // Handle FIXED calculation type
                equalAndAboveDiv.addClass('d-none');
                rangeFromDiv.addClass('d-none');
                rangeToDiv.addClass('d-none');
            }
        });

        $('body').on('click', '#remove_calculation_field', function(){
            $(this).closest('.parent-container').remove();
        });

        $('body').on('keyup', '#form-control', function(){
                console.log($(this).val());
                $(this).val($(this).val().replace(/[^\d.]/g, '').replace(/(\..*?)\..*/g, '$1'));
            });

        $('body').on('click', '#add_calculation_field', function() {
            const calculation_type = $(this).closest('.parent-container').find('#calculation_type').val();
            const rangeFrom = $(this).closest('.parent-container').find('#range_from').val();
            const rangeTo = $(this).closest('.parent-container').find('#range_to').val();
            const amount = $(this).closest('.parent-container').find('#amount_field').val();
            const equalAndAbove = $(this).closest('.parent-container').find('#equal_and_above').val();

            $('#newCalculationFields').append(newCalculationFieldHtml());

            setTimeout(() => {
                $(this).attr('id', 'remove_calculation_field');
                $(this).html("{{ _('Remove') }}");
            }, 10);

        });

        function updateBusiness(data, url, buttonSelector) {
            $.easyAjax({
                url: url,
                container: '#update-business-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: buttonSelector,
                file: true,
                data: data,
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = '{{ route('businesses.index') }}';
                    }

                }
            });
        }

        init(RIGHT_MODAL);
    });
</script>
