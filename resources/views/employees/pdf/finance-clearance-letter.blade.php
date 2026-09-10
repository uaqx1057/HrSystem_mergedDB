@php
    /** @var \App\Models\EmployeeTermination $termination */
    /** @var \App\Models\HrSettlementForm|null $settlement */
    $company     = $company ?? (function_exists('company') ? company() : null);
    if (!is_object($company)) { $company = null; }
    $companyName = optional($company)->company_name ?: 'Speed Logi Company';

    $lhFile = public_path('img/speedlogi-letterhead.png');
    $dash   = '—';
    $bar    = '#2f7d4f';   // FC form section-bar green
    $barBg  = '#eef6f0';

    $detail  = $employee->employeeDetail;
    $isSaudi = ($detail?->employee_type ?: 'expat') === 'saudi';
    $idLabel = $isSaudi ? 'National ID' : 'Iqama No.';
    $idValue = $isSaudi ? $detail?->national_id : $detail?->iqama_no;

    $cd        = $clearanceData ?? [];
    $checklist = $cd['checklist'] ?? [];
    $decision  = $termination->finance_clearance_decision;
    $decisionLabels = \App\Support\Clearance::FINANCE_DECISIONS;

    $payables     = $settlement ? $settlement->lineItems->where('kind', 'payable') : collect();
    $recoverables = $settlement ? $settlement->lineItems->where('kind', 'recoverable') : collect();
    $totalB = $settlement ? (float) $settlement->total_payable : 0.0;
    $totalA = $settlement ? (float) $settlement->total_recoverable : 0.0;
    $net    = $settlement ? (float) $settlement->net_amount : 0.0;
    $result = $net > 0 ? 'PAYABLE TO EMPLOYEE' : ($net < 0 ? 'RECOVERABLE FROM EMPLOYEE' : 'NIL — FULLY SETTLED');

    $issuedOn  = $termination->finance_clearance_issued_at ? \Carbon\Carbon::parse($termination->finance_clearance_issued_at)->format('d M Y') : $dash;
    $issuedBy  = $termination->financeClearanceIssuedBy->name ?? $dash;
    $lwd = $termination->last_working_date ? \Carbon\Carbon::parse($termination->last_working_date)->format('d M Y') : $dash;
    $doj = $detail?->joining_date ? \Carbon\Carbon::parse($detail->joining_date)->format('d M Y') : $dash;
    $pm  = (($cd['payment_method'] ?? '') === 'Other' && !empty($cd['payment_method_other'])) ? $cd['payment_method_other'] : (($cd['payment_method'] ?? '') ?: $dash);

    $ans = fn ($v) => $v === 'yes' ? 'Yes' : ($v === 'no' ? 'No' : ($v === 'na' ? 'N/A' : $dash));
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Clearance &amp; Dues Settlement - {{ $employee->name }}</title>
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
        table.kv td { border: 1px solid #cfe2d5; padding: 3px 8px; vertical-align: top; }
        table.kv td.label { width: 22%; color: #3f6b4f; font-size: 8.5px; text-transform: uppercase; }
        table.kv td.value { width: 28%; font-weight: bold; }
        table.money { width: 100%; border-collapse: collapse; margin-top: 3px; }
        table.money th, table.money td { border: 1px solid #cfe2d5; padding: 3px 8px; }
        table.money th { background: {{ $barBg }}; color: #3f6b4f; font-size: 8.5px; text-transform: uppercase; text-align: left; }
        table.money td.amt { text-align: right; font-weight: bold; width: 22%; }
        table.money tr.total td { background: {{ $barBg }}; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        .net { margin-top: 6px; border: 1px solid {{ $bar }}; padding: 6px 10px; font-size: 11px; }
        .net .big { font-size: 14px; font-weight: bold; color: {{ $bar }}; }
        table.checks { width: 100%; border-collapse: collapse; margin-top: 3px; }
        table.checks th, table.checks td { border: 1px solid #cfe2d5; padding: 3px 8px; font-size: 9px; }
        table.checks th { background: {{ $barBg }}; color: #3f6b4f; text-transform: uppercase; text-align: left; font-size: 8px; }
        .declaration { margin-top: 8px; padding: 7px 11px; border: 1px solid #cfe2d5; border-left: 3px solid {{ $bar }}; font-size: 9.5px; text-align: justify; }
        table.sign { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.sign td { width: 25%; padding: 0 8px; vertical-align: top; }
        .sign-role { font-weight: bold; color: {{ $bar }}; text-transform: uppercase; font-size: 8.5px; margin-bottom: 20px; }
        .sign-line { border-top: 1px solid #2d3748; padding-top: 4px; font-size: 8.5px; color: #4a5568; }
        .sign-meta { font-size: 7.5px; color: #718096; margin-top: 3px; }
        .footer-note { margin-top: 12px; font-size: 7.5px; color: #3f6b4f; text-align: center; }
    </style>
</head>
<body>
    @if (is_file($lhFile))
        <div class="lh-bg"><img src="{{ $lhFile }}" alt=""></div>
    @endif

    <h2 class="doc-title">Finance Clearance &amp; Dues Settlement Form</h2>
    <div class="doc-sub">
        Reference FC-{{ str_pad((string) $termination->id, 4, '0', STR_PAD_LEFT) }}
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
        <tr>
            <td class="label">Date of Joining</td><td class="value">{{ $doj }}</td>
            <td class="label">Last Working Day</td><td class="value">{{ $lwd }}</td>
        </tr>
        <tr>
            <td class="label">Reason for Clearance</td>
            <td class="value">{{ ucfirst((string) $termination->exit_type) ?: $dash }}</td>
            <td class="label">Settlement Policy</td>
            <td class="value">{{ $settlement?->policy_version ?: $dash }}</td>
        </tr>
    </table>

    <div class="section-title">2. Amounts Recoverable from Employee</div>
    <table class="money">
        <thead><tr><th>Item</th><th>Source</th><th style="text-align:right;">Amount (SAR)</th></tr></thead>
        <tbody>
            @forelse ($recoverables as $li)
                <tr><td>{{ $li->description }}</td><td>{{ $li->source_type ? 'Auto' : 'Manual' }}</td><td class="amt">{{ number_format((float) $li->amount, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">No recoverable amounts.</td></tr>
            @endforelse
            <tr class="total"><td colspan="2">Total Recoverable (A)</td><td class="amt">{{ number_format($totalA, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="section-title">3. Amounts Payable to Employee</div>
    <table class="money">
        <thead><tr><th>Item</th><th>Source</th><th style="text-align:right;">Amount (SAR)</th></tr></thead>
        <tbody>
            @forelse ($payables as $li)
                <tr><td>{{ $li->description }}</td><td>{{ $li->source_type ? 'Auto' : 'Manual' }}</td><td class="amt">{{ number_format((float) $li->amount, 2) }}</td></tr>
            @empty
                <tr><td colspan="3">No payable amounts.</td></tr>
            @endforelse
            <tr class="total"><td colspan="2">Total Payable (B)</td><td class="amt">{{ number_format($totalB, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="section-title">4. Net Settlement &amp; Payment</div>
    <div class="net">
        Total Payable (B) {{ number_format($totalB, 2) }} &nbsp;&minus;&nbsp; Total Recoverable (A) {{ number_format($totalA, 2) }}
        &nbsp;=&nbsp; <span class="big">SAR {{ number_format($net, 2) }}</span> &nbsp;&mdash;&nbsp; <strong>{{ $result }}</strong>
    </div>
    <table class="kv" style="margin-top:4px;">
        <tr>
            <td class="label">Payment Method</td><td class="value">{{ $pm ?: $dash }}</td>
            <td class="label">Bank Name</td><td class="value">{{ $cd['bank_name'] ?? $dash }}</td>
        </tr>
        <tr>
            <td class="label">IBAN</td><td class="value" colspan="3">{{ $cd['iban'] ?? $dash }}</td>
        </tr>
    </table>

    <div class="section-title">5. Finance Verification Checklist</div>
    <table class="checks">
        <thead><tr><th style="width:5%">#</th><th style="width:52%">Verification point</th><th style="width:12%">Answer</th><th>Remarks</th></tr></thead>
        <tbody>
            @foreach (\App\Support\Clearance::FINANCE_VERIFICATION as $i => $label)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $label }}</td>
                    <td>{{ $ans($checklist[$i]['answer'] ?? null) }}</td>
                    <td>{{ $checklist[$i]['remarks'] ?? '' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">6. Clearance Status &amp; Certification</div>
    <div class="declaration">
        <strong>Decision:</strong> {{ $decisionLabels[$decision] ?? ucfirst((string) $decision) }}.<br><br>
        This is to certify that <strong>{{ $employee->name }}</strong> (Employee ID {{ $detail?->employee_id ?: $dash }})
        has been reviewed by the Finance Department as part of the offboarding process. Subject to the amounts recorded
        above, all financial obligations have been settled and verified. Where a recoverable balance remains, the employee
        acknowledges the Company's right to deduct it from the final settlement, end-of-service benefits or any other
        amounts due, in accordance with the applicable labour regulations.
        @if (!empty($cd['finance_remarks']))<br><br><strong>Finance remarks:</strong> {{ $cd['finance_remarks'] }}@endif
        <br><br>
        <strong>Clearance issued by:</strong> {{ $issuedBy }} &nbsp;&nbsp; <strong>on:</strong> {{ $issuedOn }}
    </div>

    <div class="section-title">7. Signatures</div>
    <table class="sign">
        <tr>
            <td><div class="sign-role">Prepared by (Accounts)</div>
                <div class="sign-line">{{ ($cd['prepared_by'] ?? '') ?: 'Name: ______________' }}<div class="sign-meta">Signature / Date</div></div></td>
            <td><div class="sign-role">Verified by (Finance Mgr)</div>
                <div class="sign-line">{{ ($cd['verified_by'] ?? '') ?: 'Name: ______________' }}<div class="sign-meta">Signature / Date</div></div></td>
            <td><div class="sign-role">Acknowledged by (Employee)</div>
                <div class="sign-line">{{ $employee->name ?: 'Name: ______________' }}<div class="sign-meta">Signature / Date</div></div></td>
            <td><div class="sign-role">Approved by (HR / Mgmt)</div>
                <div class="sign-line">Name: ______________<div class="sign-meta">Signature / Date</div></div></td>
        </tr>
    </table>

    <div class="footer-note">
        {{ $companyName }} &nbsp;|&nbsp; CR 7038950171 &nbsp;|&nbsp;
        Finance Clearance &amp; Dues Settlement Form &mdash; FC-{{ str_pad((string) $termination->id, 4, '0', STR_PAD_LEFT) }}
        &nbsp;|&nbsp; Confidential Document
    </div>
</body>
</html>
