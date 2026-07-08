<div class="row">
    <div class="col-sm-12">
        <x-form id="save-company-asset-data-form">
            <input type="hidden" name="company_asset_id" value="{{ $asset->id }}">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    @lang('app.menu.assignCompanyAsset')</h4>
                <div class="row p-20">

                    <div class="col-md-6">
                        <x-forms.label class="my-3" fieldId="employee"
                            :fieldLabel="__('app.employee')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="employee"
                                id="employee" data-live-search="true">
                                <option value="">--</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                @endforeach
                            </select>
                        </x-forms.input-group>
                    </div>
                    <div class="col-md-6">
                        <x-forms.label class="my-3" fieldId="branch_id"
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
                    </div>
                    <div class="col-md-6">
                        <x-forms.label class="my-3" fieldId="status"
                            :fieldLabel="__('app.status')" fieldRequired="true">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control height-35" name="status"
                                id="status">
                                <option value="Pending" selected>Pending</option>
                                <option value="Approve">Approve</option>
                            </select>
                        </x-forms.input-group>
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
