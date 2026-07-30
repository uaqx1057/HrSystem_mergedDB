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
        .label { font-weight: bold; color: #718096; width: 130px; }
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
                <div class="header-text">Asset Assignment Assigned</div>

                <p>Dear <strong>{{ $assignment->employee->name }}</strong>,</p>

                <p>This is a formal notification to confirm that a company asset has been successfully assigned to you. We have received and recorded your digital signature for this assignment.</p>

                <table class="details-table">
                    <tr>
                        <td class="label">Catalog:</td>
                        <td class="value">{{ $assignment->asset->catalog }}</td>
                    </tr>
                    <tr>
                        <td class="label">Asset Name:</td>
                        <td class="value">{{ $assignment->asset->name }}</td>
                    </tr>
                    <tr>
                        <td class="label">Brand:</td>
                        <td class="value">{{ $assignment->asset->brand }}</td>
                    </tr>
                    <tr>
                        <td class="label">Type:</td>
                        <td class="value">{{ $assignment->asset->type }}</td>
                    </tr>
                    <tr>
                        <td class="label">SKU/Serial No:</td>
                        <td class="value">{{ $assignment->asset->sku_no ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Serial Number:</td>
                        <td class="value">{{ $assignment->serial_no ?? 'N/A' }}</td>
                    </tr>

                    <tr>
                        <td class="label">Status:</td>
                        <td class="value"><span style="color: #38a169;">{{ $assignment->status }}</span></td>
                    </tr>
                </table>

                <p>Please click the button below to view your signed document and manage your assigned assets.</p>

                <div class="btn-container">
                    <a href="{{ route('company-assets.show', $assignment->asset->id) }}" class="btn">View My Assets</a>
                </div>

                <p style="margin-top: 30px; font-size: 13px; color: #4a5568;">
                    Note: Please ensure this equipment is handled in accordance with company policy. For any technical issues or questions, please contact the IT department.
                </p>

                <p style="margin-top: 20px;">
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
