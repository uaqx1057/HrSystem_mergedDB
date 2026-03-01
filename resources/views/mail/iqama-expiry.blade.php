<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iqama Expiry Alert</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f4f8;
            padding: 30px 0;
            color: #2d3748;
        }

        .wrapper {
            max-width: 640px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }

        /* ── HEADER ── */
        .header {
            background: linear-gradient(135deg, #c0392b 0%, #e74c3c 100%);
            padding: 36px 40px;
            text-align: center;
        }

        .header .alert-icon {
            display: inline-block;
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            line-height: 56px;
            font-size: 26px;
            margin-bottom: 14px;
        }

        .header h1 {
            color: #ffffff;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .header p {
            color: rgba(255,255,255,0.85);
            font-size: 14px;
            margin-top: 6px;
        }

        /* ── BODY ── */
        .body {
            padding: 36px 40px;
        }

        .greeting {
            font-size: 15px;
            color: #4a5568;
            margin-bottom: 16px;
            line-height: 1.6;
        }

        .notice-box {
            background-color: #fff5f5;
            border-left: 4px solid #e74c3c;
            border-radius: 6px;
            padding: 14px 18px;
            margin-bottom: 28px;
            font-size: 14px;
            color: #c0392b;
            line-height: 1.6;
        }

        .section-title {
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #a0aec0;
            margin-bottom: 14px;
        }

        /* ── TABLE ── */
        .employee-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-bottom: 32px;
        }

        .employee-table thead tr {
            background-color: #2d3748;
            color: #ffffff;
        }

        .employee-table thead th {
            padding: 12px 16px;
            text-align: left;
            font-weight: 600;
            font-size: 13px;
            letter-spacing: 0.3px;
        }

        .employee-table thead th:first-child {
            border-radius: 6px 0 0 0;
        }

        .employee-table thead th:last-child {
            border-radius: 0 6px 0 0;
        }

        .employee-table tbody tr {
            border-bottom: 1px solid #edf2f7;
            transition: background 0.2s;
        }

        .employee-table tbody tr:nth-child(even) {
            background-color: #f7fafc;
        }

        .employee-table tbody tr:last-child {
            border-bottom: none;
        }

        .employee-table tbody td {
            padding: 13px 16px;
            color: #2d3748;
            vertical-align: middle;
        }

        .expiry-date {
            display: inline-block;
            background-color: #fff5f5;
            color: #c0392b;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 13px;
            border: 1px solid #feb2b2;
        }

        .employee-name {
            font-weight: 600;
            color: #2d3748;
        }

        .iqama-no {
            font-family: monospace;
            background: #edf2f7;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 13px;
        }

        /* ── ACTION NOTE ── */
        .action-note {
            background-color: #ebf8ff;
            border-left: 4px solid #3498db;
            border-radius: 6px;
            padding: 14px 18px;
            font-size: 14px;
            color: #2b6cb0;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        /* ── FOOTER ── */
        .footer {
            background-color: #f7fafc;
            border-top: 1px solid #edf2f7;
            padding: 24px 40px;
            text-align: center;
        }

        .footer p {
            font-size: 12px;
            color: #a0aec0;
            line-height: 1.7;
        }

        .footer .company {
            font-weight: 700;
            color: #718096;
            font-size: 13px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

<div class="wrapper">

    {{-- ── HEADER ── --}}
    <div class="header">
        <div class="alert-icon">⚠️</div>
        <h1>Iqama Expiry Alert</h1>
        <p>The following employees have Iqama expiring within 7 days</p>
    </div>

    {{-- ── BODY ── --}}
    <div class="body">

        <p class="greeting">
            Dear HR Team,
        </p>
        <p class="greeting" style="margin-top: 8px;">
            This is an automated reminder that the Iqama documents for the employees listed below
            are expiring within the next <strong>7 days</strong>. Please take the necessary action
            to initiate the renewal process as soon as possible to avoid any legal or operational issues.
        </p>

        <div class="notice-box">
            ⚠️ &nbsp; <strong>{{ $employees->count() }} employee(s)</strong> require immediate attention regarding their Iqama renewal.
        </div>

        <p class="section-title">Employee Iqama Details</p>

        <table class="employee-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Employee Name</th>
                    <th>Iqama No.</th>
                    <th>Expiry Date</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($employees as $index => $employee)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>
                            <span class="employee-name">{{ $employee->name }}</span>
                        </td>
                        <td>
                            <span class="iqama-no">
                                {{ $employee->employeeDetail->iqama_no ?? '--' }}
                            </span>
                        </td>
                        <td>
                            <span class="expiry-date">
                                {{ \Carbon\Carbon::parse($employee->employeeDetail->iqama_expiry_date)->format('d-m-Y') }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="action-note">
            ℹ️ &nbsp; Please coordinate with the relevant departments and initiate the Iqama renewal
            process at the earliest. Ensure all required documents are in order before the expiry date.
        </div>

    </div>

    {{-- ── FOOTER ── --}}
    <div class="footer">
        <p class="company">{{ config('app.name') }}</p>
        <p>
            This is an automated email generated by the HR Management System.<br>
            Please do not reply to this email.
        </p>
        <p style="margin-top: 8px;">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </p>
    </div>

</div>

</body>
</html>
