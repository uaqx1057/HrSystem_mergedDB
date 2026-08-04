<div class="row">
    <div class="col-sm-12">
        <x-form id="assign-employee-company-asset-form">
            <input type="hidden" name="employee" value="{{ $companyAssetEmployeeId }}">
            <input type="hidden" name="employee_id" value="{{ $companyAssetEmployeeId }}">

            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.menu.assignCompanyAsset')
                </h4>

                <div class="p-20">
                    <x-forms.text
                        fieldId="employee_name"
                        :fieldLabel="__('app.employee')"
                        fieldName="employee_name"
                        :fieldValue="$employee->name"
                        :fieldReadOnly="true"
                    ></x-forms.text>

                    @if ($assignableAssets->isEmpty())
                        <div class="alert alert-warning mt-3 mb-0">
                            {{ __('messages.noRecordFound') }}
                        </div>
                    @else
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <x-forms.label fieldId="company_asset_id" :fieldLabel="__('app.menu.companyAssets')" fieldRequired="true"></x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="company_asset_id" id="company_asset_id" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($assignableAssets as $asset)
                                            <option value="{{ $asset['id'] }}" data-serials='@json($asset['serials'])'>
                                                {{ $asset['name'] }} ({{ $asset['catalog'] ?: 'N/A' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </x-forms.input-group>
                            </div>

                            <input type="hidden" name="qty" value="1">

                            <div class="col-md-6">
                                <x-forms.label fieldId="serial_no" :fieldLabel="__('app.serialNo')" fieldRequired="true"></x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control select-picker" name="serial_no" id="serial_no" data-live-search="true">
                                        <option value="">--</option>
                                    </select>
                                </x-forms.input-group>
                            </div>
                        </div>
                    @endif
                </div>

                <x-form-actions>
                    @if (!$assignableAssets->isEmpty())
                        <x-forms.button-primary id="save-employee-company-asset" class="mr-3" icon="check">
                            @lang('app.save')
                        </x-forms.button-primary>
                    @endif
                    <x-forms.button-cancel :link="route('employees.show', [$companyAssetEmployeeId, 'tab' => 'company-assets'])" class="border-0">
                        @lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>
            </div>
        </x-form>
    </div>
</div>

<script>
    (function () {
        function refreshSerials() {
            const selected = $('#company_asset_id option:selected');
            const serials = selected.data('serials') || [];
            const $serial = $('#serial_no');

            $serial.empty();
            $serial.append('<option value="">--</option>');

            serials.forEach(function (serialNo) {
                $serial.append('<option value="' + serialNo + '">' + serialNo + '</option>');
            });

            $serial.selectpicker('refresh');
        }

        $('#company_asset_id').on('change', refreshSerials);

        $('#save-employee-company-asset').on('click', function () {
            $.easyAjax({
                url: "{{ route('company-assets.assign.store') }}",
                container: '#assign-employee-company-asset-form',
                type: 'POST',
                disableButton: true,
                blockUI: true,
                buttonSelector: '#save-employee-company-asset',
                data: $('#assign-employee-company-asset-form').serialize(),
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
        refreshSerials();
    })();
</script>
