<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f4f7; color: #51545e; margin: 0; padding: 0; width: 100% !important; }
        .wrapper { width: 100%; background-color: #f4f4f7; padding: 20px; }
        .email-content { max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); }
        .header { background-color: #722C81; color: #ffffff; padding: 30px; text-align: center; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 30px; }
        .details-box { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; margin: 20px 0; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #edf2f7; padding-bottom: 8px; }
        .detail-label { font-weight: bold; color: #64748b; }
        .detail-value { color: #1e293b; font-weight: 600; }
        .button-wrapper { text-align: center; margin-top: 30px; }
        .button { background-color: #722C81; color: #ffffff !important; padding: 12px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block; }
        .footer { text-align: center; padding: 20px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="email-content">
            <div class="header"><h1>Air Ticket Request</h1></div>
            <div class="body">
                <p>Hello,</p>
                <p>A new air ticket request has been submitted and requires your review.</p>
                <div class="details-box">
                    <div class="detail-row">
                        <span class="detail-label">Employee:</span>
                        <span class="detail-value">{{ $ticket->employee->name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Travel Date:</span>
                        <span class="detail-value">{{ $ticket->date }}</span>
                    </div>
                    <div class="detail-row" style="border:none;">
                        <span class="detail-label">Current Status:</span>
                        <span class="detail-value" style="color: #f59e0b;">{{ ucfirst($ticket->status) }}</span>
                    </div>
                </div>
                <div class="button-wrapper">
                    <a href="{{ route('air-tickets.index') }}" class="button">Review Request</a>
                </div>
                <p>Regards,<br>{{ config('app.name') }} System</p>
            </div>
        </div>
        <div class="footer">&copy; {{ date('Y') }} {{ config('app.name') }}.</div>
    </div>
</body>
</html>
