@php
    /** @var \App\Models\HrOffboardingCase $case */
    $company     = $company ?? (function_exists('company') ? company() : null);
    if (!is_object($company)) { $company = null; }
    $companyName = optional($company)->company_name ?: 'Speed Logi Company';

    $lhFile = public_path('img/speedlogi-letterhead.png');
    $dash   = '—';

    $detail = $employee?->employeeDetail;
    $termination = $case->termination;

    $employeeType = $detail?->employee_type ?: 'expat';
    $isSaudi      = $employeeType === 'saudi';
    $idLabel      = $isSaudi ? 'National ID' : 'Iqama No.';
    $idValue      = $isSaudi ? $detail?->national_id : $detail?->iqama_no;

    $doj = $detail?->joining_date ? \Carbon\Carbon::parse($detail->joining_date)->format('d M Y') : $dash;
    $lwd = $case->last_working_date ? \Carbon\Carbon::parse($case->last_working_date)->format('d M Y') : $dash;

    $tasks = $case->tasks;
    $requiredOpen = $tasks->where('is_required', true)->whereNotIn('status', ['completed', 'waived'])->count();
    $settlementFinal = $settlement && $settlement->status === \App\Models\HrSettlementForm::STATUS_FINAL;
    $accessRevoked = (bool) $case->access_revoked_at;
    $cleared = $requiredOpen === 0 && $settlementFinal && $accessRevoked;

    $statusLabel = fn ($s) => match ($s) {
        'completed' => 'Cleared', 'waived' => 'Waived / N/A', 'blocked' => 'Blocked', default => 'Pending',
    };

    $statutoryActions = [
        'Qiwa contract closed / employment ended',
        'GOSI contributions stopped and final month reported',
        'Iqama cancelled or sponsorship transferred (Muqeem / Absher)',
        'Final exit visa issued (if applicable)',
        'MHRSD / labour office notification',
        'Medical insurance (CCHI) cancelled',
        'Dependants\' iqama / exit handled (if applicable)',
        'WPS / Mudad final salary file submitted',
        'Traffic violations / Absher dues cleared',
        'Personnel file archived and retained',
    ];
    $inputs = $settlement && is_array($settlement->inputs) ? $settlement->inputs : [];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HR Clearance &amp; Offboarding Form - {{ $case->reference ?: $case->id }}</title>
    <style>
        @page { size: letter; margin: 144px 54px 56px 54px; }
        * { box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #2d3748; line-height: 1.4; margin: 0; padding: 0; }
        .lh-bg { position: fixed; top: -144px; left: -54px; width: 612pt; height: 792pt; z-index: -1; }
        .lh-bg img { width: 612pt; height: 792pt; }
        h2.doc-title { font-size: 15px; color: #5b2a86; margin: 0 0 2px; text-align: center; text-transform: uppercase; letter-spacing: 1px; }
        .doc-sub { text-align: center; font-size: 9px; color: #718096; margin-bottom: 10px; }
        .section-title { background: #5b2a86; color: #fff; padding: 4px 9px; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: .4px; margin: 8px 0 0; }
        table.kv { width: 100%; border-collapse: collapse; margin: 0 0 2px; background: transparent; }
        table.kv td { border: 1px solid #d9d2e6; padding: 3px 8px; vertical-align: top; background: transparent; }
        table.kv td.label { width: 22%; color: #6b5b86; font-size: 8.5px; text-transform: uppercase; letter-spacing: .3px; }
        table.kv td.value { width: 28%; font-weight: bold; color: #2d3748; }
        table.grid { width: 100%; border-collapse: collapse; margin-top: 3px; background: transparent; }
        table.grid th, table.grid td { border: 1px solid #d9d2e6; padding: 3px 7px; background: transparent; text-align: left; }
        table.grid th { background: #f4f1f7; color: #6b5b86; font-size: 8px; text-transform: uppercase; }
        ol.checks { margin: 4px 0 0; padding-left: 16px; font-size: 9px; }
        ol.checks li { margin-bottom: 1px; }
        .declaration { margin-top: 8px; padding: 7px 11px; border: 1px solid #d9d2e6; border-left: 3px solid #5b2a86; font-size: 9.5px; text-align: justify; background: transparent; }
        .decision { margin-top: 6px; padding: 6px 10px; border: 1px solid #5b2a86; font-size: 10px; }
        .decision .big { font-size: 13px; font-weight: bold; color: #5b2a86; }
        table.sign { width: 100%; border-collapse: collapse; margin-top: 14px; }
        table.sign td { width: 25%; padding: 0 8px; vertical-align: top; }
        .sign-line { border-top: 1px solid #2d3748; padding-top: 4px; font-size: 8.5px; color: #4a5568; }
        .sign-role { font-weight: bold; color: #5b2a86; text-transform: uppercase; font-size: 8.5px; margin-bottom: 18px; }
        .sign-meta { font-size: 7.5px; color: #718096; margin-top: 3px; }
        .footer-note { margin-top: 12px; font-size: 7.5px; color: #8a7ca6; text-align: center; }
    </style>
</head>
<body>

    @if (is_file($lhFile))
        <div class="lh-bg"><img src="{{ $lhFile }}" alt=""></div>
    @endif

    <div class="content">
        <h2 class="doc-title">HR Clearance &amp; Offboarding Form</h2>
        <div class="doc-sub">
            Reference {{ $case->reference ?: $case->id }} &nbsp;|&nbsp;
            {{ ucfirst((string) $case->exit_type) }} &nbsp;|&nbsp; Generated {{ \Carbon\Carbon::now()->format('d M Y') }}
        </div>

        <div class="section-title">1. Employee &amp; Separation Details</div>
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
                <td class="label">Separation Type</td><td class="value">{{ ucfirst((string) $case->exit_type) }}</td>
                <td class="label">Reason</td><td class="value">{{ $case->reason ?: $dash }}</td>
            </tr>
        </table>

        <div class="section-title">2. Departmental Clearance</div>
        <table class="grid">
            <thead><tr><th style="width:44%">Area</th><th>Owner</th><th>Status</th><th>Cleared by</th><th>Date</th></tr></thead>
            <tbody>
            @foreach($tasks as $task)
                <tr>
                    <td>{{ $task->title }}</td>
                    <td>{{ ucfirst((string) $task->owner_type) }}</td>
                    <td>{{ $statusLabel($task->status) }}{{ $task->is_required ? '' : ' (optional)' }}</td>
                    <td>{{ $task->completedBy?->name ?: ($task->status === 'waived' ? 'Waived' : $dash) }}</td>
                    <td>{{ optional($task->completed_at)->format('d M Y') ?: $dash }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="section-title">3. Statutory &amp; Government Actions (KSA)</div>
        <ol class="checks">
            @foreach($statutoryActions as $line)
                <li>{{ $line }} &nbsp; [ &nbsp; Done &nbsp; / &nbsp; N/A &nbsp; ] &nbsp; Ref: __________</li>
            @endforeach
        </ol>

        <div class="section-title">4. Leave, Entitlements &amp; Final Settlement</div>
        <table class="kv">
            <tr>
                <td class="label">Leave balance (days)</td><td class="value">{{ $inputs['leave_balance_days'] ?? $dash }}</td>
                <td class="label">Leave encashment (SAR)</td><td class="value">{{ isset($inputs['leave_encashment']) ? number_format((float) $inputs['leave_encashment'], 2) : $dash }}</td>
            </tr>
            <tr>
                <td class="label">EOSB (SAR)</td><td class="value">{{ isset($inputs['eosb_amount']) ? number_format((float) $inputs['eosb_amount'], 2) : $dash }}</td>
                <td class="label">Settlement status</td><td class="value">{{ $settlementFinal ? 'Finalised — FC form issued' : ($settlement ? 'Draft' : 'Not started') }}</td>
            </tr>
            <tr>
                <td class="label">Net settlement (SAR)</td>
                <td class="value">{{ $settlement ? number_format((float) $settlement->net_amount, 2) : $dash }}</td>
                <td class="label">Service certificate</td><td class="value">[ &nbsp; Issued &nbsp; ]</td>
            </tr>
        </table>

        <div class="section-title">5. Employee Declaration</div>
        <div class="declaration">
            I confirm that I have returned all Company property and assets in my custody, handed over my work and records,
            settled or acknowledged any amounts due, and that I have no outstanding claim against the Company other than the
            final settlement recorded above. I authorise the Company to recover any amount for which I am liable from my final
            settlement or end-of-service benefits, in accordance with the applicable labour regulations.
            <br><br>
            Forwarding address: ______________________________ &nbsp; Contact: ______________ &nbsp; Personal email: ______________
        </div>

        <div class="section-title">6. HR Clearance Decision</div>
        <div class="decision">
            @if($cleared)
                <span class="big">CLEARED</span> — all required departmental clearances complete, DMS/DOBS access revoked,
                and the final settlement finalised. The employee may be released and deactivated.
            @else
                <span class="big">NOT YET CLEARED</span> — outstanding:
                {{ $requiredOpen > 0 ? $requiredOpen . ' required clearance task(s); ' : '' }}
                {{ !$settlementFinal ? 'final settlement not finalised; ' : '' }}
                {{ !$accessRevoked ? 'linked-system access revocation not confirmed; ' : '' }}
            @endif
            <br>HR remarks: ________________________________________________________________
        </div>

        <table class="sign">
            <tr>
                <td><div class="sign-role">Employee</div><div class="sign-line">{{ $employee?->name ?: 'Name: __________' }}<div class="sign-meta">Signature / Date: __________</div></div></td>
                <td><div class="sign-role">Line Manager</div><div class="sign-line">Name: __________<div class="sign-meta">Signature / Date: __________</div></div></td>
                <td><div class="sign-role">HR Officer</div><div class="sign-line">Name: __________<div class="sign-meta">Signature / Date: __________</div></div></td>
                <td><div class="sign-role">HR Manager</div><div class="sign-line">Name: __________<div class="sign-meta">Signature / Date: __________</div></div></td>
            </tr>
        </table>

        <div class="footer-note">
            {{ $companyName }} &nbsp;|&nbsp; CR 7038950171 &nbsp;|&nbsp; HR Clearance &amp; Offboarding Form
            — {{ $case->reference ?: $case->id }} &nbsp;|&nbsp; System-generated from the offboarding case on {{ \Carbon\Carbon::now()->format('d M Y H:i') }}.
        </div>
    </div>

</body>
</html>
