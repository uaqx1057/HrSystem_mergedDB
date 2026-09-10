@php
    /** @var \App\Models\HrSettlementForm $form */
    $company     = $company ?? (function_exists('company') ? company() : null);
    if (!is_object($company)) { $company = null; }
    $companyName = optional($company)->company_name ?: 'Speed Logi Company';
    // Pulled from Company > default address tax number when set; left off until then.
    $vatNumber   = $company ? optional($company->defaultAddress)->tax_number : null;

    $lhFile = public_path('img/speedlogi-letterhead.png');
    $dash   = '—';

    $termination = $form->termination;
    $employee    = $termination?->employee;
    $detail      = $employee?->employeeDetail;

    $employeeType = $detail?->employee_type ?: 'expat';
    $isSaudi      = $employeeType === 'saudi';
    $idLabel      = $isSaudi ? 'National ID' : 'Iqama No.';
    $idValue      = $isSaudi ? $detail?->national_id : $detail?->iqama_no;

    $inputs   = is_array($form->inputs) ? $form->inputs : [];
    $wage     = (float) ($inputs['monthly_wage'] ?? 0);
    $wageBasis = $inputs['wage_basis'] ?? null;

    $payables     = $form->lineItems->where('kind', 'payable');
    $recoverables = $form->lineItems->where('kind', 'recoverable');

    $totalB = (float) $form->total_payable;
    $totalA = (float) $form->total_recoverable;
    $net    = (float) $form->net_amount;
    $result = $net > 0 ? 'PAYABLE TO EMPLOYEE' : ($net < 0 ? 'RECOVERABLE FROM EMPLOYEE' : 'NIL — FULLY SETTLED');

    $finalizedAt = $form->finalized_at ? \Carbon\Carbon::parse($form->finalized_at)->format('d M Y H:i') : $dash;
    $lwd = $termination?->last_working_date ? \Carbon\Carbon::parse($termination->last_working_date)->format('d M Y') : $dash;
    $doj = $detail?->joining_date ? \Carbon\Carbon::parse($detail->joining_date)->format('d M Y') : $dash;

    $exitLabels = [
        'termination' => 'Termination by employer',
        'resignation' => 'Resignation',
    ];

    $verificationChecklist = [
        'Employment record and last working day confirmed',
        'Basic salary / wage basis verified against payroll setup',
        'End-of-service benefit calculated per the stated policy version',
        'Leave balance reconciled with approved leave records',
        'All salary advances and staff loans accounted for',
        'Asset return / recovery forms received and reconciled',
        'Payroll stop instruction issued for the final period',
        'GOSI / statutory contributions updated',
        'Bank account details for the net payment confirmed',
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Finance Clearance &amp; Dues Settlement - {{ $employee?->name }}</title>
    <style>
        @page { size: letter; margin: 144px 54px 56px 54px; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #2d3748; line-height: 1.4; margin: 0; padding: 0; }

        .lh-bg { position: fixed; top: -144px; left: -54px; width: 612pt; height: 792pt; z-index: -1; }
        .lh-bg img { width: 612pt; height: 792pt; }

        h2.doc-title { font-size: 15px; color: #5b2a86; margin: 0 0 2px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .doc-sub { text-align: center; font-size: 9px; color: #718096; margin-bottom: 10px; }

        .section-title { background: #5b2a86; color: #fff; padding: 4px 9px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .4px; margin: 9px 0 0; }

        table.kv { width: 100%; border-collapse: collapse; margin: 0 0 2px; background: transparent; }
        table.kv td { border: 1px solid #d9d2e6; padding: 3px 8px; vertical-align: top; background: transparent; }
        table.kv td.label { width: 22%; color: #6b5b86; font-size: 8.5px; text-transform: uppercase; letter-spacing: .3px; }
        table.kv td.value { width: 28%; font-weight: bold; color: #2d3748; }

        table.money { width: 100%; border-collapse: collapse; margin-top: 3px; background: transparent; }
        table.money th, table.money td { border: 1px solid #d9d2e6; padding: 3px 8px; background: transparent; }
        table.money th { background: #f4f1f7; color: #6b5b86; font-size: 8.5px; text-transform: uppercase; text-align: left; }
        table.money td.amt { text-align: right; font-weight: bold; width: 22%; }
        table.money tr.total td { background: #f4f1f7; font-weight: bold; text-transform: uppercase; font-size: 9px; }
        table.money td.src { font-size: 8px; color: #8a7ca6; width: 16%; }

        .net { margin-top: 6px; border: 1px solid #5b2a86; padding: 6px 10px; font-size: 11px; }
        .net .big { font-size: 14px; font-weight: bold; color: #5b2a86; }

        ol.checks { margin: 4px 0 0; padding-left: 16px; font-size: 9px; }
        ol.checks li { margin-bottom: 1px; }

        .declaration { margin-top: 8px; padding: 7px 11px; background: transparent; border: 1px solid #d9d2e6; border-left: 3px solid #5b2a86; font-size: 9.5px; text-align: justify; }

        table.sign { width: 100%; border-collapse: collapse; margin-top: 16px; }
        table.sign td { width: 25%; padding: 0 8px; vertical-align: top; }
        .sign-line { border-top: 1px solid #2d3748; padding-top: 4px; font-size: 8.5px; color: #4a5568; }
        .sign-role { font-weight: bold; color: #5b2a86; text-transform: uppercase; font-size: 8.5px; margin-bottom: 20px; }
        .sign-meta { font-size: 7.5px; color: #718096; margin-top: 3px; }
        .footer-note { margin-top: 12px; font-size: 7.5px; color: #8a7ca6; text-align: center; }
    </style>
</head>
<body>

    @if (is_file($lhFile))
        <div class="lh-bg"><img src="{{ $lhFile }}" alt=""></div>
    @endif

    <div class="content">
        <h2 class="doc-title">Finance Clearance &amp; Dues Settlement</h2>
        <div class="doc-sub">
            Settlement {{ $form->id }} &nbsp;|&nbsp; Policy {{ $form->policy_version }} &nbsp;|&nbsp; Finalised {{ $finalizedAt }}
        </div>

        <div class="section-title">1. Employee &amp; Employment Details</div>
        <table class="kv">
            <tr>
                <td class="label">Employee Name</td><td class="value">{{ $employee?->name ?: $dash }}</td>
                <td class="label">Employee ID</td><td class="value">{{ $detail?->employee_id ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">{{ $idLabel }}</td><td class="value">{{ $idValue ?: $dash }}</td>
                <td class="label">Designation</td><td class="value">{{ $detail?->designation?->name ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Department</td><td class="value">{{ $detail?->department?->team_name ?: $dash }}</td>
                <td class="label">Branch</td><td class="value">{{ $employee?->branch?->name ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Date of Joining</td><td class="value">{{ $doj }}</td>
                <td class="label">Last Working Day</td><td class="value">{{ $lwd }}</td>
            </tr>
            <tr>
                <td class="label">Reason for Clearance</td>
                <td class="value">{{ $exitLabels[$termination?->exit_type] ?? ucfirst((string) $termination?->exit_type) ?: $dash }}</td>
                <td class="label">Monthly Wage (SAR)</td>
                <td class="value">{{ $wage > 0 ? number_format($wage, 2) . ($wageBasis ? ' (' . $wageBasis . ')' : '') : $dash }}</td>
            </tr>
        </table>

        <div class="section-title">2. Amounts Recoverable from Employee</div>
        <table class="money">
            <thead><tr><th>Item</th><th>Source</th><th style="text-align:right;">Amount (SAR)</th></tr></thead>
            <tbody>
                @forelse($recoverables as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="src">{{ $item->source_type ? 'Auto' : 'Manual' }}</td>
                        <td class="amt">{{ number_format((float) $item->amount, 2) }}</td>
                    </tr>
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
                @forelse($payables as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td class="src">{{ $item->source_type ? 'Auto' : 'Manual' }}</td>
                        <td class="amt">{{ number_format((float) $item->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3">No payable amounts.</td></tr>
                @endforelse
                <tr class="total"><td colspan="2">Total Payable (B)</td><td class="amt">{{ number_format($totalB, 2) }}</td></tr>
            </tbody>
        </table>

        <div class="section-title">4. Net Settlement Summary</div>
        <div class="net">
            Total Payable (B) {{ number_format($totalB, 2) }} &nbsp;&minus;&nbsp; Total Recoverable (A) {{ number_format($totalA, 2) }}
            &nbsp;=&nbsp; <span class="big">SAR {{ number_format($net, 2) }}</span> &nbsp; &mdash; &nbsp; <strong>{{ $result }}</strong>
        </div>

        <div class="section-title">5. Finance Verification Checklist</div>
        <ol class="checks">
            @foreach($verificationChecklist as $line)
                <li>{{ $line }}</li>
            @endforeach
        </ol>

        <div class="section-title">6. Clearance Status &amp; Certification</div>
        <div class="declaration">
            Finance certifies that the amounts above have been reviewed against payroll records, approved advances, asset
            recovery forms and the stated end-of-service policy version, and that the net settlement figure is correct as of
            the finalisation date. Auto-derived lines are computed by the system from source records; manual lines have been
            entered and verified by Finance. This settlement is <strong>{{ $totalA > 0 ? 'CLEARED WITH RECOVERY' : 'CLEARED' }}</strong>.
            <br><br>
            <strong>Policy note:</strong> End-of-service and leave-encashment figures are produced under policy version
            <strong>{{ $form->policy_version }}</strong> and are subject to the Company's approved interpretation of the Saudi
            Labour Law (Articles 84, 85, 111 and related provisions).
        </div>

        <table class="sign">
            <tr>
                <td>
                    <div class="sign-role">Prepared by (Accounts)</div>
                    <div class="sign-line">{{ $form->preparedBy?->name ?: 'Name: ______________' }}
                        <div class="sign-meta">Signature / Date: ____________</div>
                    </div>
                </td>
                <td>
                    <div class="sign-role">Verified by (Finance)</div>
                    <div class="sign-line">{{ $form->finalizedBy?->name ?: 'Name: ______________' }}
                        <div class="sign-meta">Signature / Date: ____________</div>
                    </div>
                </td>
                <td>
                    <div class="sign-role">Acknowledged by (Employee)</div>
                    <div class="sign-line">{{ $employee?->name ?: 'Name: ______________' }}
                        <div class="sign-meta">Signature / Date: ____________</div>
                    </div>
                </td>
                <td>
                    <div class="sign-role">Approved by (HR / Mgmt)</div>
                    <div class="sign-line">Name: ______________
                        <div class="sign-meta">Signature / Date: ____________</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            {{ $companyName }} &nbsp;|&nbsp; CR 7038950171@if($vatNumber) &nbsp;|&nbsp; VAT {{ $vatNumber }}@endif
            &nbsp;|&nbsp; Finance Clearance &amp; Dues Settlement — Settlement {{ $form->id }}
            &nbsp;|&nbsp; Immutable record. SHA-256: {{ $form->document_hash ?: 'pending' }}
        </div>
    </div>

</body>
</html>
