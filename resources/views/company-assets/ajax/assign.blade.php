@php
    $preSerialId = request('serial_id');
@endphp
<div class="row">
    <div class="col-sm-12">
        <x-form id="save-company-asset-data-form">
            <input type="hidden" name="company_asset_id" value="{{ $asset->id }}">
            <input type="hidden" name="employee_id" value="{{ $employeeId ?? '' }}">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.menu.assignCompanyAsset') &mdash; {{ $asset->name }}</h4>
                <div class="row p-20">

                    <div class="col-md-6">
                        @if (in_array($addPermission, ['all', 'branch']))
                            <x-forms.label class="" fieldId="employee" :fieldLabel="__('app.employee')" fieldRequired="true"></x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="employee" id="employee" data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}" {{ (!empty($employeeId) && $employeeId == $employee->id) ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        @else
                            <input type="hidden" value="{{ user()->id }}" name="employee">
                            <x-forms.text fieldId="assign_employee_name" :fieldLabel="__('app.employee')" fieldName="assign_employee_name"
                                :fieldValue="user()->name" :fieldReadOnly="true"></x-forms.text>
                        @endif
                    </div>

                    <div class="col-md-6">
                        <x-forms.label class="" fieldId="company_asset_serial_id" :fieldLabel="__('app.serialNo')" fieldRequired="true"></x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="company_asset_serial_id" id="company_asset_serial_id" data-live-search="true">
                                <option value="">--</option>
                                @foreach ($serials as $serial)
                                    <option value="{{ $serial->id }}" {{ (string) $preSerialId === (string) $serial->id ? 'selected' : '' }}>{{ $serial->serial_no }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                        @if ($serials->isEmpty())
                            <small class="text-danger">No available units to assign.</small>
                        @endif
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-company-asset-form" class="mr-3" icon="check">@lang('app.save')</x-forms.button-primary>
                    <x-forms.button-cancel :link="!empty($employeeId) ? route('employees.show', [$employeeId, 'tab' => 'company-assets']) : route('company-assets.index')" class="border-0">@lang('app.cancel')</x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#save-company-asset-form').click(function () {
            $.easyAjax({
                url: "{{ route('company-assets.assign.store') }}",
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
