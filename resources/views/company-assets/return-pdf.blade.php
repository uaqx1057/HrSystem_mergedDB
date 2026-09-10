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

    $refNo   = 'RT-' . str_pad($asset->id, 4, '0', STR_PAD_LEFT) . '-' . str_pad(optional($assignment)->id ?? 0, 4, '0', STR_PAD_LEFT);
    $issued  = \Carbon\Carbon::now()->format('d M Y');
    $dash    = '—';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Return Form - {{ $asset->name }}</title>
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
            line-height: 1.44;
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
            margin: 9px 0 0;
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

        table.checks { width: 100%; border-collapse: collapse; margin-top: 4px; background: transparent; }
        table.checks td { border: 1px solid #d9d2e6; padding: 5px 10px; line-height: 1.8; background: transparent; }
        .box { display: inline-block; width: 10px; height: 10px; border: 1px solid #4a5568; margin-right: 5px; }

        ol.terms { margin: 7px 0 0; padding-left: 18px; }
        ol.terms li { margin-bottom: 2px; text-align: justify; }

        .declaration {
            margin-top: 9px;
            padding: 8px 12px;
            background: transparent;
            border: 1px solid #d9d2e6;
            border-left: 3px solid #5b2a86;
            font-size: 10px;
            text-align: justify;
        }

        table.sign { width: 100%; border-collapse: collapse; margin-top: 20px; }
        table.sign td { width: 33.33%; padding: 0 12px; vertical-align: top; }
        .sign-line { border-top: 1px solid #2d3748; padding-top: 5px; font-size: 9.5px; color: #4a5568; }
        .sign-role {
            font-weight: bold;
            color: #5b2a86;
            text-transform: uppercase;
            font-size: 9.5px;
            margin-bottom: 22px;
        }
        .sign-meta { font-size: 8.5px; color: #718096; margin-top: 3px; }
    </style>
</head>
<body>

    @if (is_file($lhFile))
        <div class="lh-bg"><img src="{{ $lhFile }}" alt=""></div>
    @endif

    <div class="content">
        <h2 class="doc-title">Company Asset Return &amp; Handover-Back Form</h2>
        <div class="doc-sub">Reference {{ $refNo }} &nbsp;|&nbsp; Return date {{ $issued }}</div>

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
                <td class="label">Quantity Returned</td>
                <td class="value">{{ optional($assignment)->qty ?? 1 }}</td>
                <td class="label">Originally Issued</td>
                <td class="value">{{ optional($assignment)->qty ?? 1 }}</td>
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

        <div class="section-title">3. Condition on Return (to be completed by HR / IT)</div>
        <table class="checks">
            <tr>
                <td width="42%">
                    <span class="box"></span> Good / working order &nbsp;
                    <span class="box"></span> Minor wear
                </td>
                <td width="58%">
                    <span class="box"></span> Damaged &nbsp;
                    <span class="box"></span> Incomplete / missing &nbsp;
                    <span class="box"></span> Not working
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    Accessories returned: ______________________________________________________________<br>
                    Inspection remarks: _______________________________________________________________<br>
                    Assessed loss / damage amount (if any): __________________________ (SAR)
                </td>
            </tr>
        </table>

        <div class="section-title">4. Return Acknowledgement</div>
        <ol class="terms">
            <li>The employee confirms that the asset(s) described above, together with all issued accessories, have been returned to {{ $companyName }} on the return date shown.</li>
            <li>The Company confirms receipt of the asset(s) in the condition recorded in Section&nbsp;3. This acknowledgement does not waive the Company's right to raise a claim if a defect, loss or shortage is discovered on closer inspection.</li>
            <li>Where loss or damage is attributable to the employee's negligence or misuse, the employee authorises the Company to recover the assessed repair or replacement value from salary, end-of-service benefits or any other amounts due, in accordance with the applicable labour regulations.</li>
            <li>Once signed by both parties with no outstanding claim, the employee is released from custody responsibility for the asset(s) listed in this form.</li>
        </ol>

        <div class="declaration">
            <strong>Declaration:</strong> I, <strong>{{ $empName }}</strong> ({{ $idLabel }}: {{ $idValue ?: $dash }}),
            confirm that I have returned the asset(s) described in this form and that the information recorded above is
            correct to the best of my knowledge.
        </div>

        <table class="sign">
            <tr>
                <td>
                    <div class="sign-role">Returned by (Employee)</div>
                    <div class="sign-line">
                        {{ $empName }}
                        <div class="sign-meta">Signature / Date: ____________________</div>
                    </div>
                </td>
                <td>
                    <div class="sign-role">Received &amp; Inspected by (HR / IT)</div>
                    <div class="sign-line">
                        Name: ____________________
                        <div class="sign-meta">Signature / Date: ____________________</div>
                    </div>
                </td>
                <td>
                    <div class="sign-role">Finance / Clearance</div>
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
