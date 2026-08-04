@php
    $hasPendingDues = ($pendingAdvances ?? collect())->isNotEmpty();
@endphp

<div id="finance-clearance-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div
                    class="card-header form-heading-background border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">Finance Clearance</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-right d-flex justify-content-end mb-3">
                        <a href="{{ route('employees.index') }}?tab=pending-termination"
                            class="btn btn-sm btn-secondary">Back</a>
                    </div>

                    <x-cards.data-row :label="__('modules.employees.employeeId')" :value="$employee->employeeDetail->employee_id ?? '--'" />
                    <x-cards.data-row :label="__('modules.employees.fullName')" :value="$employee->name" />
                    <x-cards.data-row :label="__('app.email')" :value="$employee->email" />
                    <x-cards.data-row :label="__('app.designation')" :value="$employee->employeeDetail->designation->name ?? '--'" />
                    <x-cards.data-row :label="__('app.department')" :value="$employee->employeeDetail->department->team_name ?? '--'" />

                    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                        <h4 class="heading-h4 mb-0">Advance Salary / Pending Dues</h4>

                        @if ($termination && $termination->finance_clearance_status == 'issued')
                            <span class="badge badge-success p-2">Clearance Issued</span>
                        @else
                            <div>
                                @if ($hasPendingDues)
                                    <a href="javascript:;" class="btn btn-sm btn-warning send-finance-reminder">
                                        <i class="fa fa-bell mr-1"></i> Send Reminder
                                    </a>
                                @endif
                                <a href="javascript:;" class="btn btn-sm btn-primary issue-finance-clearance ml-2">
                                    <i class="fa fa-check mr-1"></i> Issue Clearance
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Advance Salary</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($pendingAdvances as $advance)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($advance->date)->translatedFormat(company()->date_format) }}
                                        </td>
                                        <td>{{ company()->currency->currency_symbol ?? '' }}{{ number_format($advance->advance_salary - $advance->deducted_amount, 2) }}
                                        </td>
                                        <td>{{ ucfirst($advance->status) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">No pending dues found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($termination && $termination->finance_clearance_status == 'issued')
                        <a href="{{ route('employees.finance-clearance.letter', $employee->id) }}"
                            class="btn btn-sm btn-primary mt-2">
                            <i class="fa fa-file-pdf-o mr-1"></i> Download Clearance Letter
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.send-finance-reminder', function() {
        var url = "{{ route('employees.finance-clearance.reminder', $employee->id) }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            blockUI: true,
            data: {
                '_token': "{{ csrf_token() }}"
            }
        });
    });

    $('body').on('click', '.issue-finance-clearance', function() {
        var url = "{{ route('employees.finance-clearance.issue', $employee->id) }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            blockUI: true,
            data: {
                '_token': "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.status == 'success') {
                    window.location.reload();
                }
            }
        });
    });
</script>
