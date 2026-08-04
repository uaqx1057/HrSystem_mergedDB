<div class="row">
    <div class="col-sm-12">
        <x-form id="save-company-asset-data-form">
            <input type="hidden" name="company_asset_id" value="{{ $asset->id }}">
            <input type="hidden" name="employee_id" value="{{ $employeeId ?? '' }}">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.menu.assignCompanyAsset')</h4>
                <div class="row p-20">

                    <div class="col-md-6">
                        @if (in_array($addPermission, ['all', 'branch']))
                            <x-forms.label class="" fieldId="employee"
                                :fieldLabel="__('app.employee')" fieldRequired="true">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="employee"
                                    id="employee" data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ (!empty($employeeId) && $employeeId == $employee->id) ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        @else
                            <input type="hidden" value="{{ user()->id }}" name="employee">
                            <x-forms.text fieldId="basic_salary" :fieldLabel="__('app.employee')" fieldName="basic_salary"
                                fieldRequired="true" :fieldPlaceholder="__('placeholders.basic_salary')" :fieldValue="user()->name" :fieldReadOnly="true">
                            </x-forms.text>
                        @endif
                    </div>
                    {{-- <div class="col-md-6">
                        <x-forms.number fieldId="asset_qty" :fieldLabel="__('app.qty')" fieldName="qty"
                                      fieldRequired="true" :fieldPlaceholder="__('placeholders.qty')" fieldValue="1">
                        </x-forms.number>
                        <small class="form-text text-muted">@lang('messages.availableQty'): {{ $asset->available_qty }}</small>
                    </div> --}}

                    <input type="hidden" name="qty" value="1">

                    <div class="col-md-6">
                        <x-forms.label class="" fieldId="serial_no"
                            :fieldLabel="__('app.serialNo')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="serial_no"
                                id="serial_no" data-live-search="true">
                                <option value="">--</option>
                                @foreach ($serials as $serial)
                                    <option value="{{ $serial->serial_no }}">{{ $serial->serial_no }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-company-asset-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="!empty($employeeId) ? route('employees.show', [$employeeId, 'tab' => 'company-assets']) : route('company-assets.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function () {

        $('#save-company-asset-form').click(function () {

            const url = "{{ route('company-assets.assign.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-company-asset-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-company-asset-form",
                data: $('#save-company-asset-data-form').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        if ($(MODAL_XL).hasClass('show')) {
                            $(MODAL_XL).modal('hide');
                            window.location.reload();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });
</script>
