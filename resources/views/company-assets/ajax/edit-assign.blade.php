<div class="row">
    <div class="col-sm-12">
        <x-form id="update-assign-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 form-heading-background font-weight-normal text-capitalize border-bottom-grey">
                    Edit Assign Company Asset</h4>
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
                                    <option value="{{ $employee->id }}" {{ $assignment && $assignment->employee_id == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
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
                                <option value="Pending" {{ $assignment && $assignment->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Approve" {{ $assignment && $assignment->status == 'Approve' ? 'selected' : '' }}>Approve</option>
                            </select>
                        </x-forms.input-group>
                    </div>

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="update-assign-form" class="mr-3" icon="check">@lang('app.save')
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

        $('#update-assign-form').click(function () {

            const url = "{{ route('company-assets.update-assign', $asset->id) }}";

            $.easyAjax({
                url: url,
                container: '#update-assign-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#update-assign-form",
                data: $('#update-assign-data-form').serialize(),
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
