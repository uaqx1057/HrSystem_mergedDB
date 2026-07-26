<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IT Clearance Letter - {{ $employee->name }}</title>
    <style>
        @page { margin: 100px 25px; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        .header {
            position: fixed;
            top: -70px;
            left: 0;
            right: 0;
            height: 100px;
        }

        .header table { width: 100%; }
        .logo-img { max-height: 100px; max-width: 100%; }
        .brand-name { font-size: 22px; color: #2c3e50; font-weight: bold; text-transform: uppercase; }
        .doc-title { text-align: right; font-size: 18px; color: #7f8c8d; }

        .footer {
            position: fixed;
            bottom: -60px;
            left: 0;
            right: 0;
            height: 50px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #edf2f7;
            padding-top: 8px;
        }

        .container { margin-top: 20px; }

        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table th {
            text-align: left;
            padding: 10px;
            border-bottom: 1px solid #edf2f7;
            color: #7f8c8d;
            width: 30%;
            font-weight: normal;
            text-transform: uppercase;
            font-size: 10px;
        }
        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #edf2f7;
            font-weight: bold;
            color: #2d3748;
        }

        .declaration {
            margin-top: 30px;
            font-size: 12px;
            color: #333;
            text-align: justify;
            background: #fdfdfd;
            padding: 15px;
            border: 1px dashed #cbd5e0;
        }

        .signature-container { margin-top: 60px; width: 100%; }
    </style>
</head>
<body>
    <div class="header">
        <table>
            <tr>
                <td class="brand-name">
                        <img class="logo-img" src="{{ asset('/img/logo.jpeg')}}" alt="{{ company()->company_name }}">
                    
                </td>
                <td class="doc-title">IT Clearance Letter</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        {{ company()->company_name ?? config('app.name') }} &middot; Generated on {{ now()->format('d-m-Y H:i') }} &middot; Confidential Document
    </div>

    <div class="container">
        <table class="info-table">
            <tr>
                <th>Employee Name</th>
                <td>{{ $employee->name }}</td>
            </tr>
            <tr>
                <th>Employee ID</th>
                <td>{{ $employee->employeeDetail->employee_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Designation</th>
                <td>{{ $employee->employeeDetail->designation->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Clearance Issued By</th>
                <td>{{ $termination->itClearanceIssuedBy->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Clearance Issued On</th>
                <td>{{ optional($termination->it_clearance_issued_at)->format('d-m-Y') }}</td>
            </tr>
        </table>

        <div class="declaration">
            This is to certify that all company assets (laptops, mobile devices, access cards and other IT equipment) issued
            to <strong>{{ $employee->name }}</strong> have been returned and verified by the IT Department.
            The employee has been cleared of all IT related dues and obligations as part of the termination/offboarding process.
        </div>

        <div class="signature-container">
            <p>Authorized Signature: ____________________________</p>
        </div>
    </div>
</body>
</html>
