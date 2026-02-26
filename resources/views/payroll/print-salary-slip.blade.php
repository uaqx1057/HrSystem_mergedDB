<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip #{{ $salarySlip->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #222;
            margin: 24px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
        }

        .subtitle {
            color: #666;
            font-size: 13px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section h3 {
            margin: 0 0 8px;
            font-size: 16px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        td, th {
            border: 1px solid #ddd;
            padding: 8px;
            font-size: 13px;
        }

        th {
            background: #f5f5f5;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .print-actions {
            margin-bottom: 16px;
        }

        @media print {
            .print-actions {
                display: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
<div class="print-actions">
    <button onclick="window.print()">Print Payslip</button>
</div>

<div class="header">
    <div>
        <div class="title">Payslip</div>
        <div class="subtitle">Slip #{{ $salarySlip->id }} | {{ strtoupper($salarySlip->month) }} {{ $salarySlip->year }}</div>
    </div>
    <div class="subtitle">Generated: {{ now()->format('Y-m-d H:i') }}</div>
</div>

<div class="section">
    <h3>Employee Details</h3>
    <table>
        <tr>
            <th style="width: 25%">Name</th>
            <td>{{ $salarySlip->payee_name ?? '-' }}</td>
            <th style="width: 25%">Status</th>
            <td>{{ ucfirst($salarySlip->status) }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>
                @if ($salarySlip->payee_type === 'driver')
                    {{ optional($salarySlip->driver)->email ?? '-' }}
                @else
                    {{ optional($salarySlip->user)->email ?? '-' }}
                @endif
            </td>
            <th>Mobile</th>
            <td>
                @if ($salarySlip->payee_type === 'driver')
                    {{ optional($salarySlip->driver)->mobile ?? '-' }}
                @else
                    {{ optional($salarySlip->user)->mobile ?? '-' }}
                @endif
            </td>
        </tr>
        <tr>
            <th>Salary Group</th>
            <td>{{ optional($salarySlip->salaryGroup)->group_name ?? '-' }}</td>
            <th>Payroll Cycle</th>
            <td>{{ optional($salarySlip->cycle)->cycle ?? '-' }}</td>
        </tr>
        <tr>
            <th>Payment Method</th>
            <td>{{ optional($salarySlip->paymentMethod)->payment_method ?? '-' }}</td>
            <th>Paid On</th>
            <td>{{ optional($salarySlip->paid_on)->format('Y-m-d') ?? '-' }}</td>
        </tr>
    </table>
</div>

<div class="section">
    <h3>Salary Breakdown</h3>
    <table>
        <tr>
            <th>Basic Salary</th>
            <td class="text-right">{{ number_format((float) $salarySlip->basic_salary, 2) }}</td>
            <th>Gross Salary</th>
            <td class="text-right">{{ number_format((float) $salarySlip->gross_salary, 2) }}</td>
        </tr>
        <tr>
            <th>Monthly Salary</th>
            <td class="text-right">{{ number_format((float) $salarySlip->monthly_salary, 2) }}</td>
            <th>Net Salary</th>
            <td class="text-right">{{ number_format((float) $salarySlip->net_salary, 2) }}</td>
        </tr>
        <tr>
            <th>Total Deductions</th>
            <td class="text-right">{{ number_format((float) $salarySlip->total_deductions, 2) }}</td>
            <th>TDS</th>
            <td class="text-right">{{ number_format((float) $salarySlip->tds, 2) }}</td>
        </tr>
        <tr>
            <th>Expense Claims</th>
            <td class="text-right">{{ number_format((float) $salarySlip->expense_claims, 2) }}</td>
            <th>Pay Days</th>
            <td class="text-right">{{ (int) $salarySlip->pay_days }}</td>
        </tr>
        <tr>
            <th>Salary Period</th>
            <td colspan="3">
                {{ optional($salarySlip->salary_from)->format('Y-m-d') ?? '-' }}
                to
                {{ optional($salarySlip->salary_to)->format('Y-m-d') ?? '-' }}
            </td>
        </tr>
    </table>
</div>

<div class="subtitle">This is a system-generated payslip.</div>
</body>
</html>
