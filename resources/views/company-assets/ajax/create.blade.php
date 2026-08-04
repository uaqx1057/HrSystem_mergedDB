<div class="row">
    <div class="col-sm-12">
        <x-form id="save-company-asset-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.menu.addCompanyAsset')</h4>
                <div class="row p-20">

                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_catalog" :fieldLabel="__('app.catalog')" fieldName="catalog"
                                      fieldRequired="true" :fieldPlaceholder="__('placeholders.catalog')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_sku_no" :fieldLabel="__('SKU No')" fieldName="sku_no"
                                      fieldRequired="true" :fieldPlaceholder="__('placeholders.skuNo')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_name" :fieldLabel="__('app.name')" fieldName="name"
                                      fieldRequired="true" :fieldPlaceholder="__('placeholders.name')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_type" :fieldLabel="__('app.type')" fieldName="type"
                                      fieldRequired="true" :fieldPlaceholder="__('placeholders.type')">
                        </x-forms.text>
                    </div>
                    <div class="col-md-6">
                        <x-forms.text fieldId="asset_brand" :fieldLabel="__('app.brand')" fieldName="brand"
                                      fieldRequired="true" :fieldPlaceholder="__('placeholders.brand')">
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
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-6">
                        @if (in_array($addPermission, ['all']) || ($addPermission === 'branch' && hr_has_all_branch_access('company_assets')))
                            <x-forms.label class="" fieldId="branch_id"
                                :fieldLabel="__('app.branchName')" fieldRequired="true">
                            </x-forms.label>
                            <x-forms.input-group>
                                <select class="form-control select-picker" name="branch_id"
                                    id="branch_id" data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($branches as $branch)
                                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </x-forms.input-group>
                        @else
                            <input type="hidden" value="{{ user()->branch_id }}" name="branch_id">
                            <x-forms.text fieldId="basic_salary" :fieldLabel="__('app.branchName')" fieldName="basic_salary"
                                fieldRequired="true" :fieldPlaceholder="__('placeholders.basic_salary')" :fieldValue="user()->branch?->name" :fieldReadOnly="true">
                            </x-forms.text>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <x-forms.number fieldId="asset_qty" :fieldLabel="__('app.qty')" fieldName="qty"
                                      fieldRequired="true" :fieldPlaceholder="__('placeholders.qty')" fieldValue="1">
                        </x-forms.number>
                    </div>

                    <div class="col-md-12 mt-2" id="serial-numbers-wrapper" style="display:none;">
                        <label class="f-14 text-dark-grey">Serial Numbers</label>
                        <div class="row" id="serial-numbers-container"></div>
                    </div>
                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-company-asset-form" class="mr-3" icon="check">@lang('app.save')
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

        function generateSerialFields() {
            let qty = parseInt($('#asset_qty').val()) || 0;
            const maxQty = 200; // sane upper limit, adjust as needed

            if (qty > maxQty) {
                qty = maxQty;
                $('#asset_qty').val(maxQty);
            }

            const container = $('#serial-numbers-container');
            const existing = container.find('input').length;

            // Keep already-typed values if qty increased, trim if decreased
            const currentValues = container.find('input').map(function () {
                return $(this).val();
            }).get();

            container.empty();

            if (qty > 0) {
                for (let i = 1; i <= qty; i++) {
                    const val = currentValues[i - 1] ? currentValues[i - 1] : '';
                    container.append(`
                        <div class="col-md-6 mb-2">
                            <input type="text" class="form-control height-35 f-14" name="serial_no[]"
                                   value="${val}"
                                   placeholder="serial number ${i}" required>
                        </div>
                    `);
                }
                $('#serial-numbers-wrapper').show();
            } else {
                $('#serial-numbers-wrapper').hide();
            }
        }

        // regenerate on qty change/typing
        $(document).on('input change', '#asset_qty', generateSerialFields);

        // generate initial fields for default qty value
        generateSerialFields();

        $('#save-company-asset-form').click(function () {

            const url = "{{ route('company-assets.store') }}";

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
