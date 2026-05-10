<div class="modal-header">
    <h5 class="modal-title">@lang('app.reject') @lang('modules.airTicket.rejectTicket')</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
</div>
<div class="modal-body">
    <x-form id="reject-ticket-form" method="POST">
        <div class="row">
            <div class="col-md-12">
                <x-forms.textarea :fieldLabel="__('app.reason')" fieldName="reason" fieldId="reason" fieldRequired="true" />
            </div>
        </div>
    </x-form>
</div>
<div class="modal-footer">
    <x-forms.button-cancel data-dismiss="modal" class="border-0 mr-3">@lang('app.close')</x-forms.button-cancel>
    <x-forms.button-primary id="save-reject-ticket" icon="check">@lang('app.save')</x-forms.button-primary>
</div>

<script>
    $('#save-reject-ticket').click(function() {
        if($('#reason').val().trim() === ''){

            Swal.fire({
                icon: 'error',
                text: 'reason is required.',
                toast: true,
                position: 'top-end',
                timer: 3000,
                timerProgressBar: true,
                showConfirmButton: false,
                showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
            });

            return;
        }
        $.easyAjax({
            url: '{{ route("air_tickets.ticket_action") }}',
            type: "POST",
            data: {
                'action': 'rejected',
                'ticketId': '{{ $ticketID }}',
                'reason': $('#reason').val(),
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
