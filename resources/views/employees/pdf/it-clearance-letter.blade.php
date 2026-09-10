@php
    /** @var \App\Models\EmployeeTermination $termination */
    $company     = $company ?? (function_exists('company') ? company() : null);
    if (!is_object($company)) { $company = null; }
    $companyName = optional($company)->company_name ?: 'Speed Logi Company';

    $lhFile = public_path('img/speedlogi-letterhead.png');
    $dash   = '—';
    $bar    = '#5b2a86';
    $barBg  = '#f4f1f7';

    $detail  = $employee->employeeDetail;
    $isSaudi = ($detail?->employee_type ?: 'expat') === 'saudi';
    $idLabel = $isSaudi ? 'National ID' : 'Iqama No.';
    $idValue = $isSaudi ? $detail?->national_id : $detail?->iqama_no;

    $cd       = $clearanceData ?? [];
    $confirm  = $cd['confirm'] ?? [];
    $decision = $termination->it_clearance_decision;
    $decisionLabels = \App\Support\Clearance::IT_DECISIONS;
    $forms = $returnedForms ?? collect();

    $issuedOn = $termination->it_clearance_issued_at ? \Carbon\Carbon::parse($termination->it_clearance_issued_at)->format('d M Y') : $dash;
    $issuedBy = $termination->itClearanceIssuedBy->name ?? $dash;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IT Clearance - {{ $employee->name }}</title>
    <style>
        @page { size: letter; margin: 144px 54px 56px 54px; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #2d3748; line-height: 1.4; margin: 0; padding: 0; }
        .lh-bg { position: fixed; top: -144px; left: -54px; width: 612pt; height: 792pt; z-index: -1; }
        .lh-bg img { width: 612pt; height: 792pt; }
        h2.doc-title { font-size: 15px; color: {{ $bar }}; margin: 0 0 2px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .doc-sub { text-align: center; font-size: 9px; color: #718096; margin-bottom: 10px; }
        .section-title { background: {{ $bar }}; color: #fff; padding: 4px 9px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .4px; margin: 9px 0 0; }
        table.kv { width: 100%; border-collapse: collapse; margin: 0 0 2px; }
        table.kv td { border: 1px solid #d9d2e6; padding: 3px 8px; vertical-align: top; }
        table.kv td.label { width: 22%; color: #6b5b86; font-size: 8.5px; text-transform: uppercase; }
        table.kv td.value { width: 28%; font-weight: bold; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 3px; }
        table.grid th, table.grid td { border: 1px solid #d9d2e6; padding: 3px 8px; font-size: 9px; }
        table.grid th { background: {{ $barBg }}; color: #6b5b86; text-transform: uppercase; text-align: left; font-size: 8px; }
        .declaration { margin-top: 8px; padding: 7px 11px; border: 1px solid #d9d2e6; border-left: 3px solid {{ $bar }}; font-size: 9.5px; text-align: justify; }
        ul.confirm { margin: 4px 0 0; padding-left: 15px; font-size: 9px; }
        ul.confirm li { margin-bottom: 1px; }
        table.sign { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.sign td { width: 33%; padding: 0 8px; vertical-align: top; }
        .sign-role { font-weight: bold; color: {{ $bar }}; text-transform: uppercase; font-size: 8.5px; margin-bottom: 20px; }
        .sign-line { border-top: 1px solid #2d3748; padding-top: 4px; font-size: 8.5px; color: #4a5568; }
        .sign-meta { font-size: 7.5px; color: #718096; margin-top: 3px; }
        .footer-note { margin-top: 12px; font-size: 7.5px; color: #8a7ca6; text-align: center; }
    </style>
</head>
<body>
    @if (is_file($lhFile))
        <div class="lh-bg"><img src="{{ $lhFile }}" alt=""></div>
    @endif

    <h2 class="doc-title">IT Clearance &amp; Asset Return Summary</h2>
    <div class="doc-sub">
        Reference ITC-{{ str_pad((string) $termination->id, 4, '0', STR_PAD_LEFT) }}
        &nbsp;|&nbsp; Issue date {{ $issuedOn }} &nbsp;|&nbsp; Confidential
    </div>

    <div class="section-title">1. Employee Details</div>
    <table class="kv">
        <tr>
            <td class="label">Employee Name</td><td class="value">{{ $employee->name ?: $dash }}</td>
            <td class="label">Employee ID</td><td class="value">{{ $detail?->employee_id ?: $dash }}</td>
        </tr>
        <tr>
            <td class="label">Designation</td><td class="value">{{ $detail?->designation?->name ?: $dash }}</td>
            <td class="label">Department</td><td class="value">{{ $detail?->department?->team_name ?: $dash }}</td>
        </tr>
        <tr>
            <td class="label">Branch</td><td class="value">{{ $employee->branch?->name ?: $dash }}</td>
            <td class="label">{{ $idLabel }}</td><td class="value">{{ $idValue ?: $dash }}</td>
        </tr>
    </table>

    <div class="section-title">2. Company Assets Returned &amp; Inspected</div>
    <table class="grid">
        <thead><tr><th style="width:12%">RT ref</th><th>Asset</th><th style="width:16%">Serial</th><th>Outcome / grade</th><th style="width:14%; text-align:right;">Recovery (SAR)</th></tr></thead>
        <tbody>
            @forelse ($forms as $rt)
                <tr>
                    <td>{{ $rt->reference }}</td>
                    <td>{{ $rt->asset->name ?? $dash }}</td>
                    <td>{{ $rt->serial->serial_no ?? $dash }}</td>
                    <td>{{ $rt->disposition_notes ?: ucfirst((string) $rt->outcome) }}</td>
                    <td style="text-align:right;">{{ $rt->recommended_recovery_amount !== null ? number_format((float) $rt->recommended_recovery_amount, 2) : $dash }}</td>
                </tr>
            @empty
                <tr><td colspan="5">No assets were assigned / returned through this clearance.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div style="font-size:8px; color:#8a7ca6; margin-top:2px;">
        The detailed Asset Return, Inspection &amp; Clearance form (RT-xxxx) for each item is filed separately with this record.
    </div>

    <div class="section-title">3. IT Data &amp; Security Clearance</div>
    <ul class="confirm">
        @foreach (\App\Support\Clearance::IT_DATA_SECURITY as $key => $label)
            <li>[{{ !empty($confirm[$key]) ? 'x' : ' ' }}] {{ $label }}</li>
        @endforeach
    </ul>

    <div class="section-title">4. Clearance Decision &amp; Certification</div>
    <div class="declaration">
        <strong>Decision:</strong> {{ $decisionLabels[$decision] ?? ucfirst((string) $decision) }}.<br><br>
        This is to certify that all company IT assets issued to <strong>{{ $employee->name }}</strong>
        (Employee ID {{ $detail?->employee_id ?: $dash }}) have been returned and inspected, and that account,
        licence and data-security actions listed above have been completed as part of the offboarding process.
        Any recovery amount recorded above is referred to Finance for deduction from the final settlement.
        @if (!empty($cd['it_remarks']))<br><br><strong>IT remarks:</strong> {{ $cd['it_remarks'] }}@endif
        <br><br>
        <strong>Clearance issued by:</strong> {{ $issuedBy }} &nbsp;&nbsp; <strong>on:</strong> {{ $issuedOn }}
        @if (!empty($cd['inspector_name']))&nbsp;&nbsp; <strong>IT officer:</strong> {{ $cd['inspector_name'] }}@endif
    </div>

    <div class="section-title">5. Signatures</div>
    <table class="sign">
        <tr>
            <td><div class="sign-role">Returned by (Employee)</div>
                <div class="sign-line">{{ $employee->name ?: 'Name: ______________' }}<div class="sign-meta">Signature / Date</div></div></td>
            <td><div class="sign-role">Inspected by (IT)</div>
                <div class="sign-line">{{ ($cd['inspector_name'] ?? '') ?: 'Name: ______________' }}<div class="sign-meta">Signature / Date</div></div></td>
            <td><div class="sign-role">Verified &amp; cleared by (HR)</div>
                <div class="sign-line">Name: ______________<div class="sign-meta">Signature / Date</div></div></td>
        </tr>
    </table>

    <div class="footer-note">
        {{ $companyName }} &nbsp;|&nbsp; CR 7038950171 &nbsp;|&nbsp;
        IT Clearance &amp; Asset Return Summary &mdash; ITC-{{ str_pad((string) $termination->id, 4, '0', STR_PAD_LEFT) }}
        &nbsp;|&nbsp; Confidential Document
    </div>
</body>
</html>
