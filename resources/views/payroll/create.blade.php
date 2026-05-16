<style>

    .nav{
        background-color: white;
        border-bottom: 1px solid gray !important;
    }
    .nav > .nav-item{
        border: 1px solid #99A5B5;
        border-radius: 0.25rem;
    }

    .nav-link.active{
        background-color: #722C81 !important;
        color: white !important;
    }

    .nav-tabs .nav-link:focus {
        color: white !important;
    }

    /* Disable manual clicking on tabs */
    .nav-tabs .nav-link {
        pointer-events: none;
        cursor: default;
    }
</style>
@extends('layouts.app')

@php
    $currentDate = \Carbon\Carbon::now();
    $currentYear = (int) $currentDate->year;
    $currentMonth = (int) $currentDate->month;

    $monthOptions = [
        1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
        5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
        9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December',
    ];
    $yearOptions = range($currentYear, max($currentYear - 10, 2000), -1);
@endphp
@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center">
            <h4 class="mb-4">Add Employee Salary Slip</h4>
            <a href="{{ route('payroll.index', ['tab' => 'salary-slips']) }}" class="btn btn-primary rounded f-14 p-2 mb-2">
                Cancel
            </a>
        </div>

        <form method="POST" action="{{ route('payroll.salary-slips.store') }}" id="save-salary-form">
            @csrf
            {{-- Hidden fields required by your backend logic --}}
            <input type="hidden" name="payee_type" value="employee">
            <input type="hidden" name="employee_id" id="payee_user_id">

            {{-- TABS --}}
            <ul class="nav nav-tabs border-bottom-0 p-4" id="payrollTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active f-15 f-w-500 text-dark" id="tab-working-days-link" data-toggle="tab" href="#tab-working-days" role="tab">
                        <i class="bi bi-calendar-check mr-1"></i> Working Days
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link f-15 f-w-500 text-dark" id="tab-allowances-link" data-toggle="tab" href="#tab-allowances" role="tab">
                        <i class="bi bi-plus-circle mr-1"></i> Allowances
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link f-15 f-w-500 text-dark" id="tab-deductions-link" data-toggle="tab" href="#tab-deductions" role="tab">
                        <i class="bi bi-dash-circle mr-1"></i> Deductions
                    </a>
                </li>
                <li class="nav-item ml-2">
                    <a class="nav-link f-15 f-w-500 text-dark" id="tab-final-salary-link" data-toggle="tab" href="#tab-final-salary" role="tab">
                        <i class="bi bi-cash-stack mr-1"></i> Final Salary
                    </a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                {{-- 1. Working Days Tab --}}
                <div class="tab-pane fade show active" id="tab-working-days" role="tabpanel">
                    <div class="rounded bg-white p-4 shadow-sm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Employee</label>
                                    <select name="employee_id" id="employee_id" class="form-control select-picker height-35" data-size="8" data-live-search="true" required>
                                        <option value="">-- Choose Employee --</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Salary Group</label>
                                    <select name="salary_group_id" class="form-control select-picker height-35" data-size="8" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($allGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Pay Days</label>
                                    <input type="number" min="0" max="31" name="pay_days" id="pay_days" class="form-control height-35" data-size="8" placeholder="e.g. 30">
                                </div>
                            </div>
                            <div class="col-md-3 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Month</label>
                                    <select name="month" id="salary_month" class="form-control height-35" data-size="8" required>
                                        @foreach ($monthOptions as $num => $label)
                                            <option value="{{ str_pad($num, 2, '0', STR_PAD_LEFT) }}" {{ $num == $currentMonth ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Year</label>
                                    <select name="year" id="salary_year" class="form-control height-35" data-size="8" required>
                                        @foreach ($yearOptions as $yr)
                                            <option value="{{ $yr }}" {{ $yr == $currentYear ? 'selected' : '' }}>{{ $yr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Salary From</label>
                                    <input type="date" id="salary_from" name="salary_from" class="form-control height-35" data-size="8" readonly>
                                </div>
                            </div>
                            <div class="col-md-3 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Salary To</label>
                                    <input type="date" id="salary_to" name="salary_to" class="form-control height-35" data-size="8" >
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-primary btn-next" data-next="#tab-allowances-link">Next <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>

                {{-- 2. Allowances Tab --}}
                <div class="tab-pane fade" id="tab-allowances" role="tabpanel">
                    <div class="rounded bg-white p-4 shadow-sm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Basic Salary</label>
                                    <input type="number" step="0.01" min="0" name="basic_salary" class="form-control height-35" data-size="8" required value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Expense Claims</label>
                                    <input type="number" step="0.01" min="0" name="expense_claims" class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-primary btn-prev" data-prev="#tab-working-days-link">Previous</button>
                            <button type="button" class="btn btn-primary btn-next" data-next="#tab-deductions-link">Next <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>

                {{-- 3. Deductions Tab --}}
                <div class="tab-pane fade" id="tab-deductions" role="tabpanel">
                    <div class="rounded bg-white p-4 shadow-sm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Total Deductions</label>
                                    <input type="number" step="0.01" min="0" name="total_deductions" class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">TDS</label>
                                    <input type="number" step="0.01" min="0" name="tds" class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-primary btn-prev" data-prev="#tab-allowances-link">Previous</button>
                            <button type="button" class="btn btn-primary btn-next" data-next="#tab-final-salary-link">Next <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>

                {{-- 4. Final Salary Tab --}}
                <div class="tab-pane fade" id="tab-final-salary" role="tabpanel">
                    <div class="rounded bg-white p-4 shadow-sm">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Monthly Salary</label>
                                    <input type="number" step="0.01" name="monthly_salary" class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Gross Salary</label>
                                    <input type="number" step="0.01" name="gross_salary" class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500 text-success">Net Salary</label>
                                    <input type="number" step="0.01" name="net_salary" class="form-control height-35" data-size="8" required value="0">
                                </div>
                            </div>

                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Paid Amount</label>
                                    <input type="number" step="0.01" name="paid_amount" class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Paid On</label>
                                    <input type="date" name="paid_on" class="form-control height-35" data-size="8">
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Status</label>
                                    <select name="status" class="form-control height-35" data-size="8">
                                        <option value="generated">Generated</option>
                                        <option value="review">Review</option>
                                        <option value="locked">Locked</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Payroll Cycle</label>
                                    <select name="payroll_cycle_id" class="form-control select-picker height-35" data-size="8">
                                        <option value="">--</option>
                                        @foreach ($allCycles as $cycle)
                                            <option value="{{ $cycle->id }}">{{ $cycle->cycle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Payment Method</label>
                                    <select name="salary_payment_method_id" class="form-control select-picker height-35" data-size="8">
                                        <option value="">--</option>
                                        @foreach ($allPaymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->payment_method }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-primary btn-prev" data-prev="#tab-deductions-link">Previous</button>
                            <button type="submit" class="btn btn-success" id="save-form">
                                <i class="bi bi-check-circle mr-1"></i> Save Salary Slip
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            const employeeId = $('#employee_id');
            const payeeUserId = $('#payee_user_id');
            const salaryMonth = $('#salary_month');
            const salaryYear = $('#salary_year');
            const salaryFrom = $('#salary_from');
            const salaryTo = $('#salary_to');

            // 1. Step Navigation logic
            $('.btn-next').click(function() {
                let nextTabLink = $(this).data('next');
                let currentTab = $(this).closest('.tab-pane');

                if (currentTab.attr('id') === 'tab-working-days') {
                    let empId = $('#employee_id').val();
                    let workingDays = $('#pay_days').val();
                    let isValid = true;

                    // Reset previous error states
                    $('#employee_id').parent().find('.dropdown-toggle').css('border-color', '');
                    $('#pay_days').removeClass('is-invalid');

                    if (empId === "") {
                        // For select-picker, we often need to highlight the toggle button
                        $('#employee_id').parent().find('.dropdown-toggle').css('border-color', '#ea5455');
                        isValid = false;
                    }

                    if (workingDays === "" || workingDays > 31) {
                        $('#pay_days').addClass('is-invalid');
                        isValid = false;
                    }

                    if (!isValid) {
                        // alert("Please select a driver and enter working days before proceeding.");
                        Swal.fire({
                            icon: 'error',
                            text: 'Employee and working days required and working days less than 32.',
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                            showClass: { popup: 'swal2-noanimation', backdrop: 'swal2-noanimation' },
                        });
                        return false; // Stop the tab from switching
                    }
                }
                $(nextTabLink).tab('show');
            });

            // Remove error highlight when user starts typing/selecting
            $('#employee_id').on('change', function() {
                $(this).parent().find('.dropdown-toggle').css('border-color', '');
            });
            $('#pay_days').on('input', function() {
                $(this).removeClass('is-invalid');
            });

            $('.btn-prev').click(function() { $($(this).data('prev')).tab('show'); });

            // 2. Sync hidden employee_id
            employeeId.on('change', function() {
                payeeUserId.val($(this).val());
            });

            // 3. Date Logic (Salary From/To)
            const syncSalaryPeriod = function () {
                const month = parseInt(salaryMonth.val(), 10);
                const year = parseInt(salaryYear.val(), 10);

                if (isNaN(month) || isNaN(year)) return;

                const startDate = new Date(year, month - 1, 1);
                const endDate = new Date(year, month, 0);

                const formatDate = (date) => {
                    const yyyy = date.getFullYear();
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const dd = String(date.getDate()).padStart(2, '0');
                    return `${yyyy}-${mm}-${dd}`;
                };

                salaryFrom.val(formatDate(startDate));
                salaryTo.val(formatDate(endDate));
                salaryTo.attr('min', salaryFrom.val());
            };

            salaryMonth.on('change', syncSalaryPeriod);
            salaryYear.on('change', syncSalaryPeriod);

            // Run on load
            syncSalaryPeriod();
        });
    </script>
@endpush
