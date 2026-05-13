<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f7;
            color: #51545e;
            margin: 0;
            padding: 0;
            width: 100% !important;
        }

        .wrapper {
            width: 100%;
            background-color: #f4f4f7;
            padding: 20px;
        }

        .email-content {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .header {
            background-color: #722C81;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .body {
            padding: 30px;
        }

        .details-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 20px;
            margin: 20px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #edf2f7;
            padding-bottom: 8px;
        }

        .detail-label {
            font-weight: bold;
            color: #64748b;
        }

        .detail-value {
            color: #1e293b;
            font-weight: 600;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            color: white;
            font-size: 14px;
            background-color: {{ $salary->status == 'approved' ? '#10b981' : '#ef4444' }};
        }

        .reason-box {
            margin-top: 15px;
            padding: 15px;
            background-color: #fbe8ff;
            border-left: 4px solid #722C81;
            font-style: italic;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <div class="email-content">
            <div class="header">
                <h1>Advance Salary Request {{ ucfirst($salary->status) }}</h1>
            </div>
            <div class="body">
                <p>Hello {{ $salary->employee->name }},</p>
                <p>There has been an update regarding your advance salary request.</p>

                <div class="details-box">
                    <div class="detail-row">
                        <span class="detail-label">Requested Amount:</span>
                        <span
                            class="detail-value">{{ currency_format($salary->advance_salary, $salary->employee->company->currency_id) }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status:</span>
                        <span class="status-badge">{{ ucfirst($salary->status) }}</span>
                    </div>

                    @if ($salary->status == 'approved' && $salary->approve_reason)
                        <div class="reason-box">
                            <strong>Note:</strong> {{ $salary->approve_reason }}
                        </div>
                    @elseif($salary->status == 'rejected' && $salary->reject_reason)
                        <div class="reason-box">
                            <strong>Reason for Rejection:</strong> {{ $salary->reject_reason }}
                        </div>
                    @endif
                </div>

                <p>Regards,<br>{{ config('app.name') }}</p>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</body>

</html>
