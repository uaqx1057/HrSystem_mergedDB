<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Assignment - {{ $asset->name }}</title>
    <style>
        @page { margin: 100px 25px; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Header Section */
        .header {
            position: fixed;
            top: -60px;
            left: 0;
            right: 0;
            height: 100px;
            /* border-bottom: 2px solid #2c3e50; */
        }

        .header table { width: 100%; }
        .brand-name { font-size: 24px; color: #2c3e50; font-weight: bold; text-transform: uppercase; }
        .doc-title { text-align: right; font-size: 18px; color: #7f8c8d; }

        /* Content Wrapper */
        .container { margin-top: 20px; }

        .section-title {
            background-color: #f8f9fa;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 14px;
            color: #2c3e50;
            border-left: 4px solid #2c3e50;
            margin-bottom: 15px;
            margin-top: 25px;
        }

        /* Data Tables */
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table th {
            text-align: left;
            padding: 10px;
            background-color: #ffffff;
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

        /* Declaration Text */
        .declaration {
            margin-top: 30px;
            font-size: 11px;
            color: #555;
            text-align: justify;
            background: #fdfdfd;
            padding: 15px;
            border: 1px dashed #cbd5e0;
        }

        /* Signature Section */
        .signature-container { margin-top: 60px; width: 100%; }
        .signature-box { width: 45%; float: left; }
        .signature-box.right { float: right; }
        .sig-line { border-top: 1px solid #000; margin-top: 40px; padding-top: 5px; text-align: center; }

        .footer { position: fixed; bottom: -60px; left: 0; right: 0; height: 50px; text-align: center; font-size: 10px; color: #999; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <table>
            <tr>
                <td class="brand-name"></td>
                <td class="doc-title">ASSET ASSIGNMENT FORM</td>
            </tr>
        </table>
    </div>

    <!-- Footer -->
    <div class="footer">
        Generated on {{ date('Y-m-d H:i:s') }} | Confidential Document
    </div>

    <div class="container">

        <!-- Asset Details -->
        <div class="section-title">ASSET INFORMATION</div>
        <table class="info-table">
            <tr>
                <th>Asset Name</th>
                <td>{{ $asset->name }}</td>
                <th>Type / Category</th>
                <td>{{ $asset->type }}</td>
            </tr>
            <tr>
                <th>SKU Number</th>
                <td>{{ $asset->sku_no }}</td>
                <th>Brand</th>
                <td>{{ $asset->brand }}</td>
            </tr>
            <tr>
                <th>Catalog</th>
                <td colspan="3">{{ $asset->catalog }}</td>
            </tr>
        </table>

        @if($assignment)
        <!-- Employee Details -->
        <div class="section-title">ASSIGNMENT DETAILS</div>
        <table class="info-table">
            <tr>
                <th>Employee Name</th>
                <td>{{ $assignment->employee->name ?? 'N/A' }}</td>
                <th>Branch</th>
                <td>{{ $assignment->branch->name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td><span style="color: green;">{{ strtoupper($assignment->status) }}</span></td>
                <th>Iqama / National ID</th>
                <td>{{ $assignment->employee->employeeDetail->iqama_no ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Iqama Expiry</th>
                <td>{{ $assignment->employee->employeeDetail->iqama_expiry_date ?? 'N/A' }}</td>
                <th>Passport Number</th>
                <td>{{ $assignment->employee->employeeDetail->passport_no ?? 'N/A' }}</td>
                {{-- <th>Assignment Date</th>
                <td>{{ $assignment->created_at->format('Y-m-d') }}</td> --}}
            </tr>
        </table>

        <!-- Terms -->
        {{-- <div class="declaration">
            <strong>Declaration:</strong> I hereby acknowledge receipt of the asset mentioned above in good working condition. I understand that this asset is company property and is provided for business use. I agree to maintain it properly and return it upon request or termination of my employment. In case of damage due to negligence, I may be held liable for repair or replacement costs.
        </div> --}}

        <!-- Signatures -->
        <div class="signature-container">
            <div class="signature-box">
                <div class="sig-line">
                    <strong>Employee Signature</strong><br>
                    {{-- Date: ____/____/_______ --}}
                </div>
            </div>
            <div class="signature-box right">
                {{-- <div class="sig-line">
                    <strong>Authorized By (IT/HR)</strong><br>
                    Date: ____/____/_______
                </div> --}}
            </div>
            <div style="clear: both;"></div>
        </div>
        @endif

    </div>

</body>
</html>
