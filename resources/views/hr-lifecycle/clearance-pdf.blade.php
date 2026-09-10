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

    $hd        = is_array($case->hr_clearance_data) ? $case->hr_clearance_data : [];
    $issued    = ($case->hr_clearance_status ?? 'pending') === 'issued';
    $sep       = $hd['separation'] ?? [];
    $handover  = $hd['handover'] ?? [];
    $statutory = $hd['statutory'] ?? [];
    $ent       = $hd['entitlements'] ?? [];
    $decisionLabels = \App\Support\Clearance::HR_DECISIONS;
    $decision  = $case->hr_clearance_decision;

    $tasks = $case->tasks;
    $settlementFinal = $settlement && $settlement->status === \App\Models\HrSettlementForm::STATUS_FINAL;

    $statusLabel = fn ($s) => match ($s) {
        'completed' => 'Cleared', 'waived' => 'Waived / N/A', 'blocked' => 'Blocked', default => 'Pending',
    };
    $tick = fn ($v) => $v === 'done' ? '[x] Done' : ($v === 'na' ? '[ ] N/A' : $dash);
    $entShow = fn ($k) => ($ent[$k] ?? '') !== '' ? ucfirst((string) $ent[$k]) : $dash;
    $noticeLabel = $case->notice_type === 'immediate' ? 'Immediate effect' : (($case->notice_months ?: '?') . '-month notice');
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
        table.grid th, table.grid td { border: 1px solid #d9d2e6; padding: 3px 7px; background: transparent; text-align: left; font-size: 9px; }
        table.grid th { background: #f4f1f7; color: #6b5b86; font-size: 8px; text-transform: uppercase; }
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
            {{ ucfirst((string) $case->exit_type) }} &nbsp;|&nbsp;
            {{ $issued ? 'Issued ' . optional($case->hr_cleared_at)->format('d M Y') : 'Generated ' . \Carbon\Carbon::now()->format('d M Y') }}
        </div>

        <div class="section-title">1. Employee &amp; Separation Details</div>
        <table class="kv">
            <tr>
                <td class="label">Employee Name</td><td class="value">{{ $employee?->name ?: $dash }}</td>
                <td class="label">Employee ID</td><td class="value">{{ $detail?->employee_id ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">{{ $idLabel }}</td><td class="value">{{ $idValue ?: $dash }}</td>
                <td class="label">Nationality</td><td class="value">{{ $sep['nationality'] ?? ($employee?->country?->nicename ?: $dash) }}</td>
            </tr>
            <tr>
                <td class="label">Designation</td><td class="value">{{ $detail?->designation?->name ?: $dash }}</td>
                <td class="label">Department</td><td class="value">{{ $detail?->department?->team_name ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Date of Joining</td><td class="value">{{ $doj }}</td>
                <td class="label">Total Service (Y/M/D)</td><td class="value">{{ $sep['total_service'] ?? $dash }}</td>
            </tr>
            <tr>
                <td class="label">Contract Type</td><td class="value">{{ $sep['contract_type'] ?? $dash }}</td>
                <td class="label">Last Working Day</td><td class="value">{{ $lwd }}</td>
            </tr>
            <tr>
                <td class="label">Separation Type</td><td class="value">{{ ucfirst((string) $case->exit_type) }}</td>
                <td class="label">Notice</td><td class="value">{{ $noticeLabel }}</td>
            </tr>
            <tr>
                <td class="label">Reason</td><td class="value" colspan="3">{{ $case->reason ?: $dash }}</td>
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

        <div class="section-title">3. Handover of Duties, Documents &amp; Company Property</div>
        <table class="grid">
            <thead><tr><th style="width:44%">Item</th><th>Done / N/A</th><th>Handed over to</th><th>Remarks</th></tr></thead>
            <tbody>
            @foreach(\App\Support\Clearance::HR_HANDOVER as $i => $label)
                @php $row = $handover[$i] ?? []; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ $tick($row['result'] ?? null) }}</td>
                    <td>{{ $row['handed_to'] ?? $dash }}</td>
                    <td>{{ $row['remarks'] ?? $dash }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="section-title">4. Statutory &amp; Government Actions (KSA)</div>
        <table class="grid">
            <thead><tr><th style="width:44%">Action</th><th>Done / N/A</th><th>Reference no.</th><th>Date</th></tr></thead>
            <tbody>
            @foreach(\App\Support\Clearance::HR_STATUTORY as $i => $label)
                @php $row = $statutory[$i] ?? []; @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>{{ $tick($row['result'] ?? null) }}</td>
                    <td>{{ $row['reference'] ?? $dash }}</td>
                    <td>{{ $row['date'] ?? $dash }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <div class="section-title">5. Leave, Entitlements &amp; Documents Due to Employee</div>
        <table class="kv">
            <tr>
                <td class="label">Annual leave entitled</td><td class="value">{{ $entShow('leave_entitled') }}</td>
                <td class="label">Leave availed</td><td class="value">{{ $entShow('leave_availed') }}</td>
            </tr>
            <tr>
                <td class="label">Leave balance</td><td class="value">{{ $entShow('leave_balance') }}</td>
                <td class="label">Leave encashment due</td><td class="value">{{ $entShow('leave_encashment_due') }}</td>
            </tr>
            <tr>
                <td class="label">Unauthorised absence (days)</td><td class="value">{{ $entShow('unauthorised_absence_days') }}</td>
                <td class="label">EOSB eligibility</td><td class="value">{{ $entShow('eosb_eligibility') }}</td>
            </tr>
            <tr>
                <td class="label">Repatriation ticket due</td><td class="value">{{ $entShow('repatriation_ticket_due') }}</td>
                <td class="label">Service certificate</td><td class="value">{{ $entShow('service_certificate') }}</td>
            </tr>
            <tr>
                <td class="label">Experience letter</td><td class="value">{{ $entShow('experience_letter') }}</td>
                <td class="label">Net settlement (SAR)</td>
                <td class="value">{{ $settlement ? number_format((float) $settlement->net_amount, 2) . ($settlementFinal ? ' (finalised)' : ' (draft)') : $dash }}</td>
            </tr>
        </table>

        <div class="section-title">6. Employee Declaration</div>
        <div class="declaration">
            The employee confirms that all Company property and assets in their custody have been returned, that work and
            records have been handed over, and that they retain no Company data or confidential information. The employee
            authorises the Company to recover any verified outstanding amount from the final settlement or end-of-service
            benefits, in accordance with the applicable labour regulations.
            {{ !empty($hd['declaration_acknowledged']) ? ' — Acknowledged.' : ' — NOT yet acknowledged.' }}
            <br><br>
            Forwarding address: {{ $hd['forwarding_address'] ?? '______________________________' }}
            &nbsp;&nbsp; Contact: {{ $hd['contact_number'] ?? '______________' }}
            &nbsp;&nbsp; Personal email: {{ $hd['personal_email'] ?? ($detail?->personal_email ?? '______________') }}
        </div>

        <div class="section-title">7. HR Clearance Decision</div>
        <div class="decision">
            <span class="big">{{ strtoupper($decisionLabels[$decision] ?? ($decision ?: 'PENDING')) }}</span>
            @if(!empty($hd['hr_remarks']))<br>HR remarks: {{ $hd['hr_remarks'] }}@endif
            <br>Issued by: {{ $case->hrClearedBy?->name ?? ($hd['hr_officer'] ?? $dash) }}
            @if(!empty($hd['hr_manager'])) &nbsp;·&nbsp; HR Manager: {{ $hd['hr_manager'] }}@endif
            &nbsp;·&nbsp; {{ optional($case->hr_cleared_at)->format('d M Y') ?: $dash }}
        </div>

        <table class="sign">
            <tr>
                <td><div class="sign-role">Employee</div><div class="sign-line">{{ $employee?->name ?: 'Name: __________' }}<div class="sign-meta">Signature / Date</div></div></td>
                <td><div class="sign-role">Line Manager</div><div class="sign-line">Name: __________<div class="sign-meta">Signature / Date</div></div></td>
                <td><div class="sign-role">HR Officer</div><div class="sign-line">{{ $hd['hr_officer'] ?? 'Name: __________' }}<div class="sign-meta">Signature / Date</div></div></td>
                <td><div class="sign-role">HR Manager</div><div class="sign-line">{{ $hd['hr_manager'] ?? 'Name: __________' }}<div class="sign-meta">Signature / Date</div></div></td>
            </tr>
        </table>

        <div class="footer-note">
            {{ $companyName }} &nbsp;|&nbsp; CR 7038950171 &nbsp;|&nbsp; HR Clearance &amp; Offboarding Form
            — {{ $case->reference ?: $case->id }} &nbsp;|&nbsp; Confidential Document.
        </div>
    </div>

</body>
</html>
