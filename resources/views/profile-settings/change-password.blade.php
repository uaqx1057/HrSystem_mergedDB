<div class="modal-header">
    <h5 class="modal-title" id="modelHeading">Change Password</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <x-form id="change-password-form" action="{{ route('profile.change-password.update') }}" class="ajax-form" method="POST">
        <div class="row">
            <div class="col-lg-12">
                <x-forms.label fieldId="current-password" fieldLabel="Current Password" fieldRequired="true" />
                <x-forms.input-group>
                    <input type="password" name="current_password" id="current-password" autocomplete="current-password"
                        placeholder="Current Password" class="form-control height-35 f-14">
                    <x-slot name="append">
                        <button type="button" data-toggle="tooltip" data-original-title="@lang('app.viewPassword')"
                            class="btn btn-outline-secondary border-grey height-35 change-password-toggle">
                            <i class="fa fa-eye"></i>
                        </button>
                    </x-slot>
                </x-forms.input-group>
            </div>
            <div class="col-lg-12">
                <x-forms.label class="mt-3" fieldId="new-password" fieldLabel="New Password" fieldRequired="true" />
                <x-forms.input-group>
                    <input type="password" name="password" id="new-password" autocomplete="new-password"
                        placeholder="New Password" class="form-control height-35 f-14">
                    <x-slot name="append">
                        <button type="button" data-toggle="tooltip" data-original-title="@lang('app.viewPassword')"
                            class="btn btn-outline-secondary border-grey height-35 change-password-toggle">
                            <i class="fa fa-eye"></i>
                        </button>
                    </x-slot>
                </x-forms.input-group>
            </div>
            <div class="col-lg-12">
                <x-forms.label class="mt-3" fieldId="confirm-password" fieldLabel="Confirm New Password" fieldRequired="true" />
                <x-forms.input-group>
                    <input type="password" name="password_confirmation" id="confirm-password" autocomplete="new-password"
                        placeholder="Confirm New Password" class="form-control height-35 f-14">
                    <x-slot name="append">
                        <button type="button" data-toggle="tooltip" data-original-title="@lang('app.viewPassword')"
                            class="btn btn-outline-secondary border-grey height-35 change-password-toggle">
                            <i class="fa fa-eye"></i>
                        </button>
                    </x-slot>
                </x-forms.input-group>
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.cancel')</x-forms.button-cancel>
    <x-forms.button-primary id="save-password" icon="check">Change Password</x-forms.button-primary>
</div>

<script>
    $('#save-password').click(function() {
        $.easyAjax({
            url: $('#change-password-form').attr('action'),
            container: '#change-password-form',
            disableButton: true,
            blockUI: true,
            buttonSelector: '#save-password',
            type: 'POST',
            data: $('#change-password-form').serialize(),
            success: function() {
                $(MODAL_LG).modal('hide');
            }
        });
    });

    $('#change-password-form').on('click', '.change-password-toggle', function() {
        const input = $(this).closest('.input-group').find('input');
        const visible = input.attr('type') === 'text';

        input.attr('type', visible ? 'password' : 'text');
        $(this).find('i').toggleClass('fa-eye fa-eye-slash');
    });
</script>