<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Employee Document Expiry Summary</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #f4f7f9;
            margin: 0;
            padding: 0;
        }

        .wrapper {
            width: 100%;
            background-color: #f4f7f9;
            padding-bottom: 40px;
        }

        .main {
            background-color: #ffffff;
            margin: 0 auto;
            width: 100%;
            max-width: 700px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
        }

        .header {
            background-color: #722C81;
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
            padding: 30px;
            line-height: 1.6;
            color: #4a4a4a;
        }

        table.summary {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table.summary th,
        table.summary td {
            text-align: left;
            padding: 10px 12px;
            border-bottom: 1px solid #e5e7eb;
            font-size: 14px;
        }

        table.summary th {
            background-color: #f9fafb;
            color: #722C81;
            text-transform: uppercase;
            font-size: 12px;
        }

        .expiry {
            color: #dc2626;
            font-weight: 600;
        }

        .footer {
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #9ca3af;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <table class="main" width="100%">
            <tr>
                <td class="header">
                    <h1>Employee Document Expiry Summary</h1>
                </td>
            </tr>
            <tr>
                <td class="content">
                    <p>The following employees have documents expiring on
                        <strong>{{ \Carbon\Carbon::parse($targetDay)->format('d M, Y') }}</strong> (7 days from today).
                    </p>

                    <table class="summary">
                        <tr>
                            <th>Employee</th>
                            <th>Email</th>
                            <th>Document</th>
                            <th>Number</th>
                            <th>Expiry Date</th>
                        </tr>
                        @foreach ($expiringList as $item)
                            @if (isset($item['iqama']))
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['email'] }}</td>
                                    <td>Iqama / National ID</td>
                                    <td>{{ $item['iqama'] }}</td>
                                    <td class="expiry">
                                        {{ \Carbon\Carbon::parse($item['iqama_expiry'])->format('d M, Y') }}</td>
                                </tr>
                            @endif
                            @if (isset($item['passport']))
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['email'] }}</td>
                                    <td>Passport</td>
                                    <td>{{ $item['passport'] }}</td>
                                    <td class="expiry">
                                        {{ \Carbon\Carbon::parse($item['passport_expiry'])->format('d M, Y') }}</td>
                                </tr>
                            @endif
                            @if (isset($item['insurance_expiry']))
                                <tr>
                                    <td>{{ $item['name'] }}</td>
                                    <td>{{ $item['email'] }}</td>
                                    <td>Insurance</td>
                                    <td>—</td>
                                    <td class="expiry">
                                        {{ \Carbon\Carbon::parse($item['insurance_expiry'])->format('d M, Y') }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </table>
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
