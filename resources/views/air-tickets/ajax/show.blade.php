@php
    $editDepartmentPermission = user()->permission('edit_employees');
    $deleteDepartmentPermission = user()->permission('delete_employees');
@endphp

<div id="department-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header form-heading-background  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('app.airTicketDetails')</h3>
                        </div>
                        <div class="col-md-2 col-2 text-right">
                            {{-- <div class="dropdown">
                                <button
                                    class="btn btn-lg f-14 px-2 py-1 text-dark-grey text-capitalize rounded  dropdown-toggle"
                                    type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-ellipsis-h"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                                    aria-labelledby="dropdownMenuLink" tabindex="0">
                                        <a class="dropdown-item openRightModal"
                                            data-redirect-url="{{ url()->previous() }}"
                                            href="{{ route('air-tickets.edit', $airTicket->id) }}">@lang('app.edit')</a>
                                        <a class="dropdown-item delete-air-ticket" data-air-ticket-id="{{ $airTicket->id }}">@lang('app.delete')</a>

                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @php
                        if ($airTicket->status == 'approved') {
                            $class = 'text-light-green';
                            $status = __('app.approved');
                        } elseif ($airTicket->status == 'pending') {
                            $class = 'text-yellow';
                            $status = __('app.pending');
                        } else {
                            $class = 'text-red';
                            $status = __('app.rejected');
                        }
                        $paidStatus = '<i class="fa fa-circle mr-1 ' . $class . ' f-10"></i> ' . $status;

                        $reject_reason = !is_null($airTicket->reject_reason) ? $airTicket->reject_reason : '--';

                        $approve_reason = !is_null($airTicket->approve_reason) ? $airTicket->approve_reason : '--';
                    @endphp
                    <x-cards.data-row :label="__('app.employee')" :value="$airTicket->employee?->name" />
                    <x-cards.data-row :label="__('modules.insurance.issue_date')" :value="$airTicket->date ? \Carbon\Carbon::parse($airTicket->date)->format(company()->date_format) : '-'" />
                    {{-- <x-cards.data-row :label="__('app.status')" :value="($airTicket->status)" /> --}}
                    <x-cards.data-row :label="__('app.status')" :value="$paidStatus" html="true" />
                    @if ($airTicket->status == 'rejected')
                        <x-cards.data-row :label="__('messages.reasonForAirTicketRejection')" :value="$reject_reason"
                            html="true" />
                    @endif

                    @if ($airTicket->status == 'approved')
                        <x-cards.data-row :label="__('messages.reasonForAirTicketApproval')" :value="$approve_reason"
                            html="true" />
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.delete-air-ticket', function() {
        var id = $(this).data('air-ticket-id');
        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            text: "@lang('messages.recoverRecord')",
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: "@lang('messages.confirmDelete')",
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            showClass: {
                popup: 'swal2-noanimation',
                backdrop: 'swal2-noanimation'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('air-tickets.destroy', ':id') }}";
                url = url.replace(':id', id);

                var token = "{{ csrf_token() }}";

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': token,
                        '_method': 'DELETE'
                    },
                    success: function(response) {
                        if (response.status == "success") {
                            showTable();
                            window.location.href = response.redirectUrl
                        }
                    }
                });
            }
        });
    });
</script>
