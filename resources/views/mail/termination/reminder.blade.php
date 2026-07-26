<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
        .wrapper { width: 100%; background-color: #f4f7f6; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .content { padding: 40px; }
        .header-text { font-size: 22px; font-weight: bold; color: #c0392b; margin-bottom: 20px; border-bottom: 2px solid #edf2f7; padding-bottom: 10px; }
        .notice-box { background-color: #fff5f5; border-left: 4px solid #e74c3c; border-radius: 6px; padding: 14px 18px; margin-bottom: 20px; font-size: 14px; color: #c0392b; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #a0aec0; }
        p { margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="content">
                <div class="header-text">{{ $department }} Clearance Reminder</div>

                <p>Dear <strong>{{ $termination->employee->name }}</strong>,</p>

                <div class="notice-box">
                    {{ $reasonMessage }}
                </div>

                <p>Please resolve the above as soon as possible so your termination clearance process can be completed. HR has been copied on this reminder.</p>

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
