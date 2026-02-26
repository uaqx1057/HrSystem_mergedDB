<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\PayrollDriverSetup;
use App\Models\PayrollEmployeeSetup;
use App\Models\SalarySlip;
use Illuminate\Console\Command;

class GenerateMonthlyPayrollSlips extends Command
{
    protected $signature = 'payroll:generate-monthly-slips';

    protected $description = 'Generate monthly salary slips for configured employees and drivers';

    public function handle(): void
    {
        $companies = Company::select('id')->get();

        foreach ($companies as $company) {
            $this->generateForEmployees((int) $company->id);
            $this->generateForDrivers((int) $company->id);
        }
    }

    private function generateForEmployees(int $companyId): void
    {
        $setups = PayrollEmployeeSetup::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        foreach ($setups as $setup) {
            $this->createSlipIfMissing(
                companyId: $companyId,
                payeeType: 'employee',
                payeeId: (int) $setup->user_id,
                basicSalary: (float) $setup->basic_salary,
                allowance1: (float) $setup->housing_allowance,
                allowance2: (float) $setup->travel_allowance,
                openingBalance: (float) $setup->opening_balance,
                components: [
                    'basic_salary' => (float) $setup->basic_salary,
                    'housing_allowance' => (float) $setup->housing_allowance,
                    'travel_allowance' => (float) $setup->travel_allowance,
                ]
            );
        }
    }

    private function generateForDrivers(int $companyId): void
    {
        $setups = PayrollDriverSetup::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('status', 'active')
            ->get();

        foreach ($setups as $setup) {
            $this->createSlipIfMissing(
                companyId: $companyId,
                payeeType: 'driver',
                payeeId: (int) $setup->driver_id,
                basicSalary: (float) $setup->basic_salary,
                allowance1: (float) $setup->accommodation_allowance,
                allowance2: (float) $setup->car_allowance,
                openingBalance: (float) $setup->opening_balance,
                components: [
                    'basic_salary' => (float) $setup->basic_salary,
                    'accommodation_allowance' => (float) $setup->accommodation_allowance,
                    'car_allowance' => (float) $setup->car_allowance,
                ]
            );
        }
    }

    private function createSlipIfMissing(
        int $companyId,
        string $payeeType,
        int $payeeId,
        float $basicSalary,
        float $allowance1,
        float $allowance2,
        float $openingBalance,
        array $components
    ): void {
        $month = now()->format('m');
        $year = now()->format('Y');

        $exists = SalarySlip::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('payee_type', $payeeType)
            ->where('user_id', $payeeId)
            ->where('month', $month)
            ->where('year', $year)
            ->exists();

        if ($exists) {
            return;
        }

        $previousSlip = SalarySlip::withoutGlobalScopes()
            ->where('company_id', $companyId)
            ->where('payee_type', $payeeType)
            ->where('user_id', $payeeId)
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->orderByDesc('id')
            ->first();

        $carryForward = $previousSlip ? (float) ($previousSlip->balance_amount ?? 0) : $openingBalance;

        $monthlySalary = round($basicSalary + $allowance1 + $allowance2, 2);
        $netSalary = round($monthlySalary + $carryForward, 2);

        SalarySlip::create([
            'company_id' => $companyId,
            'payee_type' => $payeeType,
            'user_id' => $payeeId,
            'salary_group_id' => null,
            'basic_salary' => $basicSalary,
            'monthly_salary' => $monthlySalary,
            'gross_salary' => $monthlySalary,
            'net_salary' => $netSalary,
            'paid_amount' => 0,
            'balance_amount' => $netSalary,
            'total_deductions' => 0,
            'tds' => 0,
            'expense_claims' => 0,
            'pay_days' => now()->daysInMonth,
            'month' => $month,
            'year' => $year,
            'status' => 'generated',
            'salary_from' => now()->startOfMonth(),
            'salary_to' => now()->endOfMonth(),
            'salary_json' => json_encode([
                'payee_type' => $payeeType,
                'components' => $components,
                'carry_forward' => $carryForward,
            ]),
            'extra_json' => json_encode([
                'auto_generated' => true,
            ]),
        ]);
    }
}
