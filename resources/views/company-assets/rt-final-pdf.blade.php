@php
    /** @var \App\Models\AssetReturnForm $form */
    $company     = $company ?? (function_exists('company') ? company() : null);
    if (!is_object($company)) { $company = null; }
    $companyName = optional($company)->company_name ?: 'Speed Logi Company';

    $lhFile = public_path('img/speedlogi-letterhead.png');
    $dash   = '—';

    $asset    = $form->asset;
    $serial   = $form->serial;
    $employee = $form->employee;
    $detail   = $employee?->employeeDetail;

    $employeeType = $detail?->employee_type ?: 'expat';
    $isSaudi      = $employeeType === 'saudi';
    $idLabel      = $isSaudi ? 'National ID' : 'Iqama No.';
    $idValue      = $isSaudi ? $detail?->national_id : $detail?->iqama_no;

    $empDesig  = $detail?->designation?->name;
    $empDept   = $detail?->department?->team_name;
    $empBranch = $employee?->branch?->name;
    $empMobile = $employee?->mobile_with_phonecode ?: $employee?->mobile;

    $handoverRef = 'HO-' . str_pad((string) ($form->company_asset_id ?? 0), 4, '0', STR_PAD_LEFT)
        . '-' . str_pad((string) ($form->asset_assignment_id ?? 0), 4, '0', STR_PAD_LEFT);

    $certifiedAt = $form->certified_at ? \Carbon\Carbon::parse($form->certified_at)->format('d M Y H:i') : $dash;

    $recommended = $form->recommended_recovery_amount !== null ? (float) $form->recommended_recovery_amount : null;
    $approved    = $form->approved_recovery_amount !== null ? (float) $form->approved_recovery_amount : null;
    $allocated   = (float) $form->allocations->sum('amount');

    $outcomeLabels = [
        'returned' => 'Returned to Company custody',
        'lost'     => 'Written off — Lost / stolen',
        'damaged'  => 'Written off — Damaged beyond economic repair',
        'retired'  => 'Written off — Retired / end of life',
    ];
    $recoveryStatusLabels = [
        'not_required'       => 'No recovery approved',
        'partially_approved' => 'Partially approved',
        'approved'           => 'Approved for recovery',
        'waived'             => 'Waived',
    ];

    $lines = $form->relationLoaded('lines') ? $form->lines : $form->lines()->get();
    $bySection = $lines->groupBy('section');
    $accessoryItems = \App\Services\AssetReturnService::CHECKLIST['accessories'];
    $inspectionPoints = \App\Services\AssetReturnService::CHECKLIST['technical'];
    $dataActions = \App\Services\AssetReturnService::CHECKLIST['data'];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Asset Return, Inspection &amp; Clearance Form - {{ $form->reference }}</title>
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
        table.kv td.label { width: 20%; color: #6b5b86; font-size: 8.5px; text-transform: uppercase; letter-spacing: .3px; }
        table.kv td.value { width: 30%; font-weight: bold; color: #2d3748; }

        .notesblock { border: 1px solid #d9d2e6; padding: 5px 9px; margin-top: 3px; background: transparent; min-height: 26px; white-space: pre-wrap; }
        .refitems { font-size: 8.5px; color: #8a7ca6; margin-top: 3px; }

        table.grid { width: 100%; border-collapse: collapse; margin-top: 3px; background: transparent; }
        table.grid th, table.grid td { border: 1px solid #d9d2e6; padding: 3px 7px; background: transparent; text-align: left; vertical-align: top; }
        table.grid th { background: #f4f1f7; color: #6b5b86; font-size: 8px; text-transform: uppercase; }

        .declaration { margin-top: 8px; padding: 7px 11px; background: transparent; border: 1px solid #d9d2e6; border-left: 3px solid #5b2a86; font-size: 9.5px; text-align: justify; }

        table.sign { width: 100%; border-collapse: collapse; margin-top: 18px; }
        table.sign td { width: 33.33%; padding: 0 10px; vertical-align: top; }
        .sign-line { border-top: 1px solid #2d3748; padding-top: 4px; font-size: 9px; color: #4a5568; }
        .sign-role { font-weight: bold; color: #5b2a86; text-transform: uppercase; font-size: 9px; margin-bottom: 20px; }
        .sign-meta { font-size: 8px; color: #718096; margin-top: 3px; }
        .footer-note { margin-top: 14px; font-size: 7.5px; color: #8a7ca6; text-align: center; }
        .status-pill { display: inline-block; padding: 1px 7px; border: 1px solid #5b2a86; color: #5b2a86; font-size: 8.5px; font-weight: bold; text-transform: uppercase; }
    </style>
</head>
<body>

    @if (is_file($lhFile))
        <div class="lh-bg"><img src="{{ $lhFile }}" alt=""></div>
    @endif

    <div class="content">
        <h2 class="doc-title">Company Asset Return, Inspection &amp; Clearance Form</h2>
        <div class="doc-sub">
            Reference {{ $form->reference }} &nbsp;|&nbsp; Linked handover {{ $handoverRef }}
            &nbsp;|&nbsp; Certified {{ $certifiedAt }}
        </div>

        <div class="section-title">1. Asset Details</div>
        <table class="kv">
            <tr>
                <td class="label">Asset Name</td><td class="value">{{ $asset?->name ?: $dash }}</td>
                <td class="label">Serial Number</td><td class="value">{{ $serial?->serial_no ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Type / Category</td><td class="value">{{ $asset?->type ?: $dash }}</td>
                <td class="label">Brand</td><td class="value">{{ $asset?->brand ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">SKU Number</td><td class="value">{{ $asset?->sku_no ?: $dash }}</td>
                <td class="label">Catalog</td><td class="value">{{ $asset?->catalog ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Asset Department</td><td class="value">{{ optional($asset?->department)->name ?: $dash }}</td>
                <td class="label">Asset Branch</td><td class="value">{{ optional($asset?->branch)->name ?: $dash }}</td>
            </tr>
        </table>

        <div class="section-title">2. Custodian &amp; Return Details</div>
        <table class="kv">
            <tr>
                <td class="label">Employee Name</td><td class="value">{{ $employee?->name ?: $dash }}</td>
                <td class="label">Employee ID</td><td class="value">{{ $detail?->employee_id ?: $form->employee_id }}</td>
            </tr>
            <tr>
                <td class="label">{{ $idLabel }}</td><td class="value">{{ $idValue ?: $dash }}</td>
                <td class="label">Mobile</td><td class="value">{{ $empMobile ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Designation</td><td class="value">{{ $empDesig ?: $dash }}</td>
                <td class="label">Department</td><td class="value">{{ $empDept ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Branch</td><td class="value">{{ $empBranch ?: $dash }}</td>
                <td class="label">Return Outcome</td><td class="value">{{ $outcomeLabels[$form->outcome] ?? ucfirst($form->outcome) }}</td>
            </tr>
            <tr>
                <td class="label">Certified By (IT)</td><td class="value">{{ $form->certifiedBy?->name ?: $dash }}</td>
                <td class="label">Certified At</td><td class="value">{{ $certifiedAt }}</td>
            </tr>
        </table>

        @php
            $renderSection = function ($key, $freeText, $fallbackLabels) use ($bySection) {
                $rows = $bySection->get($key);
                if ($rows && $rows->count()) {
                    return ['rows' => $rows, 'text' => null];
                }
                return ['rows' => null, 'text' => $freeText, 'labels' => $fallbackLabels];
            };
        @endphp

        <div class="section-title">3. Accessories &amp; Items Checklist</div>
        @php $sec = $renderSection('accessories', $form->accessories_checklist, $accessoryItems); @endphp
        @if($sec['rows'])
            <table class="grid"><thead><tr><th style="width:48%">Item</th><th style="width:16%">Result</th><th>Remarks</th></tr></thead><tbody>
            @foreach($sec['rows'] as $r)<tr><td>{{ $r->label }}</td><td>{{ ucfirst((string) $r->result) ?: '—' }}</td><td>{{ $r->remarks ?: '' }}</td></tr>@endforeach
            </tbody></table>
        @else
            <div class="notesblock">{{ $sec['text'] ?: 'No accessory notes recorded.' }}</div>
            <div class="refitems">Standard items: {{ implode(' · ', $sec['labels']) }}</div>
        @endif

        <div class="section-title">4. Technical &amp; Physical Evaluation (IT / Inspecting Officer)</div>
        @php $sec = $renderSection('technical', $form->technical_inspection, $inspectionPoints); @endphp
        @if($sec['rows'])
            <table class="grid"><thead><tr><th style="width:48%">Inspection point</th><th style="width:16%">Rating</th><th>Observation</th></tr></thead><tbody>
            @foreach($sec['rows'] as $r)<tr><td>{{ $r->label }}</td><td>{{ ucfirst((string) $r->result) ?: '—' }}</td><td>{{ $r->remarks ?: '' }}</td></tr>@endforeach
            </tbody></table>
        @else
            <div class="notesblock">{{ $sec['text'] ?: 'No technical inspection notes recorded.' }}</div>
            <div class="refitems">Inspection points: {{ implode(' · ', $sec['labels']) }}</div>
        @endif

        <div class="section-title">5. Data &amp; Security Clearance</div>
        @php $sec = $renderSection('data', $form->data_clearance_notes, $dataActions); @endphp
        @if($sec['rows'])
            <table class="grid"><thead><tr><th style="width:48%">Action</th><th style="width:16%">Result</th><th>Remarks</th></tr></thead><tbody>
            @foreach($sec['rows'] as $r)<tr><td>{{ $r->label }}</td><td>{{ ucfirst((string) $r->result) ?: '—' }}</td><td>{{ $r->remarks ?: '' }}</td></tr>@endforeach
            </tbody></table>
        @else
            <div class="notesblock">{{ $sec['text'] ?: 'No data / security clearance notes recorded.' }}</div>
            <div class="refitems">Required actions: {{ implode(' · ', $sec['labels']) }}</div>
        @endif

        <div class="section-title">6. Evaluation Outcome, Disposition &amp; Recovery</div>
        <table class="kv">
            <tr>
                <td class="label">Disposition</td>
                <td class="value" colspan="3">{{ $form->disposition_notes ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Recommended Recovery (SAR)</td>
                <td class="value">{{ $recommended !== null ? number_format($recommended, 2) : 'None' }}</td>
                <td class="label">Recovery Reason</td>
                <td class="value">{{ $form->recovery_reason ?: $dash }}</td>
            </tr>
            <tr>
                <td class="label">Approved Recovery (SAR)</td>
                <td class="value">{{ $approved !== null ? number_format($approved, 2) : $dash }}</td>
                <td class="label">Allocated to date (SAR)</td>
                <td class="value">{{ number_format($allocated, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Recovery Status</td>
                <td class="value">
                    <span class="status-pill">{{ $recoveryStatusLabels[$form->recovery_status] ?? $form->recovery_status }}</span>
                </td>
                <td class="label">Approved By (Finance)</td>
                <td class="value">{{ $form->recoveryApprovedBy?->name ?: $dash }}</td>
            </tr>
        </table>

        <div class="section-title">7. Declarations</div>
        <div class="declaration">
            <strong>Employee:</strong> I confirm that I have returned the asset(s) and accessories listed above, and that the
            condition recorded in Sections&nbsp;3–5 was assessed in my presence or has been communicated to me. Where loss or
            damage has been assessed as arising from my negligence, misuse or failure to follow Company policy, I acknowledge
            the Company's right under <strong>Clause&nbsp;6 of the Company Asset Handover &amp; Custody Form</strong> to recover
            the repair or replacement value from my salary, end-of-service benefits or any other amounts due to me, in
            accordance with the applicable labour regulations.<br><br>
            <strong>Company:</strong> Receipt of the asset(s) is acknowledged subject to the inspection findings recorded above.
            This form does not constitute final clearance until signed by HR. Final settlement / end-of-service clearance may be
            withheld until all assets are returned and this form is completed and signed by all parties.
        </div>

        <table class="sign">
            <tr>
                <td>
                    <div class="sign-role">Returned by (Employee)</div>
                    <div class="sign-line">{{ $employee?->name ?: $dash }}
                        <div class="sign-meta">Signature / Date: ____________________</div>
                    </div>
                </td>
                <td>
                    <div class="sign-role">Inspected &amp; Certified by (IT)</div>
                    <div class="sign-line">{{ $form->certifiedBy?->name ?: 'Name: ____________________' }}
                        <div class="sign-meta">Signature / Date: ____________________</div>
                    </div>
                </td>
                <td>
                    <div class="sign-role">Verified &amp; Cleared by (HR)</div>
                    <div class="sign-line">Name: ____________________
                        <div class="sign-meta">Signature / Date: ____________________</div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer-note">
            {{ $companyName }} &nbsp;|&nbsp; CR 7038950171 &nbsp;|&nbsp; Asset Return, Inspection &amp; Clearance Form
            — {{ $form->reference }} &nbsp;|&nbsp; Immutable record. SHA-256: {{ $form->document_hash ?: 'pending' }}
        </div>
    </div>

</body>
</html>
