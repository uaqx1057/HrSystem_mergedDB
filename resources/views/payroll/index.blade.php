@extends('layouts.app')

@php
    $addPayrollPermission = user()->permission('add_payroll');
    $editPayrollPermission = user()->permission('edit_payroll');
    $deletePayrollPermission = user()->permission('delete_payroll');
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

@section('filter-section')
    <!-- FILTER START -->
    <div class="d-flex d-lg-block filter-box project-header bg-white">
        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>

        <div class="project-menu" id="mob-client-detail">
            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>

            <nav class="tabs">
                <ul class="-primary">
                    @if (user()->permission('view_payroll') !== 'none')
                        <li>
                            <x-tab :href="route('payroll.index', ['tab' => 'salary-slips'])"
                                :text="__('Salary Slips')"
                                class="salary-slips" ajax="false" />
                        </li>
                    @endif

                    @if (in_array(user()->permission('manage_salary_group'), ['all']))
                    <li>
                        <x-tab :href="route('payroll.index', ['tab' => 'salary-groups'])"
                            :text="__('Salary Groups')"
                            class="salary-groups" ajax="false" />
                    </li>
                    @endif

                    @if (in_array(user()->permission('manage_salary_component'), ['all']))
                    <li>
                        <x-tab :href="route('payroll.index', ['tab' => 'salary-components'])"
                            :text="__('Salary Components')"
                            class="salary-components" ajax="false" />
                    </li>
                    @endif

                    @if (in_array(user()->permission('manage_employee_salary'), ['all', 'branch']))
                    <li>
                        <x-tab :href="route('payroll.index', ['tab' => 'salary-setups'])"
                            :text="__('Salary Setups')"
                            class="salary-setups" ajax="false" />
                    </li>
                    @endif

                    @if (in_array($addPayrollPermission, ['all']))
                    <li>
                        <x-tab :href="route('payroll.index', ['tab' => 'payroll-cycles'])"
                            :text="__('Payroll Cycles')"
                            class="payroll-cycles" ajax="false" />
                    </li>
                    @endif

                    @if (in_array(user()->permission('manage_salary_payment_method'), ['all']))
                    <li>
                        <x-tab :href="route('payroll.index', ['tab' => 'payment-methods'])"
                            :text="__('Payment Methods')"
                            class="payment-methods" ajax="false" />
                    </li>
                    @endif

                    @if (in_array(user()->permission('manage_salary_tds'), ['all']))
                    <li>
                        <x-tab :href="route('payroll.index', ['tab' => 'settings'])"
                            :text="__('app.menu.settings')"
                            class="settings" ajax="false" />
                    </li>
                    @endif

                </ul>
            </nav>
        </div>

        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey" onclick="openClientDetailSidebar()">
            <i class="fa fa-ellipsis-v"></i>
        </a>
    </div>
    <!-- PROJECT HEADER END -->
@endsection

@section('content')

    <div class="content-wrapper">

        @if ($activeTab === 'salary-slips')
            <div class="d-flex justify-content-between align-items-center mb-3">
                @if (in_array($addPayrollPermission, ['all','branch']))
                <x-forms.link-primary :link="route('payroll.salary-slips.add')" class="mr-3"
                                          icon="plus">
                    @lang('app.addSalary')
                </x-forms.link-primary>
                @endif
                <div>

                    @if (in_array($addPayrollPermission, ['all', 'branch']))
                        <form method="POST" action="{{ route('payroll.salary-slips.generate-monthly') }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-success mr-1">Generate Current Month Slips</button>
                        </form>
                    @endif
                    <a href="{{ route('payroll.salary-slips.export') }}" class="btn btn-sm btn-outline-primary">Export CSV</a>
                </div>
            </div>

            {{-- @if (in_array($addPayrollPermission, ['all', 'added']))
                <div class="card mb-3">
                    <div class="card-header btn-primary">Add Salary Slip</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('payroll.salary-slips.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label>Payee Type</label>
                                    <select name="payee_type" id="payee_type" class="form-control" required>
                                        <option value="employee">Employee</option>
                                        <option value="driver">Driver</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2" id="employee_select_wrap">
                                    <label>Employee</label>
                                    <select name="employee_id" id="employee_id" class="form-control">
                                        <option value="">--</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2 d-none" id="driver_select_wrap">
                                    <label>Driver</label>
                                    <select name="driver_id" id="driver_id" class="form-control">
                                        <option value="">--</option>
                                        @foreach ($drivers as $driver)
                                            <option value="{{ $driver->id }}">{{ $driver->payroll_display_name }} ({{ $driver->payroll_status_label }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <input type="hidden" name="user_id" id="payee_user_id">
                                <div class="col-md-3 mb-2">
                                    <label>Salary Group</label>
                                    <select name="salary_group_id" class="form-control select-picker" data-live-search="true">
                                        <option value="">--</option>
                                        @foreach ($allGroups as $group)
                                            <option value="{{ $group->id }}">{{ $group->group_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Month</label>
                                    <select name="month" id="salary_month" class="form-control" required>
                                        @foreach ($monthOptions as $monthNumber => $monthLabel)
                                            <option value="{{ str_pad((string) $monthNumber, 2, '0', STR_PAD_LEFT) }}" {{ $monthNumber === $currentMonth ? 'selected' : '' }}>
                                                {{ $monthLabel }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Year</label>
                                    <select name="year" id="salary_year" class="form-control" required>
                                        @foreach ($yearOptions as $yearOption)
                                            <option value="{{ $yearOption }}" {{ (int) $yearOption === $currentYear ? 'selected' : '' }}>{{ $yearOption }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="generated">Generated</option>
                                        <option value="review">Review</option>
                                        <option value="locked">Locked</option>
                                        <option value="paid">Paid</option>
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Basic Salary</label>
                                    <input type="number" step="0.01" min="0" name="basic_salary" class="form-control" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Net Salary</label>
                                    <input type="number" step="0.01" min="0" name="net_salary" class="form-control" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Gross Salary</label>
                                    <input type="number" step="0.01" min="0" name="gross_salary" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Monthly Salary</label>
                                    <input type="number" step="0.01" min="0" name="monthly_salary" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Total Deductions</label>
                                    <input type="number" step="0.01" min="0" name="total_deductions" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>TDS</label>
                                    <input type="number" step="0.01" min="0" name="tds" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Expense Claims</label>
                                    <input type="number" step="0.01" min="0" name="expense_claims" class="form-control" value="0">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Paid Amount</label>
                                    <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="0">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Pay Days</label>
                                    <input type="number" min="0" max="31" name="pay_days" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Paid On</label>
                                    <input type="date" name="paid_on" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Salary From</label>
                                    <input type="date" id="salary_from" name="salary_from" class="form-control" value="{{ $currentDate->copy()->startOfMonth()->format('Y-m-d') }}" readonly>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Salary To</label>
                                    <input type="date" id="salary_to" name="salary_to" class="form-control" value="{{ $currentDate->copy()->endOfMonth()->format('Y-m-d') }}">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Payroll Cycle</label>
                                    <select name="payroll_cycle_id" class="form-control select-picker">
                                        <option value="">--</option>
                                        @foreach ($allCycles as $cycle)
                                            <option value="{{ $cycle->id }}">{{ $cycle->cycle }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Payment Method</label>
                                    <select name="salary_payment_method_id" class="form-control select-picker">
                                        <option value="">--</option>
                                        @foreach ($allPaymentMethods as $method)
                                            <option value="{{ $method->id }}">{{ $method->payment_method }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-2">Save</button>
                        </form>
                    </div>
                </div>
            @endif --}}

            <div class="card">
                <div class="card-header form-heading-background d-flex justify-content-between align-items-center">
                    <span>Salary Slips</span>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered text-nowrap">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Period</th>
                                <th>Basic</th>
                                <th>Net</th>
                                <th>Paid</th>
                                <th>Balance</th>
                                <th>Status</th>
                                <th>Group</th>
                                <th>Method</th>
                                <th>Cycle</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($salarySlips as $slip)
                                <tr>
                                    <td>{{ $slip->id }}</td>
                                    <td>{{ $slip->payee_name }}</td>
                                    <td>{{ $slip->month }} {{ $slip->year }}</td>
                                    <td>{{ $slip->basic_salary }}</td>
                                    <td>{{ $slip->net_salary }}</td>
                                    <td>{{ $slip->paid_amount ?? 0 }}</td>
                                    <td>{{ $slip->balance_amount ?? $slip->net_salary }}</td>
                                    <td>{{ ucfirst($slip->status) }}</td>
                                    <td>{{ optional($slip->salaryGroup)->group_name }}</td>
                                    <td>{{ optional($slip->paymentMethod)->payment_method }}</td>
                                    <td>{{ optional($slip->cycle)->cycle }}</td>
                                    <td class="d-flex">
                                        <a href="{{ route('payroll.salary-slips.print', $slip->id) }}" target="_blank" class="btn btn-sm btn-primary mr-1">Print</a>
                                        <a href="{{ route('payroll.salary-slips.pdf', $slip->id) }}" class="btn btn-sm btn-primary mr-1">Download PDF</a>

                                        @if (
                                                $editPayrollPermission == 'all'
                                                || ($editPayrollPermission == 'branch' && hr_has_all_branch_access('payroll'))
                                                || ($editPayrollPermission == 'branch' && user()->branch_id == $slip->user->branch_id)
                                                || ($editPayrollPermission == 'added' && user()->id == $slip->added_by)
                                                || ($editPayrollPermission == 'owned' && user()->id == $slip->user_id)
                                                || ($editPayrollPermission == 'both' && (user()->id == $row->user_id
                                                || user()->id == $row->added_by))
                                            ) 
                                            <form method="POST" action="{{ route('payroll.salary-slips.update', $slip->id) }}" class="mr-1 d-flex align-items-center salary-slip-update-form">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="user_id" value="{{ $slip->user_id }}">
                                                <input type="hidden" name="salary_group_id" value="{{ $slip->salary_group_id }}">
                                                <input type="hidden" name="basic_salary" value="{{ $slip->basic_salary }}">
                                                <input type="hidden" name="net_salary" value="{{ $slip->net_salary }}">
                                                <input type="hidden" name="gross_salary" value="{{ $slip->gross_salary }}">
                                                <input type="hidden" name="monthly_salary" value="{{ $slip->monthly_salary }}">
                                                <input type="hidden" name="total_deductions" value="{{ $slip->total_deductions }}">
                                                <input type="hidden" name="tds" value="{{ $slip->tds }}">
                                                <input type="hidden" name="expense_claims" value="{{ $slip->expense_claims }}">
                                                <input type="hidden" name="pay_days" value="{{ $slip->pay_days }}">
                                                <input type="hidden" name="payroll_cycle_id" value="{{ $slip->payroll_cycle_id }}">
                                                <input type="hidden" name="salary_payment_method_id" value="{{ $slip->salary_payment_method_id }}">
                                                <input type="hidden" name="salary_from" class="update-salary-from" value="{{ optional($slip->salary_from)->format('Y-m-d') }}">
                                                <select name="month" class="form-control form-control-sm mr-1 update-salary-month d-none" style="width: 115px">
                                                    @foreach ($monthOptions as $monthNumber => $monthLabel)
                                                        <option value="{{ str_pad((string) $monthNumber, 2, '0', STR_PAD_LEFT) }}" {{ (int) $monthNumber === (int) $slip->month ? 'selected' : '' }}>
                                                            {{ $monthLabel }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <select name="year" class="form-control form-control-sm mr-1 update-salary-year d-none" style="width: 90px">
                                                    @foreach ($yearOptions as $yearOption)
                                                        <option value="{{ $yearOption }}" {{ (int) $yearOption === (int) $slip->year ? 'selected' : '' }}>{{ $yearOption }}</option>
                                                    @endforeach
                                                </select>
                                                <input type="date" name="salary_to" class="form-control form-control-sm mr-1 update-salary-to d-none" value="{{ optional($slip->salary_to)->format('Y-m-d') }}" style="width: 135px">
                                                <input type="number" name="paid_amount" value="{{ $slip->paid_amount ?? 0 }}" step="0.01" min="0" class="form-control form-control-sm mr-1 d-none" style="width: 90px">
                                                <select name="status" class="form-control form-control mr-1 height-35" data-size="8" style="width: 110px">
                                                    <option value="generated" {{ $slip->status === 'generated' ? 'selected' : '' }}>Generated</option>
                                                    <option value="review" {{ $slip->status === 'review' ? 'selected' : '' }}>Review</option>
                                                    <option value="locked" {{ $slip->status === 'locked' ? 'selected' : '' }}>Locked</option>
                                                    <option value="paid" {{ $slip->status === 'paid' ? 'selected' : '' }}>Paid</option>
                                                </select>
                                                <button type="submit" class="btn btn-sm btn-secondary">Save</button>
                                            </form>
                                        @endif
                                        @if (
                                                $deletePayrollPermission == 'all'
                                                || ($deletePayrollPermission == 'branch' && hr_has_all_branch_access('payroll'))
                                                || ($deletePayrollPermission == 'branch' && user()->branch_id == $slip->user->branch_id)
                                                || ($deletePayrollPermission == 'added' && user()->id == $slip->added_by)
                                                || ($deletePayrollPermission == 'owned' && user()->id == $slip->user_id)
                                                || ($deletePayrollPermission == 'both' && (user()->id == $row->user_id
                                                || user()->id == $row->added_by))
                                            ) 
                                            <form method="POST" action="{{ route('payroll.salary-slips.destroy', $slip->id) }}" onsubmit="return confirm('Delete this salary slip?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No salary slips found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $salarySlips->links() }}
                </div>
            </div>
        @endif

        @if ($activeTab === 'salary-setups')
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-header form-heading-background">Employee Salary Setup (One-Time)</div>
                        <div class="card-body">
                            @if (in_array(user()->permission('manage_employee_salary'), ['all', 'branch']))
                                <form method="POST" action="{{ route('payroll.salary-setups.employees.store') }}" class="mb-3">
                                    @csrf
                                    <div class="form-row">
                                        <div class="col-md-4 mb-2">
                                            <label>Employee</label>
                                            <select name="user_id" class="form-control height-35" required>
                                                <option value="">--</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Status</label>
                                            <select name="status" class="form-control height-35" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Basic</label>
                                            <input type="number" step="0.01" min="0" name="basic_salary" class="form-control height-35" required>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Housing</label>
                                            <input type="number" step="0.01" min="0" name="housing_allowance" class="form-control height-35" value="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Travel</label>
                                            <input type="number" step="0.01" min="0" name="travel_allowance" class="form-control height-35" value="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Opening Balance</label>
                                            <input type="number" step="0.01" min="0" name="opening_balance" class="form-control height-35" value="0">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Setup</button>
                                </form>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Employee</th>
                                            <th>Basic</th>
                                            <th>Housing</th>
                                            <th>Travel</th>
                                            <th>Open Bal</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($employeeSetups as $setup)
                                            <tr>
                                                <td>{{ optional($setup->employee)->name }}</td>
                                                <td>{{ $setup->basic_salary }}</td>
                                                <td>{{ $setup->housing_allowance }}</td>
                                                <td>{{ $setup->travel_allowance }}</td>
                                                <td>{{ $setup->opening_balance }}</td>
                                                <td>{{ ucfirst($setup->status) }}</td>
                                                <td>
                                                    <div class="d-flex">

                                                        @if (in_array(user()->permission('manage_employee_salary'), ['all', 'branch']))
                                                            <form method="POST" action="{{ route('payroll.salary-setups.employees.update', $setup->id) }}" class="mb-1">
                                                                @csrf
                                                                @method('PUT')
                                                                <div class="d-flex flex-wrap">
                                                                    <input type="number" step="0.01" min="0" name="basic_salary" value="{{ $setup->basic_salary }}" class="form-control form-control-sm mr-1 mb-1" style="width: 90px" required>
                                                                    <input type="number" step="0.01" min="0" name="housing_allowance" value="{{ $setup->housing_allowance }}" class="form-control form-control-sm mr-1 mb-1" style="width: 90px">
                                                                    <input type="number" step="0.01" min="0" name="travel_allowance" value="{{ $setup->travel_allowance }}" class="form-control form-control-sm mr-1 mb-1" style="width: 90px">
                                                                    <input type="number" step="0.01" min="0" name="opening_balance" value="{{ $setup->opening_balance }}" class="form-control form-control-sm mr-1 mb-1" style="width: 90px">
                                                                    <select name="status" class="form-control form-control-sm mr-1 mb-1" style="width: 100px">
                                                                        <option value="active" {{ $setup->status === 'active' ? 'selected' : '' }}>Active</option>
                                                                        <option value="inactive" {{ $setup->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                    </select>
                                                                    <button type="submit" class="btn btn-sm btn-secondary mb-1">Update</button>
                                                                </div>
                                                            </form>
                                                        @endif
                                                        @if (in_array(user()->permission('manage_employee_salary'), ['all', 'branch']))
                                                            <form method="POST" action="{{ route('payroll.salary-setups.employees.destroy', $setup->id) }}" onsubmit="return confirm('Delete this setup?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-sm btn-danger ml-2">Delete</button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No employee setups found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                {{ $employeeSetups->links() }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header btn-primary">Driver Salary Setup (One-Time)</div>
                        <div class="card-body">
                            @if (in_array($addPayrollPermission, ['all', 'added']))
                                <form method="POST" action="{{ route('payroll.salary-setups.drivers.store') }}" class="mb-3">
                                    @csrf
                                    <div class="form-row">
                                        <div class="col-md-6 mb-2">
                                            <label>Driver</label>
                                            <select name="driver_id" class="form-control" required>
                                                <option value="">--</option>
                                                @foreach ($drivers as $driver)
                                                    <option value="{{ $driver->id }}">{{ $driver->payroll_display_name }} ({{ $driver->payroll_status_label }})</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label>Status</label>
                                            <select name="status" class="form-control" required>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Basic</label>
                                            <input type="number" step="0.01" min="0" name="basic_salary" class="form-control" required>
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Accommodation</label>
                                            <input type="number" step="0.01" min="0" name="accommodation_allowance" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-4 mb-2">
                                            <label>Car</label>
                                            <input type="number" step="0.01" min="0" name="car_allowance" class="form-control" value="0">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <label>Opening Balance</label>
                                            <input type="number" step="0.01" min="0" name="opening_balance" class="form-control" value="0">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save Setup</button>
                                </form>
                            @endif

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr>
                                            <th>Driver</th>
                                            <th>Basic</th>
                                            <th>Accommodation</th>
                                            <th>Car</th>
                                            <th>Open Bal</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($driverSetups as $setup)
                                            <tr>
                                                <td>{{ optional($setup->driver)->name ?: ('Driver #' . $setup->driver_id) }}</td>
                                                <td>{{ $setup->basic_salary }}</td>
                                                <td>{{ $setup->accommodation_allowance }}</td>
                                                <td>{{ $setup->car_allowance }}</td>
                                                <td>{{ $setup->opening_balance }}</td>
                                                <td>{{ ucfirst($setup->status) }}</td>
                                                <td>
                                                    @if (in_array($editPayrollPermission, ['all', 'added']))
                                                        <form method="POST" action="{{ route('payroll.salary-setups.drivers.update', $setup->id) }}" class="mb-1">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="d-flex flex-wrap">
                                                                <input type="number" step="0.01" min="0" name="basic_salary" value="{{ $setup->basic_salary }}" class="form-control form-control-sm mr-1 mb-1" style="width: 90px" required>
                                                                <input type="number" step="0.01" min="0" name="accommodation_allowance" value="{{ $setup->accommodation_allowance }}" class="form-control form-control-sm mr-1 mb-1" style="width: 90px">
                                                                <input type="number" step="0.01" min="0" name="car_allowance" value="{{ $setup->car_allowance }}" class="form-control form-control-sm mr-1 mb-1" style="width: 90px">
                                                                <input type="number" step="0.01" min="0" name="opening_balance" value="{{ $setup->opening_balance }}" class="form-control form-control-sm mr-1 mb-1" style="width: 90px">
                                                                <select name="status" class="form-control form-control-sm mr-1 mb-1" style="width: 100px">
                                                                    <option value="active" {{ $setup->status === 'active' ? 'selected' : '' }}>Active</option>
                                                                    <option value="inactive" {{ $setup->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                                </select>
                                                                <button type="submit" class="btn btn-sm btn-secondary mb-1">Update</button>
                                                            </div>
                                                        </form>
                                                    @endif
                                                    @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5)
                                                        <form method="POST" action="{{ route('payroll.salary-setups.drivers.destroy', $setup->id) }}" onsubmit="return confirm('Delete this setup?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No driver setups found.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                {{ $driverSetups->links() }}
                            </div>
                        </div>
                    </div>
                </div> --}}
            </div>
        @endif

        @if ($activeTab === 'salary-groups')
            <div class="row">
                {{-- @if (in_array($addPayrollPermission, ['all', 'added'])) --}}
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header form-heading-background">Add Salary Group</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payroll.salary-groups.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Group Name</label>
                                        <input type="text" name="group_name" class="form-control height-35" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Components</label>
                                        <select name="component_ids[]" class="form-control height-35 select-picker" multiple data-live-search="true">
                                            @foreach ($allComponents as $component)
                                                <option value="{{ $component->id }}">{{ $component->component_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Employees</label>
                                        <select name="employee_ids[]" class="form-control height-35 select-picker" multiple data-live-search="true">
                                            @foreach ($employees as $employee)
                                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                {{-- @endif --}}

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header form-heading-background">Salary Groups</div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Employees</th>
                                        <th>Components</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($salaryGroups as $group)
                                        <tr>
                                            <td>{{ $group->id }}</td>
                                            <td>
                                                {{-- @if (in_array($editPayrollPermission, ['all', 'added'])) --}}
                                                    <form method="POST" action="{{ route('payroll.salary-groups.update', $group->id) }}" class="d-flex">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="group_name" value="{{ $group->group_name }}" class="form-control form-control-sm mr-1" required>
                                                        <input type="hidden" name="component_ids[]" value="">
                                                        <input type="hidden" name="employee_ids[]" value="">
                                                        <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                                                    </form>
                                                {{-- @else
                                                    {{ $group->group_name }}
                                                @endif --}}
                                            </td>
                                            <td>{{ $group->employees_count }}</td>
                                            <td>{{ $group->components_count }}</td>
                                            <td>
                                                {{-- @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5) --}}
                                                    <form method="POST" action="{{ route('payroll.salary-groups.destroy', $group->id) }}" onsubmit="return confirm('Delete this salary group?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                {{-- @endif --}}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">No salary groups found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $salaryGroups->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($activeTab === 'salary-components')
            <div class="row">
                {{-- @if (in_array($addPayrollPermission, ['all', 'added'])) --}}
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-header form-heading-background">Add Salary Component</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payroll.salary-components.store') }}">
                                    @csrf
                                    <div class="row">

                                        <div class="form-group col-md-3">
                                            <label>Name</label>
                                            <input type="text" name="component_name" class="form-control height-35" data-size="8" required placeholder="Name">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Type</label>
                                            <select name="component_type" class="form-control height-35" data-size="8">
                                                <option value="earning">Earning</option>
                                                <option value="deduction">Deduction</option>
                                            </select>
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Value</label>
                                            <input type="number" step="0.01" min="0" name="component_value" class="form-control height-35" data-size="8" required placeholder="Value">
                                        </div>
                                        <div class="form-group col-md-3">
                                            <label>Value Type</label>
                                            <select name="value_type" class="form-control height-35" data-size="8">
                                                <option value="fixed">Fixed</option>
                                                <option value="percent">Percent</option>
                                                <option value="basic_percent">Basic Percent</option>
                                                <option value="variable">Variable</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                {{-- @endif --}}

                <div class="col-12">
                    <div class="card">
                        <div class="card-header form-heading-background">Salary Components</div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Type</th>
                                        <th>Value</th>
                                        <th>Value Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($salaryComponents as $component)
                                        <tr>
                                            <td>{{ $component->id }}</td>
                                            <td>
                                                {{-- @if (in_array($editPayrollPermission, ['all', 'added'])) --}}
                                                    <form method="POST" action="{{ route('payroll.salary-components.update', $component->id) }}" class="d-flex flex-wrap">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="component_name" value="{{ $component->component_name }}" class="form-control form-control-sm mr-1 mb-1" style="width: 160px" required>
                                                        <select name="component_type" class="form-control form-control-sm mr-1 mb-1" style="width: 120px">
                                                            <option value="earning" {{ $component->component_type === 'earning' ? 'selected' : '' }}>Earning</option>
                                                            <option value="deduction" {{ $component->component_type === 'deduction' ? 'selected' : '' }}>Deduction</option>
                                                        </select>
                                                        <input type="number" step="0.01" min="0" name="component_value" value="{{ $component->component_value }}" class="form-control form-control-sm mr-1 mb-1" style="width: 120px" required>
                                                        <select name="value_type" class="form-control form-control-sm mr-1 mb-1" style="width: 140px">
                                                            <option value="fixed" {{ $component->value_type === 'fixed' ? 'selected' : '' }}>Fixed</option>
                                                            <option value="percent" {{ $component->value_type === 'percent' ? 'selected' : '' }}>Percent</option>
                                                            <option value="basic_percent" {{ $component->value_type === 'basic_percent' ? 'selected' : '' }}>Basic Percent</option>
                                                            <option value="variable" {{ $component->value_type === 'variable' ? 'selected' : '' }}>Variable</option>
                                                        </select>
                                                        <button type="submit" class="btn btn-sm btn-secondary mb-1">Update</button>
                                                    </form>
                                                {{-- @else
                                                    {{ $component->component_name }}
                                                @endif --}}
                                            </td>
                                            <td>{{ ucfirst($component->component_type) }}</td>
                                            <td>{{ $component->component_value }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $component->value_type)) }}</td>
                                            <td>
                                                {{-- @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5) --}}
                                                    <form method="POST" action="{{ route('payroll.salary-components.destroy', $component->id) }}" onsubmit="return confirm('Delete this salary component?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                {{-- @endif --}}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No salary components found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $salaryComponents->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($activeTab === 'payroll-cycles')
            <div class="row">
                {{-- @if (in_array($addPayrollPermission, ['all', 'added'])) --}}
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header form-heading-background">Add Payroll Cycle</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payroll.payroll-cycles.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Cycle</label>
                                        <input type="text" name="cycle" class="form-control height-35" placeholder="Monthly / Bi-Weekly" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control height-35">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                {{-- @endif --}}

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header form-heading-background">Payroll Cycles</div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Cycle</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($payrollCycles as $cycle)
                                        <tr>
                                            <td>{{ $cycle->id }}</td>
                                            <td>
                                                {{-- @if (in_array($editPayrollPermission, ['all', 'added'])) --}}
                                                    <form method="POST" action="{{ route('payroll.payroll-cycles.update', $cycle->id) }}" class="d-flex">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="cycle" value="{{ $cycle->cycle }}" class="form-control form-control-sm mr-1" required>
                                                        <select name="status" class="form-control form-control-sm mr-1">
                                                            <option value="active" {{ $cycle->status === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ $cycle->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                        <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                                                    </form>
                                                {{-- @else
                                                    {{ $cycle->cycle }}
                                                @endif --}}
                                            </td>
                                            <td>{{ ucfirst($cycle->status) }}</td>
                                            <td>
                                                {{-- @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5) --}}
                                                    <form method="POST" action="{{ route('payroll.payroll-cycles.destroy', $cycle->id) }}" onsubmit="return confirm('Delete this payroll cycle?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                {{-- @endif --}}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No payroll cycles found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $payrollCycles->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($activeTab === 'payment-methods')
            <div class="row">
                {{-- @if (in_array($addPayrollPermission, ['all', 'added'])) --}}
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header form-heading-background">Add Payment Method</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payroll.payment-methods.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="payment_method" class="form-control height-35" required>
                                    </div>
                                    <div class="form-check d-flex align-items-end mb-3">
                                        <input class="form-check-input" type="checkbox" name="default" value="1" id="default-method">
                                        <label class="form-check-label ml-2" for="default-method">Set as default</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                {{-- @endif --}}

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header form-heading-background">Payment Methods</div>
                        <div class="card-body table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Default</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($paymentMethods as $method)
                                        <tr>
                                            <td>{{ $method->id }}</td>
                                            <td>
                                                {{-- @if (in_array($editPayrollPermission, ['all', 'added'])) --}}
                                                    <form method="POST" action="{{ route('payroll.payment-methods.update', $method->id) }}" class="d-flex align-items-center">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="payment_method" value="{{ $method->payment_method }}" class="form-control height-35 mr-1" required>
                                                        <input type="checkbox" name="default" value="1" {{ $method->default ? 'checked' : '' }}>
                                                        <label class="mb-0 mr-1">Default</label>
                                                        <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                                                    </form>
                                                {{-- @else
                                                    {{ $method->payment_method }}
                                                @endif --}}
                                            </td>
                                            <td>{{ $method->default ? 'Yes' : 'No' }}</td>
                                            <td>
                                                {{-- @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5) --}}
                                                    <form method="POST" action="{{ route('payroll.payment-methods.destroy', $method->id) }}" onsubmit="return confirm('Delete this payment method?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                {{-- @endif --}}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No payment methods found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                            {{ $paymentMethods->links() }}
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($activeTab === 'settings')
            <div class="card">
                <div class="card-header form-heading-background">Payroll Settings</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.settings.update') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label>TDS Salary</label>
                                <input type="number" step="0.01" min="0" name="tds_salary" class="form-control height-35" value="{{ $payrollSetting->tds_salary }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>TDS Status</label>
                                <select name="tds_status" class="form-control height-35">
                                    <option value="1" {{ $payrollSetting->tds_status ? 'selected' : '' }}>Enabled</option>
                                    <option value="0" {{ !$payrollSetting->tds_status ? 'selected' : '' }}>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Finance Month</label>
                                <input type="text" maxlength="2" name="finance_month" class="form-control height-35" value="{{ $payrollSetting->finance_month }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Semi Start</label>
                                <input type="number" min="1" max="31" name="semi_monthly_start" class="form-control height-35" value="{{ $payrollSetting->semi_monthly_start }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Semi End</label>
                                <input type="number" min="1" max="31" name="semi_monthly_end" class="form-control height-35" value="{{ $payrollSetting->semi_monthly_end }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Currency</label>
                                <select name="currency_id" class="form-control height-35 select-picker" data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ (int) $payrollSetting->currency_id === (int) $currency->id ? 'selected' : '' }}>
                                            {{ $currency->currency_name }} ({{ $currency->currency_symbol }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- @if (in_array($editPayrollPermission, ['all', 'added'])) --}}
                            <button type="submit" class="btn btn-primary mt-2">Update Settings</button>
                        {{-- @endif --}}
                    </form>
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts')
    <script>

        const activeTab = "{{ $activeTab }}";
        $('.project-menu .' + activeTab).addClass('active');

        const container = document.querySelector('.tabs');
        const primary = container.querySelector('.-primary');
        const primaryItems = container.querySelectorAll('.-primary > li:not(.-more)');

        if (container) {
            container.classList.add('--jsfied');

            primary.insertAdjacentHTML('beforeend', `
            <li class="-more">
                <button type="button" class="px-4 h-100 bg-grey d-none d-lg-flex align-items-center" aria-haspopup="true" aria-expanded="false">
                {{__('app.more')}} <span>&darr;</span>
                </button>
                <ul class="-secondary" id="hide-project-menues">
                ${primary.innerHTML}
                </ul>
            </li>
            `);

            const secondary = container.querySelector('.-secondary');
            const secondaryItems = secondary.querySelectorAll('li');
            const allItems = container.querySelectorAll('li');
            const moreLi = primary.querySelector('.-more');
            const moreBtn = moreLi.querySelector('button');

            moreBtn.addEventListener('click', e => {
                e.preventDefault();
                container.classList.toggle('--show-secondary');
                moreBtn.setAttribute('aria-expanded', container.classList.contains('--show-secondary'));
            });

            const doAdapt = () => {
                allItems.forEach(item => {
                    item.classList.remove('--hidden');
                });

                let stopWidth = moreBtn.offsetWidth;
                let hiddenItems = [];
                const primaryWidth = primary.offsetWidth;
                primaryItems.forEach((item, i) => {
                    if (primaryWidth >= stopWidth + item.offsetWidth) {
                        stopWidth += item.offsetWidth;
                    } else {
                        item.classList.add('--hidden');
                        hiddenItems.push(i);
                    }
                });

                if (!hiddenItems.length) {
                    moreLi.classList.add('--hidden');
                    container.classList.remove('--show-secondary');
                    moreBtn.setAttribute('aria-expanded', false);
                } else {
                    secondaryItems.forEach((item, i) => {
                        if (!hiddenItems.includes(i)) {
                            item.classList.add('--hidden');
                        }
                    });
                }
            };

            doAdapt();
            window.addEventListener('resize', doAdapt);

            document.addEventListener('click', e => {
                let el = e.target;
                while (el) {
                    if (el === secondary || el === moreBtn) return;
                    el = el.parentNode;
                }
                container.classList.remove('--show-secondary');
                moreBtn.setAttribute('aria-expanded', false);
            });
        }

    </script>
    <script>
        (function () {
            const payeeType = document.getElementById('payee_type');
            const employeeWrap = document.getElementById('employee_select_wrap');
            const driverWrap = document.getElementById('driver_select_wrap');
            const employeeId = document.getElementById('employee_id');
            const driverId = document.getElementById('driver_id');
            const payeeUserId = document.getElementById('payee_user_id');
            const salaryMonth = document.getElementById('salary_month');
            const salaryYear = document.getElementById('salary_year');
            const salaryFrom = document.getElementById('salary_from');
            const salaryTo = document.getElementById('salary_to');

            if (!payeeType || !employeeWrap || !driverWrap || !payeeUserId) {
                return;
            }

            const syncPayee = function () {
                if (payeeType.value === 'driver') {
                    employeeWrap.classList.add('d-none');
                    driverWrap.classList.remove('d-none');
                    if (employeeId) {
                        employeeId.disabled = true;
                    }
                    if (driverId) {
                        driverId.disabled = false;
                    }
                    payeeUserId.value = driverId ? driverId.value : '';
                }
                else {
                    driverWrap.classList.add('d-none');
                    employeeWrap.classList.remove('d-none');
                    if (driverId) {
                        driverId.disabled = true;
                    }
                    if (employeeId) {
                        employeeId.disabled = false;
                    }
                    payeeUserId.value = employeeId ? employeeId.value : '';
                }
            };

            payeeType.addEventListener('change', syncPayee);
            if (employeeId) {
                employeeId.addEventListener('change', syncPayee);
            }
            if (driverId) {
                driverId.addEventListener('change', syncPayee);
            }

            const syncSalaryPeriod = function () {
                if (!salaryMonth || !salaryYear || !salaryFrom || !salaryTo) {
                    return;
                }

                const month = parseInt(salaryMonth.value, 10);
                const year = parseInt(salaryYear.value, 10);

                if (isNaN(month) || isNaN(year)) {
                    return;
                }

                const startDate = new Date(year, month - 1, 1);
                const endDate = new Date(year, month, 0);
                const formatDate = function (date) {
                    const yyyy = date.getFullYear();
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const dd = String(date.getDate()).padStart(2, '0');

                    return `${yyyy}-${mm}-${dd}`;
                };

                salaryFrom.value = formatDate(startDate);
                salaryTo.value = formatDate(endDate);
                salaryTo.min = salaryFrom.value;
            };

            if (salaryMonth) {
                salaryMonth.addEventListener('change', syncSalaryPeriod);
            }

            if (salaryYear) {
                salaryYear.addEventListener('change', syncSalaryPeriod);
            }

            const updateForms = document.querySelectorAll('.salary-slip-update-form');
            updateForms.forEach(function (form) {
                const monthField = form.querySelector('.update-salary-month');
                const yearField = form.querySelector('.update-salary-year');
                const fromField = form.querySelector('.update-salary-from');
                const toField = form.querySelector('.update-salary-to');

                if (!monthField || !yearField || !fromField || !toField) {
                    return;
                }

                const syncUpdatePeriod = function () {
                    const month = parseInt(monthField.value, 10);
                    const year = parseInt(yearField.value, 10);

                    if (isNaN(month) || isNaN(year)) {
                        return;
                    }

                    const startDate = new Date(year, month - 1, 1);
                    const endDate = new Date(year, month, 0);
                    const formatDate = function (date) {
                        const yyyy = date.getFullYear();
                        const mm = String(date.getMonth() + 1).padStart(2, '0');
                        const dd = String(date.getDate()).padStart(2, '0');

                        return `${yyyy}-${mm}-${dd}`;
                    };

                    fromField.value = formatDate(startDate);
                    if (!toField.value || toField.value < fromField.value) {
                        toField.value = formatDate(endDate);
                    }

                    toField.min = fromField.value;
                };

                monthField.addEventListener('change', syncUpdatePeriod);
                yearField.addEventListener('change', syncUpdatePeriod);
                syncUpdatePeriod();
            });

            syncPayee();
            syncSalaryPeriod();
        })();
    </script>
@endpush
