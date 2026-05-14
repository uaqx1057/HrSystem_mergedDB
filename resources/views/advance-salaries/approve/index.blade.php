<div class="modal-header">
    <h5 class="modal-title">@lang('app.approve') @lang('modules.advanceSalary.title')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="approve-salary-form" method="POST">
        <div class="row">
            <div class="col-md-12">
                <x-forms.textarea :fieldLabel="__('app.note')" fieldName="approve_reason" fieldId="approve_reason" />
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-approve-salary" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#save-approve-salary').click(function() {
        $.easyAjax({
            url: '{{ route("advance_salaries.salary_action") }}',
            type: "POST",
            disableButton: true,
            buttonSelector: "#save-approve-salary",
            data: {
                'action': 'approved',
                'salaryId': '{{ $salaryID }}',
                'approveReason': $('#approve_reason').val(),
                '_token': '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.status == "success") {
                    window.location.reload();
                }
            }
        })
    });
</script>
