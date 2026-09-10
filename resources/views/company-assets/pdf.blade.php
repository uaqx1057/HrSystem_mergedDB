@php
    $company     = $company ?? (function_exists('company') ? company() : null);
    if (!is_object($company)) { $company = null; }
    $companyName  = optional($company)->company_name ?: 'Speed Logi Company';

    $lhFile = public_path('img/speedlogi-letterhead.png');

    $employee     = optional($assignment)->employee;
    $detail       = $employee?->employeeDetail;
    $employeeType = $detail?->employee_type ?: 'expat';
    $isSaudi      = $employeeType === 'saudi';
    $idLabel      = $isSaudi ? 'National ID' : 'Iqama No.';
    $idValue      = $isSaudi ? $detail?->national_id : $detail?->iqama_no;
    $idExpiry     = $isSaudi ? $detail?->national_id_expiry_date : $detail?->iqama_expiry_date;

    // Employee (custodian) fields come strictly from the employee's own HR record
    // (never from the asset) so nothing is mis-attributed. Same mapping the
    // employee profile screen uses; blank fields render as an em dash.
    $empName   = $employee?->name ?: 'N/A';
    $empCode   = $detail?->employee_id;
    $empMobile = $employee?->mobile_with_phonecode ?: $employee?->mobile;
    $empDesig  = $detail?->designation?->name;              // HR designation, not iqama profession
    $empDept   = $detail?->department?->team_name;          // employee's department (Team.team_name)
    $empBranch = $employee?->branch?->name;                 // employee's branch

    $refNo   = 'HO-' . str_pad($asset->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad(optional($assignment)->id ?? 0, 4, '0', STR_PAD_LEFT);
    $issued  = \Carbon\Carbon::now()->format('d M Y');
    $dash    = '—';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Handover Form - {{ $asset->name }}</title>
    <style>
        /* Letter page. Margins keep all content inside the pre-printed
           letterhead's blank area: below its header band, with a 1.5in top
           margin. This letterhead has NO footer, so the bottom margin is just
           a normal print margin. Nothing is added to the page edges - the
           letterhead already carries the branding. */
        @page { size: letter; margin: 144px 60px 60px 60px; }

        * { box-sizing: border-box; }

        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #2d3748;
            line-height: 1.46;
            margin: 0;
            padding: 0;
        }

        /* Pre-printed company letterhead as a full-page background on every page.
           DomPDF anchors position:fixed to the content box (inside @page margins),
           so the negative offsets pull the image back to the physical page corner.
           z-index:-1 keeps it BEHIND the flowing content. */
        .lh-bg {
            position: fixed;
            top: -144px;
            left: -60px;
            width: 612pt;
            height: 792pt;
            z-index: -1;
        }
        .lh-bg img { width: 612pt; height: 792pt; }

        h2.doc-title {
            font-size: 16px;
            color: #5b2a86;
            margin: 0 0 2px;
            text-align: center;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .doc-sub {
            text-align: center;
            font-size: 9.5px;
            color: #718096;
            margin-bottom: 12px;
        }

        .section-title {
            background: #5b2a86;
            color: #fff;
            padding: 5px 10px;
            font-size: 10.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin: 10px 0 0;
        }

        /* Cells kept transparent so the letterhead texture is never masked in blocks. */
        table.kv { width: 100%; border-collapse: collapse; margin: 0 0 3px; background: transparent; }
        table.kv td { border: 1px solid #d9d2e6; padding: 4px 9px; vertical-align: top; background: transparent; }
        table.kv td.label {
            width: 20%;
            color: #6b5b86;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .3px;
        }
        table.kv td.value { width: 30%; font-weight: bold; color: #2d3748; }

        ol.terms { margin: 7px 0 0; padding-left: 18px; }
        ol.terms li { margin-bottom: 3px; text-align: justify; }

        .declaration {
            margin-top: 10px;
            padding: 8px 12px;
            background: transparent;
            border: 1px solid #d9d2e6;
            border-left: 3px solid #5b2a86;
            font-size: 10px;
            text-align: justify;
        }

        table.sign { width: 100%; border-collapse: collapse; margin-top: 24px; }
        table.sign td { width: 33.33%; padding: 0 12px; vertical-align: top; }
        .sign-line { border-top: 1px solid #2d3748; padding-top: 5px; font-size: 9.5px; color: #4a5568; }
        .sign-role {
            font-weight: bold;
            color: #5b2a86;
            text-transform: uppercase;
            font-size: 9.5px;
            margin-bottom: 34px;
        }
        .sign-meta { font-size: 8.5px; color: #718096; margin-top: 3px; }
    </style>
</head>
<body>

    @if (is_file($lhFile))
        <div class="lh-bg"><img src="{{ $lhFile }}" alt=""></div>
    @endif

    <div class="content">
        <h2 class="doc-title">Company Asset Handover &amp; Custody Form</h2>
        <div class="doc-sub">Reference {{ $refNo }} &nbsp;|&nbsp; Issue date {{ $issued }}</div>

        <div class="section-title">1. Asset Details</div>
        <table class="kv">
            <tr>
                <td class="label">Asset Name</td>
                <td class="value">{{ $asset->name ?: $dash }}</td>
                <td class="label">Serial Number</td>
                <td class="value">{{ optional($assignment)->serial_no ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Type / Category</td>
                <td class="value">{{ $asset->type ?: $dash }}</td>
                <td class="label">Brand</td>
                <td class="value">{{ $asset->brand ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">SKU Number</td>
                <td class="value">{{ $asset->sku_no ?: $dash }}</td>
                <td class="label">Catalog</td>
                <td class="value">{{ $asset->catalog ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Department</td>
                <td class="value">{{ optional($asset->department)->name ?: $dash }}</td>
                <td class="label">Branch</td>
                <td class="value">{{ optional($asset->branch)->name ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Quantity Issued</td>
                <td class="value">{{ optional($assignment)->qty ?? 1 }}</td>
                <td class="label">Condition at Handover</td>
                <td class="value">New / Good working order</td>
            </tr>
        </table>

        <div class="section-title">2. Employee (Custodian) Details</div>
        <table class="kv">
            <tr>
                <td class="label">Employee Name</td>
                <td class="value">{{ $empName ?: $dash }}</td>
                <td class="label">Employee ID</td>
                <td class="value">{{ $empCode ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">{{ $idLabel }}</td>
                <td class="value">{{ $idValue ?: $dash }}</td>
                <td class="label">{{ $idLabel }} Expiry</td>
                <td class="value">{{ $idExpiry ? \Carbon\Carbon::parse($idExpiry)->format('d M Y') : $dash }}</td>
            </tr>
            <tr>
                <td class="label">Designation</td>
                <td class="value">{{ $empDesig ?: $dash }}</td>
                <td class="label">Mobile</td>
                <td class="value">{{ $empMobile ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Department</td>
                <td class="value">{{ $empDept ?: $dash }}</td>
                <td class="label">Branch</td>
                <td class="value">{{ $empBranch ?: $dash }}</td>
            </tr>
        </table>

        <div class="section-title">3. Terms &amp; Conditions of Custody</div>
        <ol class="terms">
            <li>I acknowledge that I have received the asset(s) listed in Section&nbsp;1 in good working condition, complete with all standard accessories.</li>
            <li>The asset(s) remain at all times the exclusive property of {{ $companyName }} and are issued to me solely for the performance of my official duties.</li>
            <li>I will keep the asset(s) in my personal custody, use them with due care, and will not lend, transfer, sell, pledge, modify or allow any third party to use them without prior written approval from the Company.</li>
            <li>I will not install unlicensed software or store unlawful, offensive or non-work-related material on any Company device issued to me.</li>
            <li>I will promptly report any loss, theft, damage or malfunction of the asset(s) to the HR / IT department, and in the case of theft will also file a report with the competent authorities.</li>
            <li><strong>[Clause&nbsp;6]</strong> In the event of loss or damage caused by my negligence, misuse or failure to follow Company policy, I authorise the Company to recover the repair or replacement value from my salary, end-of-service benefits or any other amounts due to me, in accordance with the applicable labour regulations. This is the clause referenced by the Company Asset Return, Inspection &amp; Clearance Form.</li>
            <li>I will return the asset(s) in good condition (fair wear and tear excepted) immediately upon the Company's request, on transfer, or on the last working day of my employment, whichever is earlier. Final settlement / clearance may be withheld until all assets are returned.</li>
            <li>This form is issued in duplicate; one signed copy is retained by the Company and one is provided to the employee.</li>
        </ol>

        <div class="declaration">
            <strong>Declaration:</strong> I, <strong>{{ $empName }}</strong> ({{ $idLabel }}: {{ $idValue ?: $dash }}),
            confirm that I have read, understood and agree to the terms and conditions stated above, and I accept
            responsibility for the asset(s) described in this form from the date of my signature below.
        </div>

        <table class="sign">
            <tr>
                <td style="width: 50%;">
                    <div class="sign-role">Received by (Employee)</div>
                    <div class="sign-line">
                        {{ $empName }}
                        <div class="sign-meta">Signature / Date: ____________________</div>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="sign-role">Issued by (HR / IT)</div>
                    <div class="sign-line">
                        Name: ____________________
                        <div class="sign-meta">Signature / Date: ____________________</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
