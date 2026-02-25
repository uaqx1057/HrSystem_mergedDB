@extends('layouts.app')

@php
    $addPayrollPermission = user()->permission('add_payroll');
    $editPayrollPermission = user()->permission('edit_payroll');
    $deletePayrollPermission = user()->permission('delete_payroll');
@endphp

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="mb-0">@lang('app.menu.payroll')</h4>
        </div>

        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'salary-slips' ? 'active' : '' }}" href="{{ route('payroll.index', ['tab' => 'salary-slips']) }}">Salary Slips</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'salary-groups' ? 'active' : '' }}" href="{{ route('payroll.index', ['tab' => 'salary-groups']) }}">Salary Groups</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'salary-components' ? 'active' : '' }}" href="{{ route('payroll.index', ['tab' => 'salary-components']) }}">Salary Components</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'payroll-cycles' ? 'active' : '' }}" href="{{ route('payroll.index', ['tab' => 'payroll-cycles']) }}">Payroll Cycles</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'payment-methods' ? 'active' : '' }}" href="{{ route('payroll.index', ['tab' => 'payment-methods']) }}">Payment Methods</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'settings' ? 'active' : '' }}" href="{{ route('payroll.index', ['tab' => 'settings']) }}">Settings</a>
            </li>
        </ul>

        @if ($activeTab === 'salary-slips')
            @if (in_array($addPayrollPermission, ['all', 'added']))
                <div class="card mb-3">
                    <div class="card-header">Add Salary Slip</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('payroll.salary-slips.store') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label>Employee</label>
                                    <select name="user_id" class="form-control select-picker" data-live-search="true" required>
                                        <option value="">--</option>
                                        @foreach ($employees as $employee)
                                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
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
                                    <input type="text" name="month" class="form-control" required>
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Year</label>
                                    <input type="number" name="year" class="form-control" required>
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
                                    <label>Pay Days</label>
                                    <input type="number" min="0" max="31" name="pay_days" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Paid On</label>
                                    <input type="date" name="paid_on" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Salary From</label>
                                    <input type="date" name="salary_from" class="form-control">
                                </div>
                                <div class="col-md-2 mb-2">
                                    <label>Salary To</label>
                                    <input type="date" name="salary_to" class="form-control">
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
            @endif

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Salary Slips</span>
                    <a href="{{ route('payroll.salary-slips.export') }}" class="btn btn-sm btn-outline-primary">Export CSV</a>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee</th>
                                <th>Period</th>
                                <th>Basic</th>
                                <th>Net</th>
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
                                    <td>{{ optional($slip->user)->name }}</td>
                                    <td>{{ $slip->month }} {{ $slip->year }}</td>
                                    <td>{{ $slip->basic_salary }}</td>
                                    <td>{{ $slip->net_salary }}</td>
                                    <td>{{ ucfirst($slip->status) }}</td>
                                    <td>{{ optional($slip->salaryGroup)->group_name }}</td>
                                    <td>{{ optional($slip->paymentMethod)->payment_method }}</td>
                                    <td>{{ optional($slip->cycle)->cycle }}</td>
                                    <td class="d-flex">
                                        <a href="{{ route('payroll.salary-slips.print', $slip->id) }}" target="_blank" class="btn btn-sm btn-info mr-1">Print</a>
                                        <a href="{{ route('payroll.salary-slips.pdf', $slip->id) }}" class="btn btn-sm btn-dark mr-1">Download PDF</a>
                                        @if (in_array($editPayrollPermission, ['all', 'added']))
                                            <form method="POST" action="{{ route('payroll.salary-slips.update', $slip->id) }}" class="mr-1">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="user_id" value="{{ $slip->user_id }}">
                                                <input type="hidden" name="salary_group_id" value="{{ $slip->salary_group_id }}">
                                                <input type="hidden" name="basic_salary" value="{{ $slip->basic_salary }}">
                                                <input type="hidden" name="net_salary" value="{{ $slip->net_salary }}">
                                                <input type="hidden" name="month" value="{{ $slip->month }}">
                                                <input type="hidden" name="year" value="{{ $slip->year }}">
                                                <input type="hidden" name="status" value="locked">
                                                <button type="submit" class="btn btn-sm btn-secondary">Lock</button>
                                            </form>
                                        @endif
                                        @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5)
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
                                    <td colspan="10" class="text-center">No salary slips found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    {{ $salarySlips->links() }}
                </div>
            </div>
        @endif

        @if ($activeTab === 'salary-groups')
            <div class="row">
                @if (in_array($addPayrollPermission, ['all', 'added']))
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header">Add Salary Group</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payroll.salary-groups.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Group Name</label>
                                        <input type="text" name="group_name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Components</label>
                                        <select name="component_ids[]" class="form-control select-picker" multiple data-live-search="true">
                                            @foreach ($allComponents as $component)
                                                <option value="{{ $component->id }}">{{ $component->component_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Employees</label>
                                        <select name="employee_ids[]" class="form-control select-picker" multiple data-live-search="true">
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
                @endif

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Salary Groups</div>
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
                                                @if (in_array($editPayrollPermission, ['all', 'added']))
                                                    <form method="POST" action="{{ route('payroll.salary-groups.update', $group->id) }}" class="d-flex">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="group_name" value="{{ $group->group_name }}" class="form-control form-control-sm mr-1" required>
                                                        <input type="hidden" name="component_ids[]" value="">
                                                        <input type="hidden" name="employee_ids[]" value="">
                                                        <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                                                    </form>
                                                @else
                                                    {{ $group->group_name }}
                                                @endif
                                            </td>
                                            <td>{{ $group->employees_count }}</td>
                                            <td>{{ $group->components_count }}</td>
                                            <td>
                                                @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5)
                                                    <form method="POST" action="{{ route('payroll.salary-groups.destroy', $group->id) }}" onsubmit="return confirm('Delete this salary group?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                @endif
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
                @if (in_array($addPayrollPermission, ['all', 'added']))
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header">Add Salary Component</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payroll.salary-components.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="component_name" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Type</label>
                                        <select name="component_type" class="form-control">
                                            <option value="earning">Earning</option>
                                            <option value="deduction">Deduction</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Value</label>
                                        <input type="number" step="0.01" min="0" name="component_value" class="form-control" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Value Type</label>
                                        <select name="value_type" class="form-control">
                                            <option value="fixed">Fixed</option>
                                            <option value="percent">Percent</option>
                                            <option value="basic_percent">Basic Percent</option>
                                            <option value="variable">Variable</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Salary Components</div>
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
                                                @if (in_array($editPayrollPermission, ['all', 'added']))
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
                                                @else
                                                    {{ $component->component_name }}
                                                @endif
                                            </td>
                                            <td>{{ ucfirst($component->component_type) }}</td>
                                            <td>{{ $component->component_value }}</td>
                                            <td>{{ ucfirst(str_replace('_', ' ', $component->value_type)) }}</td>
                                            <td>
                                                @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5)
                                                    <form method="POST" action="{{ route('payroll.salary-components.destroy', $component->id) }}" onsubmit="return confirm('Delete this salary component?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                @endif
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
                @if (in_array($addPayrollPermission, ['all', 'added']))
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header">Add Payroll Cycle</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payroll.payroll-cycles.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Cycle</label>
                                        <input type="text" name="cycle" class="form-control" placeholder="Monthly / Bi-Weekly" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control">
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Payroll Cycles</div>
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
                                                @if (in_array($editPayrollPermission, ['all', 'added']))
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
                                                @else
                                                    {{ $cycle->cycle }}
                                                @endif
                                            </td>
                                            <td>{{ ucfirst($cycle->status) }}</td>
                                            <td>
                                                @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5)
                                                    <form method="POST" action="{{ route('payroll.payroll-cycles.destroy', $cycle->id) }}" onsubmit="return confirm('Delete this payroll cycle?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                @endif
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
                @if (in_array($addPayrollPermission, ['all', 'added']))
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-header">Add Payment Method</div>
                            <div class="card-body">
                                <form method="POST" action="{{ route('payroll.payment-methods.store') }}">
                                    @csrf
                                    <div class="form-group">
                                        <label>Name</label>
                                        <input type="text" name="payment_method" class="form-control" required>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="checkbox" name="default" value="1" id="default-method">
                                        <label class="form-check-label" for="default-method">Set as default</label>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Save</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">Payment Methods</div>
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
                                                @if (in_array($editPayrollPermission, ['all', 'added']))
                                                    <form method="POST" action="{{ route('payroll.payment-methods.update', $method->id) }}" class="d-flex align-items-center">
                                                        @csrf
                                                        @method('PUT')
                                                        <input type="text" name="payment_method" value="{{ $method->payment_method }}" class="form-control form-control-sm mr-1" required>
                                                        <label class="mb-0 mr-1"><input type="checkbox" name="default" value="1" {{ $method->default ? 'checked' : '' }}> Default</label>
                                                        <button type="submit" class="btn btn-sm btn-secondary">Update</button>
                                                    </form>
                                                @else
                                                    {{ $method->payment_method }}
                                                @endif
                                            </td>
                                            <td>{{ $method->default ? 'Yes' : 'No' }}</td>
                                            <td>
                                                @if ($deletePayrollPermission != 'none' && $deletePayrollPermission != 5)
                                                    <form method="POST" action="{{ route('payroll.payment-methods.destroy', $method->id) }}" onsubmit="return confirm('Delete this payment method?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                                    </form>
                                                @endif
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
                <div class="card-header">Payroll Settings</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('payroll.settings.update') }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <label>TDS Salary</label>
                                <input type="number" step="0.01" min="0" name="tds_salary" class="form-control" value="{{ $payrollSetting->tds_salary }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>TDS Status</label>
                                <select name="tds_status" class="form-control">
                                    <option value="1" {{ $payrollSetting->tds_status ? 'selected' : '' }}>Enabled</option>
                                    <option value="0" {{ !$payrollSetting->tds_status ? 'selected' : '' }}>Disabled</option>
                                </select>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>Finance Month</label>
                                <input type="text" maxlength="2" name="finance_month" class="form-control" value="{{ $payrollSetting->finance_month }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>Semi Start</label>
                                <input type="number" min="1" max="31" name="semi_monthly_start" class="form-control" value="{{ $payrollSetting->semi_monthly_start }}" required>
                            </div>
                            <div class="col-md-2 mb-2">
                                <label>Semi End</label>
                                <input type="number" min="1" max="31" name="semi_monthly_end" class="form-control" value="{{ $payrollSetting->semi_monthly_end }}" required>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label>Currency</label>
                                <select name="currency_id" class="form-control select-picker" data-live-search="true">
                                    <option value="">--</option>
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->id }}" {{ (int) $payrollSetting->currency_id === (int) $currency->id ? 'selected' : '' }}>
                                            {{ $currency->currency_name }} ({{ $currency->currency_symbol }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        @if (in_array($editPayrollPermission, ['all', 'added']))
                            <button type="submit" class="btn btn-primary mt-2">Update Settings</button>
                        @endif
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
