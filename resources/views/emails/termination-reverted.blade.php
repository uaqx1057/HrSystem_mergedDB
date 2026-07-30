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
                <div class="header-text">
                    {{ $wasCompleted ? 'Termination Reverted — Employee Reactivated' : 'Pending Termination Cancelled' }}
                </div>

                <p>Dear Team,</p>

                @if($wasCompleted)
                    <p>The termination for the employee below has been <strong>reverted</strong>. Their account has been reactivated and they have been restored to active employee status.</p>
                @else
                    <p>The pending termination process for the employee below has been <strong>cancelled</strong> before completion. No further clearance action is required.</p>
                @endif

                <table class="details-table">
                    <tr>
                        <td class="label">Employee Name:</td>
                        <td class="value">{{ $termination->employee->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Employee ID:</td>
                        <td class="value">{{ $termination->employee->employeeDetail->employee_id ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Email:</td>
                        <td class="value">{{ $termination->employee->email ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Reverted By:</td>
                        <td class="value">{{ $termination->revertedBy->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td class="label">Reverted On:</td>
                        <td class="value">{{ optional($termination->reverted_at)->format('d M Y, h:i A') ?? 'N/A' }}</td>
                    </tr>
                    @if($termination->revert_reason)
                        <tr>
                            <td class="label">Reason:</td>
                            <td class="value">{{ $termination->revert_reason }}</td>
                        </tr>
                    @endif
                </table>

                <p>Please log in to the HR system to view the employee's current status.</p>

                <div class="btn-container">
                    <a href="{{ route('employees.index') }}" class="btn">View Employee</a>
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
