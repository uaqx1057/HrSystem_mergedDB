<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Expiry Notification</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }
        .wrapper {
            width: 100%;
            table-layout: fixed;
            background-color: #f4f7f9;
            padding-bottom: 40px;
        }
        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 600px;
            border-spacing: 0;
            color: #4a4a4a;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .header {
            background-color: #722C81; /* Dark Professional Blue */
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 22px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
        }
        .content h2 {
            color: #111827;
            font-size: 20px;
            margin-top: 0;
        }
        .alert-box {
            background-color: #fffaf0;
            border-left: 4px solid #722C81; /* Amber/Warning color */
            padding: 15px 20px;
            margin: 20px 0;
        }
        .document-card {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 15px;
        }
        .document-card table {
            width: 100%;
        }
        .label {
            color: #722C81;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: bold;
            width: 40%;
        }
        .value {
            color: #111827;
            font-size: 15px;
            font-weight: 500;
        }
        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
        .btn {
            display: inline-block;
            background-color: #722C81;
            color: #ffffff !important;
            padding: 12px 25px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <table class="main">
            <tr>
                <td class="header">
                    <h1>Document Renewal Notice</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <h2>Hello, {{ $expiryData['name'] }}</h2>
                    <p>This is an automated notification to inform you that your official identification document(s) are set to expire in <strong>7 days</strong>.</p>

                    <div class="alert-box">
                        Please review the details below and initiate the renewal process to avoid any disruption in your work status or legal compliance.
                    </div>

                    <!-- Iqama Section -->
                    @if(isset($expiryData['iqama']))
                    <div class="document-card">
                        <table>
                            <tr>
                                <td class="label">Document Type</td>
                                <td class="value">Iqama / National ID</td>
                            </tr>
                            <tr>
                                <td class="label">Document No.</td>
                                <td class="value">{{ $expiryData['iqama'] }}</td>
                            </tr>
                            <tr>
                                <td class="label">Expiry Date</td>
                                <td class="value" style="color: #dc2626;">{{ \Carbon\Carbon::parse($expiryData['iqama_expiry'])->format('d M, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    @endif

                    <!-- Passport Section -->
                    @if(isset($expiryData['passport']))
                    <div class="document-card">
                        <table>
                            <tr>
                                <td class="label">Document Type</td>
                                <td class="value">Passport</td>
                            </tr>
                            <tr>
                                <td class="label">Document No.</td>
                                <td class="value">{{ $expiryData['passport'] }}</td>
                            </tr>
                            <tr>
                                <td class="label">Expiry Date</td>
                                <td class="value" style="color: #dc2626;">{{ \Carbon\Carbon::parse($expiryData['passport_expiry'])->format('d M, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    @endif

                    <!-- Insurance -->
                    @if(isset($expiryData['insurance_expiry']))
                    <div class="document-card">
                        <table>
                            <tr>
                                <td class="label">Document Type</td>
                                <td class="value">Insurance</td>
                            </tr>
                            <tr>
                                <td class="label">Expiry Date</td>
                                <td class="value" style="color: #dc2626;">{{ \Carbon\Carbon::parse($expiryData['insurance_expiry'])->format('d M, Y') }}</td>
                            </tr>
                        </table>
                    </div>
                    @endif

                    <p>If you have already submitted your renewal documents to the HR department, please disregard this message.</p>

                </td>
            </tr>
            <tr>
                <td class="footer">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.<br>
                    This is a system-generated email. Please do not reply to this address.
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
