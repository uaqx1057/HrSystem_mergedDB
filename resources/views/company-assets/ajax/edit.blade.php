<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-company-asset-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.menu.editCompanyAsset')</h4>
                <div class="row p-20">
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_name" :fieldLabel="__('app.name')" fieldName="name"
                                      fieldRequired="true" :fieldValue="$asset->name" :fieldPlaceholder="__('placeholders.name')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_catalog" :fieldLabel="__('app.catalog')" fieldName="catalog"
                                      fieldRequired="true" :fieldValue="$asset->catalog" :fieldPlaceholder="__('placeholders.catalog')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_sku_no" :fieldLabel="__('SKU No')" fieldName="sku_no"
                                      fieldRequired="true" :fieldValue="$asset->sku_no" :fieldPlaceholder="__('placeholders.skuNo')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_type" :fieldLabel="__('app.type')" fieldName="type"
                                      fieldRequired="true" :fieldValue="$asset->type" :fieldPlaceholder="__('placeholders.type')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_brand" :fieldLabel="__('app.brand')" fieldName="brand"
                                      fieldRequired="true" :fieldValue="$asset->brand" :fieldPlaceholder="__('placeholders.brand')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.label class="" fieldId="department_id"
                            :fieldLabel="__('app.department')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="department_id"
                                id="department_id" data-live-search="true">
                                <option value="">--</option>
                                @foreach ($departments as $department)
                                    <option value="{{ $department->id }}" {{ $asset->department_id == $department->id ? 'selected' : '' }}>{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-6">
                        <x-forms.label class="" fieldId="branch_id"
                            :fieldLabel="__('app.branchName')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="branch_id"
                                id="branch_id" data-live-search="true">
                                <option value="">--</option>
                                @foreach ($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $asset->branch_id == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="asset_qty" :fieldLabel="__('app.qty')" fieldName="qty"
                                      fieldRequired="true" :fieldValue="$asset->qty" :fieldPlaceholder="__('placeholders.qty')">
                        </x-forms.number>
                    </div>

                    <div class="col-md-12 mt-2" id="serial-numbers-wrapper">
                        <label class="f-14 text-dark-grey">Serial Numbers</label>
                        <div class="row" id="serial-numbers-container"></div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-company-asset-form" class="mr-3" icon="check">@lang('app.update')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('company-assets.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>

<script>
    $(document).ready(function () {

        const existingSerials = {!! json_encode($serials->map(fn($s) => [
            'id' => $s->id,
            'serial_no' => $s->serial_no,
            'status' => $s->status,
        ])) !!};

        function renderSerialFields() {
            let qty = parseInt($('#asset_qty').val()) || 0;
            const maxQty = 200;

            const assignedCount = existingSerials.filter(s => s.status === 'assigned').length;

            // never allow qty below number of already-assigned serials
            if (qty < assignedCount) {
                qty = assignedCount;
                $('#asset_qty').val(assignedCount);
            }
            if (qty > maxQty) {
                qty = maxQty;
                $('#asset_qty').val(maxQty);
            }

            const container = $('#serial-numbers-container');
            container.empty();

            for (let i = 0; i < qty; i++) {
                const existing = existingSerials[i];

                if (existing) {
                    const isAssigned = existing.status === 'assigned';
                    container.append(`
                        <div class="col-md-6 mb-2">
                            <input type="hidden" name="serial_id[]" value="${existing.id}">
                            <input type="text" class="form-control height-35 f-14" name="serial_no[]"
                                   value="${existing.serial_no}"
                                   placeholder="serial number ${i + 1}"
                                   ${isAssigned ? 'readonly title="Assigned - cannot edit"' : ''}
                                   required>
                        </div>
                    `);
                } else {
                    container.append(`
                        <div class="col-md-6 mb-2">
                            <input type="hidden" name="serial_id[]" value="">
                            <input type="text" class="form-control height-35 f-14" name="serial_no[]"
                                   value=""
                                   placeholder="serial number ${i + 1}" required>
                        </div>
                    `);
                }
            }
        }

        $(document).on('input change', '#asset_qty', renderSerialFields);
        renderSerialFields();

        $('#update-company-asset-form').click(function () {

            const url = "{{ route('company-assets.update', $asset->id) }}";

            $.easyAjax({
                url: url,
                container: '#edit-company-asset-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-company-asset-form",
                data: $('#edit-company-asset-data-form').serialize(),
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
