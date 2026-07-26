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
                <div class="header-text">{{ $department }} Clearance Required</div>

                <p>Dear {{ $department }} Team,</p>

                <p>The employee below has been marked for termination and is now pending clearance from your department. Please review the assigned {{ $department == 'IT' ? 'company assets' : 'financial dues / advance salary' }} and issue clearance once verified.</p>

                <table class="details-table">
                    <tr>
                        <td class="label">Employee Name:</td>
                        <td class="value">{{ $termination->employee->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Employee ID:</td>
                        <td class="value">{{ $termination->employee->employeeDetail->employee_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td class="value">{{ $termination->employee->email }}</td>
                    </tr>
                    <tr>
                        <td class="label">Termination Initiated By:</td>
                        <td class="value">{{ $termination->initiatedBy->name ?? 'N/A' }}</td>
                    </tr>
                </table>

                <p>Please log in to the HR system to view details and issue the {{ $department }} clearance letter.</p>

                <div class="btn-container">
                    <a href="{{ route('employees.index') }}?tab=pending-termination" class="btn">View Pending Termination</a>
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
