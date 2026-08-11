<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f4f7f6; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .content { padding: 40px; }
        .header-text { font-size: 22px; font-weight: bold; color: #2d3748; margin-bottom: 20px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        .details-table { width: 100%; background: #f8fafc; border-radius: 6px; padding: 20px; margin: 25px 0; border-spacing: 0; }
        .details-table td { padding: 8px 0; font-size: 14px; }
        .label { font-weight: bold; color: #718096; width: 160px; }
        .value { color: #2d3748; font-weight: 500; }
        .amount-highlight { background: #fff5f5; border-left: 4px solid #e53e3e; padding: 15px 20px; border-radius: 4px; margin: 25px 0; }
        .amount-highlight .amount-label { font-size: 13px; color: #742a2a; text-transform: uppercase; letter-spacing: 0.5px; }
        .amount-highlight .amount { font-size: 22px; font-weight: bold; color: #c53030; margin-top: 4px; }
        .btn-container { text-align: center; margin-top: 30px; }
        .btn { background-color: #722C81; color: #ffffff !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #a0aec0; }
        p { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div class="header-text">Asset Loss/Damage - Salary Deduction Required</div>

                <p>Dear Finance Team,</p>

                <p>An employee has returned a company asset marked as lost or damaged. The amount below should be deducted from the employee's salary. Please review the details and process the deduction accordingly.</p>

                <table class="details-table">
                    <tr>
                        <td class="label">Employee Name:</td>
                        <td class="value">{{ $assignment->employee->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Employee ID:</td>
                        <td class="value">{{ $assignment->employee->employeeDetail->employee_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Asset Name:</td>
                        <td class="value">{{ $asset->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Serial No:</td>
                        <td class="value">{{ $assignment->serial_no ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Reported On:</td>
                        <td class="value">{{ $assessLoss->created_at->format('d M, Y') }}</td>
                    </tr>
                </table>

                <div class="amount-highlight">
                    <div class="amount-label">Loss Amount to be Deducted</div>
                    <div class="amount">{{ number_format($assessLoss->loss_amount, 2) }}</div>
                </div>

                <p>Please log in to the HR system to view full details and process this salary deduction.</p>

                <div class="btn-container">
                    <a href="{{ route('employees.show', [$assignment->employee_id, 'tab' => 'company-assets']) }}" class="btn">View Employee Assets</a>
                </div>

                <p style="margin-top: 30px;">
                    Regards,<br>
                    <strong>{{ config('app.name') }} Team</strong>
                </p>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>
</html>
