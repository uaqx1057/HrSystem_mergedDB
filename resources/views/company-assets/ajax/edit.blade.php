<div class="row">
    <div class="col-sm-12">
        <x-form id="edit-company-asset-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
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
