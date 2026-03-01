<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Driver;
use App\Models\EmployeeSalaryGroup;
use App\Models\PayrollDriverSetup;
use App\Models\PayrollEmployeeSetup;
use App\Models\PayrollCycle;
use App\Models\PayrollSetting;
use App\Models\SalaryComponent;
use App\Models\SalaryGroup;
use App\Models\SalaryGroupComponent;
use App\Models\SalaryPaymentMethod;
use App\Models\SalarySlip;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class PayrollController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.payroll';

        $this->middleware(function ($request, $next) {
            $isImpersonatingCompany = session()->has('impersonate');
            abort_403(!$isImpersonatingCompany && !in_array('payroll', $this->user->modules));

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('view_payroll') === 'none' || user()->permission('view_payroll') == 5));

        $tab = $request->get('tab', 'salary-slips');
        $this->activeTab = $tab;

        $this->salarySlips = SalarySlip::with(['user:id,name', 'salaryGroup:id,group_name', 'paymentMethod:id,payment_method', 'cycle:id,cycle'])
            ->latest('id')
            ->paginate(15, ['*'], 'salary_slips_page')
            ->withQueryString();

        $this->salarySlips->getCollection()->load('driver:id,name,email,mobile');

        $this->salaryGroups = SalaryGroup::withCount(['employees', 'components'])
            ->latest('id')
            ->paginate(15, ['*'], 'salary_groups_page')
            ->withQueryString();

        $this->salaryComponents = SalaryComponent::latest('id')
            ->paginate(15, ['*'], 'salary_components_page')
            ->withQueryString();

        $this->payrollCycles = PayrollCycle::latest('id')
            ->paginate(15, ['*'], 'payroll_cycles_page')
            ->withQueryString();

        $this->paymentMethods = SalaryPaymentMethod::latest('id')
            ->paginate(15, ['*'], 'payment_methods_page')
            ->withQueryString();

        $this->employeeSetups = PayrollEmployeeSetup::with(['employee:id,name'])
            ->latest('id')
            ->paginate(15, ['*'], 'employee_setups_page')
            ->withQueryString();

        $this->driverSetups = PayrollDriverSetup::with(['driver:id,name,driver_id,iqaama_number'])
            ->latest('id')
            ->paginate(15, ['*'], 'driver_setups_page')
            ->withQueryString();

        $this->payrollSetting = PayrollSetting::firstOrCreate(
            ['company_id' => company()->id],
            [
                'tds_salary' => '0',
                'tds_status' => 0,
                'finance_month' => '04',
                'semi_monthly_start' => 1,
                'semi_monthly_end' => 30,
                'currency_id' => company()->currency_id,
            ]
        );

        $this->employees = User::allEmployees(null, false, 'all');
        $this->drivers = Driver::withoutGlobalScopes()
            ->newQuery()
            ->select('id', 'name', 'driver_id', 'iqaama_number', 'email', 'mobile', 'onboarding_stage', 'offboard_request', 'offboarding_stage')
            ->orderBy('name')
            ->get()
            ->map(function (Driver $driver) {
                $driver->payroll_display_name = $driver->name
                    ?: ($driver->driver_id
                        ?: ($driver->iqaama_number
                            ? 'Driver ' . $driver->iqaama_number
                            : 'Driver #' . $driver->id));
                $driver->payroll_status_label = $this->resolveDriverPayrollStatusLabel($driver);

                return $driver;
            });
        $this->currencies = Currency::all(['id', 'currency_name', 'currency_symbol']);
        $this->allGroups = SalaryGroup::orderBy('group_name')->get(['id', 'group_name']);
        $this->allComponents = SalaryComponent::orderBy('component_name')->get(['id', 'component_name']);
        $this->allCycles = PayrollCycle::orderBy('cycle')->get(['id', 'cycle']);
        $this->allPaymentMethods = SalaryPaymentMethod::orderBy('payment_method')->get(['id', 'payment_method']);

        return view('payroll.index', $this->data);
    }

    public function exportSalarySlipsCsv(Request $request)
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('view_payroll') === 'none' || user()->permission('view_payroll') == 5));

        $status = $request->get('status');
        $month = $request->get('month');
        $year = $request->get('year');

        $query = SalarySlip::with(['user:id,name', 'driver:id,name', 'salaryGroup:id,group_name', 'paymentMethod:id,payment_method', 'cycle:id,cycle'])
            ->orderByDesc('id');

        if (!empty($status) && in_array($status, ['generated', 'review', 'locked', 'paid'])) {
            $query->where('status', $status);
        }

        if (!empty($month)) {
            $query->where('month', $month);
        }

        if (!empty($year)) {
            $query->where('year', $year);
        }

        $fileName = 'salary-slips-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');

            fputcsv($output, [
                'ID',
                'Employee',
                'Month',
                'Year',
                'Status',
                'Basic Salary',
                'Net Salary',
                'Gross Salary',
                'Monthly Salary',
                'Total Deductions',
                'TDS',
                'Expense Claims',
                'Pay Days',
                'Group',
                'Payment Method',
                'Payroll Cycle',
                'Paid On',
            ]);

            $query->chunkById(500, function ($slips) use ($output) {
                foreach ($slips as $slip) {
                    fputcsv($output, [
                        $slip->id,
                        $slip->payee_name,
                        $slip->month,
                        $slip->year,
                        $slip->status,
                        $slip->basic_salary,
                        $slip->net_salary,
                        $slip->gross_salary,
                        $slip->monthly_salary,
                        $slip->total_deductions,
                        $slip->tds,
                        $slip->expense_claims,
                        $slip->pay_days,
                        optional($slip->salaryGroup)->group_name,
                        optional($slip->paymentMethod)->payment_method,
                        optional($slip->cycle)->cycle,
                        optional($slip->paid_on)->format('Y-m-d'),
                    ]);
                }
            });

            fclose($output);
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function printSalarySlip(SalarySlip $salarySlip)
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('view_payroll') === 'none' || user()->permission('view_payroll') == 5));

        $salarySlip->load([
            'user:id,name,email,mobile',
            'driver:id,name,email,mobile',
            'salaryGroup:id,group_name',
            'paymentMethod:id,payment_method',
            'cycle:id,cycle',
        ]);

        return view('payroll.print-salary-slip', [
            'salarySlip' => $salarySlip,
        ]);
    }

    public function downloadSalarySlipPdf(SalarySlip $salarySlip)
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('view_payroll') === 'none' || user()->permission('view_payroll') == 5));

        $salarySlip->load([
            'user:id,name,email,mobile',
            'driver:id,name,email,mobile',
            'salaryGroup:id,group_name',
            'paymentMethod:id,payment_method',
            'cycle:id,cycle',
        ]);

        $pdf = app('dompdf.wrapper');
        $pdf->setOption('isHtml5ParserEnabled', true);
        $pdf->setOption('isRemoteEnabled', true);
        $pdf->loadView('payroll.print-salary-slip', [
            'salarySlip' => $salarySlip,
        ]);

        $filename = 'payslip-' . $salarySlip->id . '-' . strtolower($salarySlip->month) . '-' . $salarySlip->year;

        return $pdf->download($filename . '.pdf');
    }

    public function storeSalarySlip(Request $request): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('add_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'payee_type' => 'required|in:employee,driver',
            'employee_id' => 'nullable|exists:users,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'user_id' => 'nullable',
            'salary_group_id' => 'nullable|exists:salary_groups,id',
            'basic_salary' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'month' => 'required|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'year' => 'required|integer|min:2000|max:' . now()->year,
            'status' => 'required|in:generated,review,locked,paid',
            'paid_on' => 'nullable|date',
            'salary_payment_method_id' => 'nullable|exists:salary_payment_methods,id',
            'tds' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'gross_salary' => 'nullable|numeric|min:0',
            'total_deductions' => 'nullable|numeric|min:0',
            'pay_days' => 'nullable|integer|min:0|max:31',
            'salary_from' => 'nullable|date',
            'salary_to' => 'nullable|date|after_or_equal:salary_from',
            'payroll_cycle_id' => 'nullable|exists:payroll_cycles,id',
            'expense_claims' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $payeeType = $request->payee_type;
        $payeeId = $payeeType === 'driver' ? $request->driver_id : $request->employee_id;

        abort_403(empty($payeeId));

        $validated['user_id'] = $payeeId;
        unset($validated['employee_id'], $validated['driver_id'], $validated['payee_type']);

        $validated['company_id'] = company()->id;
        $validated['added_by'] = user()->id;
        $validated['last_updated_by'] = user()->id;

        [$periodStart, $periodEnd] = $this->resolveSalaryPeriod((int) $validated['year'], (string) $validated['month']);
        $validated['salary_from'] = $periodStart->toDateString();
        $validated['salary_to'] = $request->filled('salary_to') ? $validated['salary_to'] : $periodEnd->toDateString();
        $validated['tds'] = (float) ($validated['tds'] ?? 0);
        $validated['gross_salary'] = (float) ($validated['gross_salary'] ?? $validated['net_salary'] ?? 0);
        $validated['monthly_salary'] = (float) ($validated['monthly_salary'] ?? $validated['net_salary'] ?? 0);
        $validated['total_deductions'] = (float) ($validated['total_deductions'] ?? 0);
        $validated['expense_claims'] = (float) ($validated['expense_claims'] ?? 0);
        $validated['pay_days'] = (int) ($validated['pay_days'] ?? $periodEnd->day);

        $netSalary = (float) ($validated['net_salary'] ?? 0);
        $paidAmount = min((float) ($validated['paid_amount'] ?? 0), $netSalary);
        $validated['paid_amount'] = $paidAmount;
        $validated['balance_amount'] = max($netSalary - $paidAmount, 0);
        $validated['payee_type'] = $payeeType;

        if ($validated['balance_amount'] <= 0 && ($validated['status'] ?? '') !== 'paid') {
            $validated['status'] = 'paid';
            $validated['paid_on'] = $validated['paid_on'] ?? now()->toDateString();
        }

        if (($validated['status'] ?? '') === 'paid' && empty($validated['paid_on'])) {
            $validated['paid_on'] = now()->toDateString();
        }

        $validated['salary_json'] = json_encode(array_filter([
            'payee_type' => $payeeType,
        ]));

        SalarySlip::create($validated);

        return redirect()->route('payroll.index', ['tab' => 'salary-slips'])->with('success', __('messages.recordSaved'));
    }

    public function updateSalarySlip(Request $request, SalarySlip $salarySlip): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('edit_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'payee_type' => 'nullable|in:employee,driver',
            'employee_id' => 'nullable|exists:users,id',
            'driver_id' => 'nullable|exists:drivers,id',
            'user_id' => 'nullable',
            'salary_group_id' => 'nullable|exists:salary_groups,id',
            'basic_salary' => 'required|numeric|min:0',
            'net_salary' => 'required|numeric|min:0',
            'month' => 'required|in:01,02,03,04,05,06,07,08,09,10,11,12',
            'year' => 'required|integer|min:2000|max:' . now()->year,
            'status' => 'required|in:generated,review,locked,paid',
            'paid_on' => 'nullable|date',
            'salary_payment_method_id' => 'nullable|exists:salary_payment_methods,id',
            'tds' => 'nullable|numeric|min:0',
            'monthly_salary' => 'nullable|numeric|min:0',
            'gross_salary' => 'nullable|numeric|min:0',
            'total_deductions' => 'nullable|numeric|min:0',
            'pay_days' => 'nullable|integer|min:0|max:31',
            'salary_from' => 'nullable|date',
            'salary_to' => 'nullable|date|after_or_equal:salary_from',
            'payroll_cycle_id' => 'nullable|exists:payroll_cycles,id',
            'expense_claims' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
        ]);

        $payeeType = $request->payee_type ?: $salarySlip->payee_type;
        $payeeId = $salarySlip->user_id;

        if ($request->filled('employee_id') || $request->filled('driver_id')) {
            $payeeId = $payeeType === 'driver' ? $request->driver_id : $request->employee_id;
            abort_403(empty($payeeId));
        }

        $validated['user_id'] = $payeeId;
        unset($validated['employee_id'], $validated['driver_id'], $validated['payee_type']);

        $validated['last_updated_by'] = user()->id;
        $salaryJson = is_string($salarySlip->salary_json) ? json_decode($salarySlip->salary_json, true) : (array) $salarySlip->salary_json;
        $salaryJson['payee_type'] = $payeeType;

        [$periodStart, $periodEnd] = $this->resolveSalaryPeriod((int) $validated['year'], (string) $validated['month']);
        $validated['salary_from'] = $periodStart->toDateString();
        $validated['salary_to'] = $request->filled('salary_to') ? $validated['salary_to'] : $periodEnd->toDateString();
        $validated['tds'] = (float) ($validated['tds'] ?? $salarySlip->tds ?? 0);
        $validated['gross_salary'] = (float) ($validated['gross_salary'] ?? $salarySlip->gross_salary ?? $validated['net_salary'] ?? 0);
        $validated['monthly_salary'] = (float) ($validated['monthly_salary'] ?? $salarySlip->monthly_salary ?? $validated['net_salary'] ?? 0);
        $validated['total_deductions'] = (float) ($validated['total_deductions'] ?? $salarySlip->total_deductions ?? 0);
        $validated['expense_claims'] = (float) ($validated['expense_claims'] ?? $salarySlip->expense_claims ?? 0);
        $validated['pay_days'] = (int) ($validated['pay_days'] ?? $salarySlip->pay_days ?? $periodEnd->day);

        $netSalary = (float) ($validated['net_salary'] ?? $salarySlip->net_salary ?? 0);
        $paidAmount = (float) ($validated['paid_amount'] ?? $salarySlip->paid_amount ?? 0);
        $paidAmount = min($paidAmount, $netSalary);

        $validated['paid_amount'] = $paidAmount;
        $validated['balance_amount'] = max($netSalary - $paidAmount, 0);
        $validated['payee_type'] = $payeeType;

        if ($validated['balance_amount'] <= 0 && ($validated['status'] ?? '') !== 'paid') {
            $validated['status'] = 'paid';
            $validated['paid_on'] = $validated['paid_on'] ?? now()->toDateString();
        }

        if (($validated['status'] ?? '') === 'paid' && empty($validated['paid_on'])) {
            $validated['paid_on'] = now()->toDateString();
        }

        $validated['salary_json'] = json_encode($salaryJson);
        $salarySlip->update($validated);

        return redirect()->route('payroll.index', ['tab' => 'salary-slips'])->with('success', __('messages.updateSuccess'));
    }

    public function destroySalarySlip(SalarySlip $salarySlip): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('delete_payroll') === 'none' || user()->permission('delete_payroll') == 5));

        $salarySlip->delete();

        return redirect()->route('payroll.index', ['tab' => 'salary-slips'])->with('success', __('messages.deleteSuccess'));
    }

    public function storeSalaryGroup(Request $request): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('add_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'group_name' => 'required|string|max:191',
            'component_ids' => 'nullable|array',
            'component_ids.*' => 'nullable|exists:salary_components,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function () use ($validated) {
            $group = SalaryGroup::create([
                'company_id' => company()->id,
                'group_name' => $validated['group_name'],
            ]);

            $componentIds = array_filter($validated['component_ids'] ?? []);
            $employeeIds = array_filter($validated['employee_ids'] ?? []);

            foreach ($componentIds as $componentId) {
                SalaryGroupComponent::create([
                    'company_id' => company()->id,
                    'salary_group_id' => $group->id,
                    'salary_component_id' => $componentId,
                ]);
            }

            foreach ($employeeIds as $employeeId) {
                EmployeeSalaryGroup::create([
                    'salary_group_id' => $group->id,
                    'user_id' => $employeeId,
                ]);
            }
        });

        return redirect()->route('payroll.index', ['tab' => 'salary-groups'])->with('success', __('messages.recordSaved'));
    }

    public function updateSalaryGroup(Request $request, SalaryGroup $salaryGroup): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('edit_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'group_name' => 'required|string|max:191',
            'component_ids' => 'nullable|array',
            'component_ids.*' => 'nullable|exists:salary_components,id',
            'employee_ids' => 'nullable|array',
            'employee_ids.*' => 'nullable|exists:users,id',
        ]);

        DB::transaction(function () use ($validated, $salaryGroup) {
            $salaryGroup->update(['group_name' => $validated['group_name']]);

            SalaryGroupComponent::where('salary_group_id', $salaryGroup->id)->delete();
            EmployeeSalaryGroup::where('salary_group_id', $salaryGroup->id)->delete();

            foreach (array_filter($validated['component_ids'] ?? []) as $componentId) {
                SalaryGroupComponent::create([
                    'company_id' => company()->id,
                    'salary_group_id' => $salaryGroup->id,
                    'salary_component_id' => $componentId,
                ]);
            }

            foreach (array_filter($validated['employee_ids'] ?? []) as $employeeId) {
                EmployeeSalaryGroup::create([
                    'salary_group_id' => $salaryGroup->id,
                    'user_id' => $employeeId,
                ]);
            }
        });

        return redirect()->route('payroll.index', ['tab' => 'salary-groups'])->with('success', __('messages.updateSuccess'));
    }

    public function destroySalaryGroup(SalaryGroup $salaryGroup): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('delete_payroll') === 'none' || user()->permission('delete_payroll') == 5));

        DB::transaction(function () use ($salaryGroup) {
            SalaryGroupComponent::where('salary_group_id', $salaryGroup->id)->delete();
            EmployeeSalaryGroup::where('salary_group_id', $salaryGroup->id)->delete();
            $salaryGroup->delete();
        });

        return redirect()->route('payroll.index', ['tab' => 'salary-groups'])->with('success', __('messages.deleteSuccess'));
    }

    public function storeSalaryComponent(Request $request): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('add_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'component_name' => 'required|string|max:191',
            'component_type' => 'required|in:earning,deduction',
            'component_value' => 'required|numeric|min:0',
            'value_type' => 'required|in:fixed,percent,basic_percent,variable',
        ]);

        $validated['company_id'] = company()->id;
        SalaryComponent::create($validated);

        return redirect()->route('payroll.index', ['tab' => 'salary-components'])->with('success', __('messages.recordSaved'));
    }

    public function updateSalaryComponent(Request $request, SalaryComponent $salaryComponent): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('edit_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'component_name' => 'required|string|max:191',
            'component_type' => 'required|in:earning,deduction',
            'component_value' => 'required|numeric|min:0',
            'value_type' => 'required|in:fixed,percent,basic_percent,variable',
        ]);

        $salaryComponent->update($validated);

        return redirect()->route('payroll.index', ['tab' => 'salary-components'])->with('success', __('messages.updateSuccess'));
    }

    public function destroySalaryComponent(SalaryComponent $salaryComponent): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('delete_payroll') === 'none' || user()->permission('delete_payroll') == 5));

        SalaryGroupComponent::where('salary_component_id', $salaryComponent->id)->delete();
        $salaryComponent->delete();

        return redirect()->route('payroll.index', ['tab' => 'salary-components'])->with('success', __('messages.deleteSuccess'));
    }

    public function storePayrollCycle(Request $request): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('add_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'cycle' => 'required|string|max:191',
            'status' => 'required|in:active,inactive',
        ]);

        PayrollCycle::create($validated);

        return redirect()->route('payroll.index', ['tab' => 'payroll-cycles'])->with('success', __('messages.recordSaved'));
    }

    public function updatePayrollCycle(Request $request, PayrollCycle $payrollCycle): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('edit_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'cycle' => 'required|string|max:191',
            'status' => 'required|in:active,inactive',
        ]);

        $payrollCycle->update($validated);

        return redirect()->route('payroll.index', ['tab' => 'payroll-cycles'])->with('success', __('messages.updateSuccess'));
    }

    public function destroyPayrollCycle(PayrollCycle $payrollCycle): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('delete_payroll') === 'none' || user()->permission('delete_payroll') == 5));

        $payrollCycle->delete();

        return redirect()->route('payroll.index', ['tab' => 'payroll-cycles'])->with('success', __('messages.deleteSuccess'));
    }

    public function storePaymentMethod(Request $request): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('add_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'payment_method' => 'required|string|max:191',
            'default' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated) {
            $isDefault = isset($validated['default']) ? (bool)$validated['default'] : false;

            if ($isDefault) {
                SalaryPaymentMethod::query()->update(['default' => 0]);
            }

            SalaryPaymentMethod::create([
                'company_id' => company()->id,
                'payment_method' => $validated['payment_method'],
                'default' => $isDefault ? 1 : 0,
            ]);
        });

        return redirect()->route('payroll.index', ['tab' => 'payment-methods'])->with('success', __('messages.recordSaved'));
    }

    public function updatePaymentMethod(Request $request, SalaryPaymentMethod $salaryPaymentMethod): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('edit_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'payment_method' => 'required|string|max:191',
            'default' => 'nullable|boolean',
        ]);

        DB::transaction(function () use ($validated, $salaryPaymentMethod) {
            $isDefault = isset($validated['default']) ? (bool)$validated['default'] : false;

            if ($isDefault) {
                SalaryPaymentMethod::query()->where('id', '!=', $salaryPaymentMethod->id)->update(['default' => 0]);
            }

            $salaryPaymentMethod->update([
                'payment_method' => $validated['payment_method'],
                'default' => $isDefault ? 1 : 0,
            ]);
        });

        return redirect()->route('payroll.index', ['tab' => 'payment-methods'])->with('success', __('messages.updateSuccess'));
    }

    public function destroyPaymentMethod(SalaryPaymentMethod $salaryPaymentMethod): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('delete_payroll') === 'none' || user()->permission('delete_payroll') == 5));

        $salaryPaymentMethod->delete();

        return redirect()->route('payroll.index', ['tab' => 'payment-methods'])->with('success', __('messages.deleteSuccess'));
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('edit_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'tds_salary' => 'required|numeric|min:0',
            'tds_status' => 'required|boolean',
            'finance_month' => 'required|string|max:2',
            'semi_monthly_start' => 'required|integer|min:1|max:31',
            'semi_monthly_end' => 'required|integer|min:1|max:31|gte:semi_monthly_start',
            'currency_id' => 'nullable|exists:currencies,id',
        ]);

        PayrollSetting::updateOrCreate(
            ['company_id' => company()->id],
            $validated
        );

        return redirect()->route('payroll.index', ['tab' => 'settings'])->with('success', __('messages.updateSuccess'));
    }

    public function generateMonthlySlips(): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('add_payroll'), ['all', 'added']));

        Artisan::call('payroll:generate-monthly-slips');

        return redirect()->route('payroll.index', ['tab' => 'salary-slips'])->with('success', 'Monthly salary slips generated successfully.');
    }

    public function storeEmployeeSetup(Request $request): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('add_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'basic_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'travel_allowance' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        PayrollEmployeeSetup::updateOrCreate(
            [
                'company_id' => company()->id,
                'user_id' => $validated['user_id'],
            ],
            [
                'basic_salary' => $validated['basic_salary'],
                'housing_allowance' => $validated['housing_allowance'] ?? 0,
                'travel_allowance' => $validated['travel_allowance'] ?? 0,
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'status' => $validated['status'],
            ]
        );

        return redirect()->route('payroll.index', ['tab' => 'salary-setups'])->with('success', __('messages.recordSaved'));
    }

    public function updateEmployeeSetup(Request $request, PayrollEmployeeSetup $payrollEmployeeSetup): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('edit_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'housing_allowance' => 'nullable|numeric|min:0',
            'travel_allowance' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $payrollEmployeeSetup->update([
            'basic_salary' => $validated['basic_salary'],
            'housing_allowance' => $validated['housing_allowance'] ?? 0,
            'travel_allowance' => $validated['travel_allowance'] ?? 0,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'status' => $validated['status'],
        ]);

        return redirect()->route('payroll.index', ['tab' => 'salary-setups'])->with('success', __('messages.updateSuccess'));
    }

    public function destroyEmployeeSetup(PayrollEmployeeSetup $payrollEmployeeSetup): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('delete_payroll') === 'none' || user()->permission('delete_payroll') == 5));

        $payrollEmployeeSetup->delete();

        return redirect()->route('payroll.index', ['tab' => 'salary-setups'])->with('success', __('messages.deleteSuccess'));
    }

    public function storeDriverSetup(Request $request): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('add_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'basic_salary' => 'required|numeric|min:0',
            'accommodation_allowance' => 'nullable|numeric|min:0',
            'car_allowance' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        PayrollDriverSetup::updateOrCreate(
            [
                'company_id' => company()->id,
                'driver_id' => $validated['driver_id'],
            ],
            [
                'basic_salary' => $validated['basic_salary'],
                'accommodation_allowance' => $validated['accommodation_allowance'] ?? 0,
                'car_allowance' => $validated['car_allowance'] ?? 0,
                'opening_balance' => $validated['opening_balance'] ?? 0,
                'status' => $validated['status'],
            ]
        );

        return redirect()->route('payroll.index', ['tab' => 'salary-setups'])->with('success', __('messages.recordSaved'));
    }

    public function updateDriverSetup(Request $request, PayrollDriverSetup $payrollDriverSetup): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && !in_array(user()->permission('edit_payroll'), ['all', 'added']));

        $validated = $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'accommodation_allowance' => 'nullable|numeric|min:0',
            'car_allowance' => 'nullable|numeric|min:0',
            'opening_balance' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $payrollDriverSetup->update([
            'basic_salary' => $validated['basic_salary'],
            'accommodation_allowance' => $validated['accommodation_allowance'] ?? 0,
            'car_allowance' => $validated['car_allowance'] ?? 0,
            'opening_balance' => $validated['opening_balance'] ?? 0,
            'status' => $validated['status'],
        ]);

        return redirect()->route('payroll.index', ['tab' => 'salary-setups'])->with('success', __('messages.updateSuccess'));
    }

    public function destroyDriverSetup(PayrollDriverSetup $payrollDriverSetup): RedirectResponse
    {
        $isImpersonatingCompany = session()->has('impersonate');
        abort_403(!$isImpersonatingCompany && (user()->permission('delete_payroll') === 'none' || user()->permission('delete_payroll') == 5));

        $payrollDriverSetup->delete();

        return redirect()->route('payroll.index', ['tab' => 'salary-setups'])->with('success', __('messages.deleteSuccess'));
    }

    private function resolveDriverPayrollStatusLabel(Driver $driver): string
    {
        $offboardingStage = strtolower((string) ($driver->offboarding_stage ?? ''));

        if ($offboardingStage === 'completed') {
            return 'Offboarding Completed';
        }

        $hasOffboarding = ((int) ($driver->offboard_request ?? 0) === 1) || !empty($offboardingStage);

        if ($hasOffboarding) {
            return 'Pending Offboarding';
        }

        if (strtolower((string) ($driver->onboarding_stage ?? '')) === 'completed') {
            return 'Onboarding Completed';
        }

        return 'Pending Onboarding';
    }

    private function resolveSalaryPeriod(int $year, string $month): array
    {
        $periodStart = Carbon::create($year, (int) $month, 1)->startOfMonth();
        $periodEnd = (clone $periodStart)->endOfMonth();

        return [$periodStart, $periodEnd];
    }
}
