<style>
    .nav {
        /* background-color: white; */
        /* border-bottom: 1px solid gray !important; */
        background-color: @if(!user()->dark_theme)
            #ffffff;
        @else
            #181c34;
        @endif
    }

    .nav>.nav-item {
        border: 1px solid #99A5B5;
        border-radius: 0.25rem;
    }

    .nav-link.active {
        background: @if(!user()->dark_theme)
            radial-gradient(circle at 14% 18%, rgba(217, 119, 6, 0.16), transparent 22%),
            radial-gradient(circle at 84% 20%, rgba(5, 150, 105, 0.26), transparent 24%),
            radial-gradient(circle at 70% 82%, rgba(6, 182, 212, 0.18), transparent 22%),
            linear-gradient(135deg, #010e09 0%, #021810 38%, #031f14 100%);
        @else
            #ffffff;
        @endif
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
        1 => 'January',
        2 => 'February',
        3 => 'March',
        4 => 'April',
        5 => 'May',
        6 => 'June',
        7 => 'July',
        8 => 'August',
        9 => 'September',
        10 => 'October',
        11 => 'November',
        12 => 'December',
    ];
    $yearOptions = range($currentYear, max($currentYear - 10, 2000), -1);
@endphp
@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center form-heading-background">
            <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize">
                Add Employee Salary Slip</h4>
            {{-- <a href="{{ route('payroll.index', ['tab' => 'salary-slips']) }}" class="btn btn-primary rounded f-14 p-2 mb-2">
                Cancel
            </a> --}}
        </div>

        <form method="POST" action="{{ route('payroll.salary-slips.store') }}" id="save-salary-form">
            @csrf
            {{-- Hidden fields required by your backend logic --}}
            <input type="hidden" name="payee_type" value="employee">
            <input type="hidden" name="employee_id" id="payee_user_id">

            {{-- TABS --}}
            <ul class="nav nav-tabs border-bottom-0 p-4" id="payrollTabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active f-15 f-w-500 text-dark" id="tab-working-days-link" data-toggle="tab"
                        href="#tab-working-days" role="tab">
                        <i class="bi bi-calendar-check mr-1"></i> Working Days
                    </a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link f-15 f-w-500 text-dark" id="tab-allowances-link" data-toggle="tab"
                        href="#tab-allowances" role="tab">
                        <i class="bi bi-plus-circle mr-1"></i> Allowances
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link f-15 f-w-500 text-dark" id="tab-deductions-link" data-toggle="tab"
                        href="#tab-deductions" role="tab">
                        <i class="bi bi-dash-circle mr-1"></i> Deductions
                    </a>
                </li>
                <li class="nav-item ml-2">
                    <a class="nav-link f-15 f-w-500 text-dark" id="tab-final-salary-link" data-toggle="tab"
                        href="#tab-final-salary" role="tab">
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
                                    <select name="employee_id" id="employee_id" class="form-control select-picker height-35"
                                        data-size="8" data-live-search="true" required>
                                        <option value="">-- Choose Employee --</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            {{-- <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Salary Group</label>
                                    <select name="salary_group_id" class="form-control select-picker height-35"
                                        data-size="8" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($allGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div> --}}
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Pay Days</label>
                                    <input type="number" min="0" max="31" name="pay_days" id="pay_days"
                                        class="form-control height-35" data-size="8" placeholder="e.g. 30">
                                </div>
                            </div>
                            <div class="col-md-4 ">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Month</label>
                                    <select name="month" id="salary_month" class="form-control height-35" data-size="8"
                                        required>
                                        @foreach ($monthOptions as $num => $label)
                                            <option value="{{ str_pad($num, 2, '0', STR_PAD_LEFT) }}"
                                                {{ $num == $currentMonth ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Year</label>
                                    <select name="year" id="salary_year" class="form-control height-35" data-size="8"
                                        required>
                                        @foreach ($yearOptions as $yr)
                                            <option value="{{ $yr }}"
                                                {{ $yr == $currentYear ? 'selected' : '' }}>{{ $yr }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Salary From</label>
                                    <input type="date" id="salary_from" name="salary_from"
                                        class="form-control height-35" data-size="8" readonly>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Salary To</label>
                                    <input type="date" id="salary_to" name="salary_to" class="form-control height-35"
                                        data-size="8">
                                </div>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-primary btn-next" data-next="#tab-allowances-link">Next
                                <i class="bi bi-arrow-right"></i></button>
                        </div>
                    </div>
                </div>

                {{-- 2. Allowances Tab --}}
                <div class="tab-pane fade" id="tab-allowances" role="tabpanel">
                    <div class="rounded bg-white p-4 shadow-sm">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Basic Salary</label>
                                    <input type="number" step="0.01" min="0" name="basic_salary"
                                        class="form-control height-35" data-size="8" required value="0">
                                </div>
                            </div>
                            <div class="col-md-6 d-none">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Expense Claims</label>
                                    <input type="number" step="0.01" min="0" name="expense_claims"
                                        class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2" id="employee-allowances-list">
                            <div class="col-md-12">
                                <span class="text-muted f-13">Select an employee to load allowances.</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-primary btn-prev"
                                data-prev="#tab-working-days-link">Previous</button>
                            <button type="button" class="btn btn-primary btn-next" data-next="#tab-deductions-link">Next
                                <i class="bi bi-arrow-right"></i></button>
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
                                    <input type="number" step="0.01" min="0" name="total_deductions"
                                        class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">TDS</label>
                                    <input type="number" step="0.01" min="0" name="tds"
                                        class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                        </div>
                        {{-- inside #tab-deductions, after the TDS row --}}
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <label class="f-14 f-w-500">Advance Salary Deductions</label>
                                <div id="advance-salary-list" class="border rounded p-2">
                                    <span class="text-muted f-13">Select an employee to load pending advances.</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <button type="button" class="btn btn-primary btn-prev"
                                data-prev="#tab-allowances-link">Previous</button>
                            <button type="button" class="btn btn-primary btn-next"
                                data-next="#tab-final-salary-link">Next <i class="bi bi-arrow-right"></i></button>
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
                                    <input type="number" step="0.01" name="monthly_salary"
                                        class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Gross Salary</label>
                                    <input type="number" step="0.01" name="gross_salary"
                                        class="form-control height-35" data-size="8" value="0">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label class="f-14 f-w-500 text-success">Net Salary</label>
                                    <input type="number" step="0.01" name="net_salary"
                                        class="form-control height-35" data-size="8" required value="0">
                                </div>
                            </div>

                            <div class="col-md-4 mt-2">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Paid Amount</label>
                                    <input type="number" step="0.01" name="paid_amount"
                                        class="form-control height-35" data-size="8" value="0">
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
                                    <select name="payroll_cycle_id" class="form-control select-picker height-35"
                                        data-size="8">
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
                                    <select name="salary_payment_method_id" class="form-control select-picker height-35"
                                        data-size="8" id="salary_payment_method_id">
                                        <option value="">--</option>
                                        @foreach ($allPaymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->payment_method }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <input type="hidden" name="payment_type_name" id="payment_type_name" value="bank">
                            {{-- NEW: Bank Account field --}}
                            <div class="col-md-6 mt-2" id="employee_bank">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">Bank Account</label>
                                    <select name="employee_bank_account_id" id="employee_bank_account_id"
                                        class="form-control select-picker height-35" data-size="8">
                                        <option value="">-- Select Employee First --</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-primary btn-prev"
                                data-prev="#tab-deductions-link">Previous</button>
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

            const bankAccountSelect = $('#employee_bank_account_id');
            const bankAccountsUrl = "{{ url('account/payroll/employees') }}"; // base URL, employee id appended below

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
                            showClass: {
                                popup: 'swal2-noanimation',
                                backdrop: 'swal2-noanimation'
                            },
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

            $('.btn-prev').click(function() {
                $($(this).data('prev')).tab('show');
            });

            // 2. Sync hidden employee_id
            employeeId.on('change', function() {
                const empId = $(this).val();
                payeeUserId.val(empId);

                // Reset bank account dropdown
                bankAccountSelect.empty();

                if (!empId) {
                    bankAccountSelect.append('<option value="">-- Select Employee First --</option>');
                    bankAccountSelect.selectpicker('refresh');
                    return;
                }

                bankAccountSelect.append('<option value="">-- Loading... --</option>');
                bankAccountSelect.selectpicker('refresh');

                $.get(`${bankAccountsUrl}/${empId}/bank-accounts`, function(accounts) {
                    bankAccountSelect.empty();

                    if (!accounts.length) {
                        bankAccountSelect.append('<option value="">No bank accounts found</option>');
                    } else {
                        bankAccountSelect.append('<option value="">--</option>');
                        accounts.forEach(function(acc) {
                            let label = acc.bank_name || 'Unnamed Bank';
                            if (acc.account_number) label += ' - ' + acc.account_number;
                            if (acc.is_main_account) label += ' (Main)';

                            const opt = $('<option></option>')
                                .val(acc.id)
                                .text(label);

                            if (acc.is_main_account) opt.prop('selected', true);

                            bankAccountSelect.append(opt);
                        });
                    }

                    bankAccountSelect.selectpicker('refresh');
                }).fail(function() {
                    bankAccountSelect.empty().append('<option value="">Failed to load accounts</option>');
                    bankAccountSelect.selectpicker('refresh');
                });
            });

            const advanceListUrl = "{{ url('account/payroll/employees') }}";
            const advanceListContainer = $('#advance-salary-list');

            function loadPendingAdvances(empId) {
                if (!empId) {
                    advanceListContainer.html('<span class="text-muted f-13">Select an employee to load pending advances.</span>');
                    return;
                }

                advanceListContainer.html('<span class="text-muted f-13">Loading...</span>');

                $.get(`${advanceListUrl}/${empId}/pending-advances`, function (advances) {
                    if (!advances.length) {
                        advanceListContainer.html('<span class="text-muted f-13">No pending advances.</span>');
                        return;
                    }

                    let html = '';
                    advances.forEach(function (adv) {
                        html += `
                            <div class="d-flex align-items-center mb-2 advance-row" data-id="${adv.id}" data-balance="${adv.balance}">
                                <div class="custom-control custom-checkbox mr-2">
                                    <input type="checkbox" class="custom-control-input advance-check" id="adv-${adv.id}">
                                    <label class="custom-control-label f-13" for="adv-${adv.id}">
                                        ${adv.date} — Balance: ${adv.balance.toFixed(2)}
                                    </label>
                                </div>
                                <input type="number" class="form-control form-control-sm advance-amount ml-auto"
                                    style="width:120px" min="0" max="${adv.balance}" step="0.01"
                                    value="${adv.balance}" disabled>
                            </div>`;
                    });

                    advanceListContainer.html(html);
                }).fail(function () {
                    advanceListContainer.html('<span class="text-danger f-13">Failed to load advances.</span>');
                });
            }

            // hook into the employee change handler you already have
            employeeId.on('change', function () {
                loadPendingAdvances($(this).val());
            });

            // enable/disable amount input alongside checkbox, cap at balance
            $(document).on('change', '.advance-check', function () {
                const row = $(this).closest('.advance-row');
                const amountInput = row.find('.advance-amount');
                amountInput.prop('disabled', !this.checked);
                recalcNetSalary();
            });

            $(document).on('input', '.advance-amount', function () {
                const row = $(this).closest('.advance-row');
                const balance = parseFloat(row.data('balance'));
                let val = parseFloat($(this).val()) || 0;
                if (val > balance) $(this).val(balance);
                if (val < 0) $(this).val(0);
                recalcNetSalary();
            });

            function totalAdvanceDeduction() {
                let total = 0;
                $('.advance-row').each(function () {
                    if ($(this).find('.advance-check').is(':checked')) {
                        total += parseFloat($(this).find('.advance-amount').val()) || 0;
                    }
                });
                return total;
            }

            const allowancesUrl = "{{ url('account/payroll/employees') }}";
            const allowancesContainer = $('#employee-allowances-list');

            function loadEmployeeAllowances(empId) {
                if (!empId) {
                    allowancesContainer.html('<div class="col-md-12"><span class="text-muted f-13">Select an employee to load allowances.</span></div>');
                    recalcNetSalary();
                    return;
                }

                allowancesContainer.html('<div class="col-md-12"><span class="text-muted f-13">Loading...</span></div>');

                $.get(`${allowancesUrl}/${empId}/allowances`, function (allowances) {
                    if (!allowances.length) {
                        allowancesContainer.html('<div class="col-md-12"><span class="text-muted f-13">No allowances found.</span></div>');
                        recalcNetSalary();
                        return;
                    }

                    let html = '';
                    allowances.forEach(function (a) {
                        html += `
                            <div class="col-md-6 mt-2 allowance-row" data-id="${a.id}">
                                <div class="form-group">
                                    <label class="f-14 f-w-500">${a.name}</label>
                                    <input type="number" step="0.01" min="0"
                                        name="allowances[${a.id}]"
                                        class="form-control height-35 allowance-amount"
                                        value="${a.amount}" readonly>
                                </div>
                            </div>`;
                    });

                    allowancesContainer.html(html);
                    recalcNetSalary();
                }).fail(function () {
                    allowancesContainer.html('<div class="col-md-12"><span class="text-danger f-13">Failed to load allowances.</span></div>');
                });
            }

            employeeId.on('change', function () {
                loadEmployeeAllowances($(this).val());
            });

            function totalAllowances() {
                let total = 0;
                $('.allowance-amount').each(function () {
                    total += parseFloat($(this).val()) || 0;
                });
                return total;
            }

            function recalcNetSalary() {
                const basic = parseFloat($('[name="basic_salary"]').val()) || 0;
                const expenseClaims = parseFloat($('[name="expense_claims"]').val()) || 0;
                const allowances = totalAllowances();
                const otherDeductions = parseFloat($('[name="total_deductions"]').val()) || 0;
                const tds = parseFloat($('[name="tds"]').val()) || 0;
                const advanceDeduction = totalAdvanceDeduction();

                const gross = basic + expenseClaims + allowances;
                const net = gross - otherDeductions - tds - advanceDeduction;

                $('[name="gross_salary"]').val(gross.toFixed(2));
                $('[name="net_salary"]').val(net > 0 ? net.toFixed(2) : 0);
            }

            // 3. Date Logic (Salary From/To)
            const syncSalaryPeriod = function() {
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

            $(document).on('change', '#salary_payment_method_id', function () {
                let paymentMethodId = $(this).val();
                let paymentMethodName = $(this).find(':selected').text().toLowerCase();

                if(paymentMethodName == 'cash'){
                    $('#employee_bank').addClass('d-none');
                } else{
                    $('#employee_bank').removeClass('d-none');
                }

                $('#payment_type_name').val(paymentMethodName);

            });

            $('#save-salary-form').on('submit', function () {
                $('input[name^="advance_deductions"]').remove(); // clear stale ones

                $('.advance-row').each(function () {
                    if ($(this).find('.advance-check').is(':checked')) {
                        const id = $(this).data('id');
                        const amount = $(this).find('.advance-amount').val();
                        $(this).closest('form').append(
                            `<input type="hidden" name="advance_deductions[${id}]" value="${amount}">`
                        );
                    }
                });

                // No allowance handling needed anymore — inputs submit natively as allowances[id]
            });
        });
    </script>
@endpush
