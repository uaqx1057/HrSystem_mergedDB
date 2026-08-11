@php
    $editDepartmentPermission = user()->permission('edit_employees');
    $deleteDepartmentPermission = user()->permission('delete_employees');
@endphp

<div id="department-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div
                    class="card-header form-heading-background  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">@lang('app.employeeDetail')</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="text-right d-flex justify-content-end">

                        <a href="{{ route('employees.index') . '?tab=pending-termination' }}"
                            class="btn btn-sm btn-primary">Back</a>
                        @if (($canManageTermination ?? false) && $termination && $termination->isFullyCleared())
                            <a href="javascript:;" data-id="{{ $employee->id }}"
                                class="btn btn-sm btn-primary complete-termination-btn ml-2">
                                <i class="fa fa-user-times mr-2"></i> Complete Terminate
                            </a>
                        @endif
                    </div>
                    <x-cards.data-row :label="__('modules.employees.employeeId')" :value="$employee->employeeDetail->employee_id ?? '--'" />

                    <x-cards.data-row :label="__('modules.employees.fullName')" :value="$employee->name" />

                    <x-cards.data-row :label="__('app.designation')" :value="$employee->employeeDetail->designation->name ?? '--'" />

                    <x-cards.data-row :label="__('app.department')" :value="$employee->employeeDetail->department->team_name ?? '--'" />

                    <x-cards.data-row :label="__('app.branchName')" :value="$employee->branch->name ?? '--'" />

                    <x-cards.data-row :label="__('modules.employees.gender')" :value="$employee->gender ?? '--'" />

                    @php
                        $currentyearJoiningDate = \Carbon\Carbon::parse(
                            now(company()->timezone)->year .
                                '-' .
                                $employee->employeeDetail->joining_date->translatedFormat('m-d'),
                        );
                        if ($currentyearJoiningDate->copy()->endOfDay()->isPast()) {
                            $currentyearJoiningDate = $currentyearJoiningDate->addYear();
                        }
                        $diffInHoursJoiningDate = now(company()->timezone)->floatDiffInHours(
                            $currentyearJoiningDate,
                            false,
                        );
                    @endphp

                    <x-cards.data-row :label="__('modules.employees.workAnniversary')" :value="!is_null($employee->employeeDetail) && !is_null($employee->employeeDetail->joining_date)
                        ? ($diffInHoursJoiningDate > -23 && $diffInHoursJoiningDate <= 0
                            ? __('app.today')
                            : $currentyearJoiningDate->longRelativeToNowDiffForHumans())
                        : '--'" />

                    <x-cards.data-row :label="__('modules.employees.dateOfBirth')" :value="$employee->employeeDetail->date_of_birth
                        ? $employee->employeeDetail->date_of_birth->translatedFormat('d F')
                        : '--'" />

                    <x-cards.data-row :label="__('app.email')" :value="$employee->email" />

                    <x-cards.data-row :label="__('app.mobile')" :value="$employee->mobile_with_phonecode" />

                    <x-cards.data-row :label="__('modules.employees.linkedinUsername')" :value="$employee->employeeDetail->slack_username
                        ? '@' . $employee->employeeDetail->slack_username
                        : '--'" />

                    <x-cards.data-row :label="__('modules.employees.hourlyRate')" :value="company()->currency->currency_symbol . ($employee->employeeDetail->hourly_rate ?? '0')" />

                    <x-cards.data-row :label="__('app.address')" :value="$employee->employeeDetail->address ?? '--'" />

                    <x-cards.data-row :label="__('app.language')" :value="$employeeLanguage->language_name ?? '--'" />

                    <x-cards.data-row :label="__('modules.employees.joiningDate')" :value="$employee->employeeDetail->joining_date
                        ? $employee->employeeDetail->joining_date->translatedFormat(company()->date_format)
                        : '--'" />

                    <x-cards.data-row :label="__('modules.employees.probationEndDate')" :value="$employee->employeeDetail->probation_end_date
                        ? \Carbon\Carbon::parse($employee->employeeDetail->probation_end_date)->translatedFormat(
                            company()->date_format,
                        )
                        : '--'" />

                    {{-- <x-cards.data-row  :label="__('modules.employees.noticePeriodStartDate')"
                                    :value="$employee->employeeDetail->notice_period_start_date
                                        ? \Carbon\Carbon::parse($employee->employeeDetail->notice_period_start_date)->translatedFormat(company()->date_format)
                                        : '--'" />

                                <x-cards.data-row  :label="__('modules.employees.noticePeriodEndDate')"
                                    :value="$employee->employeeDetail->notice_period_end_date
                                        ? \Carbon\Carbon::parse($employee->employeeDetail->notice_period_end_date)->translatedFormat(company()->date_format)
                                        : '--'" /> --}}



                    <x-cards.data-row :label="__('modules.employees.employmentType')" :value="$employee->employeeDetail->employment_type
                        ? __('modules.employees.' . $employee->employeeDetail->employment_type)
                        : '--'" />
                    <x-cards.data-row :label="__('modules.employees.basic_salary')" :value="$employee->employeeDetail->basic_salary
                        ? __($employee->employeeDetail->basic_salary)
                        : '--'" />
                    <x-cards.data-row :label="__('modules.employees.vehicle_allocation')" :value="$employee->employeeDetail->vehicle_allocation
                        ? __($employee->employeeDetail->vehicle_allocation)
                        : '--'" />

                    @if ($employee->employeeDetail->employment_type == 'internship')
                        <x-cards.data-row :label="__('modules.employees.internshipEndDate')" :value="$employee->employeeDetail->internship_end_date
                            ? \Carbon\Carbon::parse($employee->employeeDetail->internship_end_date)->translatedFormat(
                                company()->date_format,
                            )
                            : '--'" />
                    @endif

                    @if ($employee->employeeDetail->employment_type == 'on_contract')
                        <x-cards.data-row :label="__('modules.employees.contractEndDate')" :value="$employee->employeeDetail->contract_end_date
                            ? \Carbon\Carbon::parse($employee->employeeDetail->contract_end_date)->translatedFormat(
                                company()->date_format,
                            )
                            : '--'" />
                    @endif
                    {{-- Custom fields data --}}
                    <x-forms.custom-field-show :fields="$fields" :model="$employee->employeeDetail"></x-forms.custom-field-show>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mt-3">
                <div
                    class="card-header form-heading-background  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-8 col-8">
                            <h3 class="heading-h1">IT Clearance - @lang('app.assignment')</h3>
                        </div>
                        <div class="col-md-4 col-4 text-right">
                            @if ($termination && $termination->it_clearance_status == 'issued')
                                <span class="badge badge-success p-2">Clearance Issued</span>
                                <a href="{{ route('employees.it-clearance.letter', $employee->id) }}" class="btn btn-sm btn-primary ml-2">View Letter</a>
                            @else
                                <span class="badge badge-warning p-2">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Asset Name</th>
                                    <th>Qty</th>
                                    <th>Assign Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assignedAssets ?? [] as $assignment)
                                    <tr>
                                        <td>{{ $assignment->asset->name ?? '--' }}</td>
                                        <td>{{ $assignment->qty }}</td>
                                        <td>{{ $assignment->created_at ? $assignment->created_at->translatedFormat(company()->date_format) : '--' }}</td>
                                        <td>{{ $assignment->status }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">All assets returned / no assets assigned.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card bg-white border-0 b-shadow-4 mt-3">
                <div
                    class="card-header form-heading-background  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-8 col-8">
                            <h3 class="heading-h1">Finance Clearance - Salaries Detail</h3>
                        </div>
                        <div class="col-md-4 col-4 text-right">
                            @if ($termination && $termination->finance_clearance_status == 'issued')
                                <span class="badge badge-success p-2">Clearance Issued</span>
                                <a href="{{ route('employees.finance-clearance.letter', $employee->id) }}" class="btn btn-sm btn-primary ml-2">View Letter</a>
                            @else
                                <span class="badge badge-warning p-2">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <h4 class="heading-h4 mb-0">Advance Salary / Pending Dues</h4>
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
                                @forelse ($pendingAdvances ?? [] as $advance)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($advance->date)->translatedFormat(company()->date_format) }}
                                        <td>{{ company()->currency->currency_symbol ?? '' }}{{ number_format($advance->advance_salary - $advance->deducted_amount, 2) }}</td>
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

                    <h4 class="heading-h4 mb-0">Asset Loss Deductions</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Company Asset</th>
                                    <th>Serial No</th>
                                    <th>Loss Amount</th>
                                    <th>Deducted Amount</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assetDeductions as $deduction)
                                    <tr>
                                        <td>{{ optional($deduction->companyAsset)->name }}</td>
                                        <td>{{ optional($deduction->assetLoss)->serial_no }}</td>
                                        <td>{{ $deduction->loss_amount }}</td>
                                        <td>{{ $deduction->deducted_amount }}</td>
                                        <td>{{ $deduction->loss_amount - $deduction->deducted_amount }}</td>
                                        <td class="{{ $deduction->status == 'Deducted' ? 'text-success' : 'text-warning' }}">
                                            <strong>{{ ucfirst($deduction->status) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">Not Available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').on('click', '.complete-termination-btn', function() {
        var id = $(this).data('id');

        Swal.fire({
            title: "@lang('messages.sweetAlertTitle')",
            html:
                '<div class="form-group text-left">' +
                    '<label for="notice_period_start_date">Notice Period Start</label>' +
                    '<input id="notice_period_start_date" type="date" class="form-control" />' +
                '</div>' +
                '<div class="form-group text-left">' +
                    '<label for="notice_period_end_date">Notice Period End</label>' +
                    '<input id="notice_period_end_date" type="date" class="form-control" />' +
                '</div>',
            icon: 'warning',
            showCancelButton: true,
            focusConfirm: false,
            confirmButtonText: 'Submit',
            cancelButtonText: "@lang('app.cancel')",
            customClass: {
                confirmButton: 'btn btn-primary mr-3',
                cancelButton: 'btn btn-secondary'
            },
            buttonsStyling: false,
            preConfirm: function() {
                var startDate = document.getElementById('notice_period_start_date').value;
                var endDate = document.getElementById('notice_period_end_date').value;

                if (!startDate || !endDate) {
                    Swal.showValidationMessage('Both notice period dates are required.');
                    return false;
                }

                if (new Date(startDate) > new Date(endDate)) {
                    Swal.showValidationMessage('Notice period end date must be the same or after the start date.');
                    return false;
                }

                return {
                    notice_period_start_date: startDate,
                    notice_period_end_date: endDate
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                var url = "{{ route('employees.complete-termination', ':id') }}".replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    blockUI: true,
                    data: {
                        '_token': "{{ csrf_token() }}",
                        notice_period_start_date: result.value.notice_period_start_date,
                        notice_period_end_date: result.value.notice_period_end_date
                    },
                    success: function(response) {
                        if (response.status == 'success') {
                            window.location.href = "{{ route('employees.index') }}?tab=terminated";
                        }
                    }
                });
            }
        });
    });
</script>
