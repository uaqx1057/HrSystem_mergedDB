@php
    $hasPendingAssets = ($assignedAssets ?? collect())->isNotEmpty();
@endphp

<div id="it-clearance-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header form-heading-background border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">IT Clearance</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-right d-flex justify-content-end mb-3">
                        <a href="{{ route('employees.index') }}?tab=pending-termination" class="btn btn-sm btn-secondary">Back</a>
                    </div>

                    <x-cards.data-row :label="__('modules.employees.employeeId')" :value="$employee->employeeDetail->employee_id ?? '--'" />
                    <x-cards.data-row :label="__('modules.employees.fullName')" :value="$employee->name" />
                    <x-cards.data-row :label="__('app.email')" :value="$employee->email" />
                    <x-cards.data-row :label="__('app.designation')" :value="$employee->employeeDetail->designation->name ?? '--'" />
                    <x-cards.data-row :label="__('app.department')" :value="$employee->employeeDetail->department->team_name ?? '--'" />

                    <div class="d-flex justify-content-between align-items-center mt-4 mb-2">
                        <h4 class="heading-h4 mb-0">Assigned Company Assets</h4>

                        @if ($termination && $termination->it_clearance_status == 'issued')
                            <span class="badge badge-success p-2">Clearance Issued</span>
                        @else
                            <div>
                                @if ($hasPendingAssets)
                                    <a href="javascript:;" class="btn btn-sm btn-warning send-it-reminder">
                                        <i class="fa fa-bell mr-1"></i> Send Reminder
                                    </a>
                                @endif
                                <a href="javascript:;" class="btn btn-sm btn-primary issue-it-clearance ml-2">
                                    <i class="fa fa-check mr-1"></i> Issue Clearance
                                </a>
                            </div>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Asset Name</th>
                                    <th>Catalog</th>
                                    <th>Qty</th>
                                    <th>Assign Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assignedAssets as $assignment)
                                    <tr>
                                        <td>{{ $assignment->asset->name ?? '--' }}</td>
                                        <td>{{ $assignment->asset->catalog ?? '--' }}</td>
                                        <td>{{ $assignment->qty }}</td>
                                        <td>{{ $assignment->created_at ? $assignment->created_at->translatedFormat(company()->date_format) : '--' }}</td>
                                        <td>{{ $assignment->status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">All assets returned / no assets assigned.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($termination && $termination->it_clearance_status == 'issued')
                        <a href="{{ route('employees.it-clearance.letter', $employee->id) }}" class="btn btn-sm btn-primary mt-2">
                            <i class="fa fa-file-pdf-o mr-1"></i> Download Clearance Letter
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.send-it-reminder', function() {
        var url = "{{ route('employees.it-clearance.reminder', $employee->id) }}";

        $.easyAjax({
            type: 'POST',
            url: url,
            blockUI: true,
            data: {
                '_token': "{{ csrf_token() }}"
            }
        });
    });

    $('body').on('click', '.issue-it-clearance', function() {
        var url = "{{ route('employees.it-clearance.issue', $employee->id) }}";

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
