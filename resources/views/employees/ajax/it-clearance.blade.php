@php
    /** @var \App\Models\EmployeeTermination $termination */
    $issued        = $termination && $termination->it_clearance_status === 'issued';
    $pendingAssets = ($assignedAssets ?? collect());
    $saved         = $clearanceData ?? [];
    $savedConfirm  = $saved['confirm'] ?? [];

    $sectionTitles = [
        'accessories' => '3. Accessories & items checklist',
        'technical'   => '4. Technical & physical evaluation',
        'data'        => '5. Data & security clearance',
    ];
    $grades = ['A' => 'A - As new, fully serviceable', 'B' => 'B - Good, fair wear & tear', 'C' => 'C - Damaged, repair required', 'D' => 'D - Beyond economic repair / not returned'];
    $damageOptions = ['No damage', 'Fair wear and tear - no recovery', 'Damage / loss - negligence or misuse'];
    $dispositionOptions = ['Return to IT stock', 'Reassign', 'Send for repair', 'Quarantine / hold', 'Scrap & dispose', 'Insurance claim'];
@endphp

<div id="it-clearance-section">
    <div class="row">
        <div class="col-sm-12">
            <div class="card bg-white border-0 b-shadow-4">
                <div class="card-header form-heading-background border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-8 col-8"><h3 class="heading-h1 mb-0">Asset Return, Inspection &amp; IT Clearance</h3></div>
                        <div class="col-md-4 col-4 text-right">
                            @if ($issued)
                                <span class="badge badge-success p-2">Issued &mdash; {{ \App\Support\Clearance::IT_DECISIONS[$termination->it_clearance_decision] ?? ucfirst((string) $termination->it_clearance_decision) }}</span>
                            @else
                                <span class="badge badge-warning p-2">Pending</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="text-right d-flex justify-content-end mb-3">
                        <a href="{{ route('employees.index') }}?tab=pending-offboard" class="btn btn-sm btn-secondary">Back</a>
                        @unless ($issued)
                            @if ($pendingAssets->isNotEmpty())
                                <a href="javascript:;" class="btn btn-sm btn-warning send-it-reminder ml-2">
                                    <i class="fa fa-bell mr-1"></i> Send Reminder
                                </a>
                            @endif
                        @endunless
                        @if ($issued)
                            <a href="{{ route('employees.it-clearance.letter', $employee->id) }}" class="btn btn-sm btn-primary ml-2" target="_blank">
                                <i class="fa fa-file-pdf-o mr-1"></i> Download IT Clearance Letter
                            </a>
                        @endif
                    </div>

                    <h5 class="heading-h4 mb-2">1. Employee</h5>
                    <x-cards.data-row :label="__('modules.employees.employeeId')" :value="$employee->employeeDetail->employee_id ?? '--'" />
                    <x-cards.data-row :label="__('modules.employees.fullName')" :value="$employee->name" />
                    <x-cards.data-row :label="__('app.designation')" :value="$employee->employeeDetail->designation->name ?? '--'" />
                    <x-cards.data-row :label="__('app.department')" :value="$employee->employeeDetail->department->team_name ?? '--'" />

                    {{-- ── Assets still to return ── --}}
                    <h5 class="heading-h4 mt-4 mb-2">2. Assets to return &amp; inspect ({{ $pendingAssets->count() }})</h5>
                    @if ($pendingAssets->isEmpty())
                        <div class="alert alert-success py-2"><i class="fa fa-check mr-1"></i> No assets pending return.</div>
                    @else
                        <div class="accordion" id="asset-return-accordion">
                            @foreach ($pendingAssets as $idx => $assignment)
                                <div class="card border">
                                    <div class="card-header p-2" id="ah-{{ $assignment->id }}">
                                        <button class="btn btn-link text-left w-100 collapsed" type="button"
                                                data-toggle="collapse" data-target="#ac-{{ $assignment->id }}">
                                            <i class="fa fa-laptop mr-1"></i>
                                            <strong>{{ $assignment->asset->name ?? 'Asset' }}</strong>
                                            &mdash; serial {{ $assignment->serial->serial_no ?? ($assignment->serial_no ?? 'n/a') }}
                                            <span class="badge badge-warning float-right">Return pending</span>
                                        </button>
                                    </div>
                                    <div id="ac-{{ $assignment->id }}" class="collapse" data-parent="#asset-return-accordion">
                                        <div class="card-body">
                                            <form class="asset-return-form" data-assignment="{{ $assignment->id }}" enctype="multipart/form-data">
                                                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                                                @foreach (['accessories', 'technical', 'data'] as $section)
                                                    <h6 class="font-weight-bold mt-2">{{ $sectionTitles[$section] }}</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-bordered mb-2">
                                                            <thead class="thead-light">
                                                                <tr><th style="width:44%">Item</th><th style="width:22%">Result</th><th>Remarks</th></tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($checklist[$section] as $i => $label)
                                                                    <tr>
                                                                        <td class="align-middle f-13">{{ $i + 1 }}. {{ $label }}</td>
                                                                        <td>
                                                                            <select name="{{ $section }}[{{ $i }}][result]" class="form-control form-control-sm">
                                                                                <option value="">--</option>
                                                                                @foreach ($resultOptions[$section] as $opt)
                                                                                    <option value="{{ $opt }}">{{ ucfirst($opt) }}</option>
                                                                                @endforeach
                                                                            </select>
                                                                        </td>
                                                                        <td><input type="text" class="form-control form-control-sm" name="{{ $section }}[{{ $i }}][remarks]"></td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endforeach

                                                <h6 class="font-weight-bold mt-3">6. Evaluation outcome</h6>
                                                <div class="form-row">
                                                    <div class="col-md-4 mb-2">
                                                        <label class="f-13 mb-1">Overall condition grade <span class="text-danger">*</span></label>
                                                        <select name="overall_grade" class="form-control form-control-sm" required>
                                                            <option value="">--</option>
                                                            @foreach ($grades as $g => $gl)<option value="{{ $g }}">{{ $gl }}</option>@endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <label class="f-13 mb-1">Damage assessment <span class="text-danger">*</span></label>
                                                        <select name="damage_assessment" class="form-control form-control-sm" required>
                                                            <option value="">--</option>
                                                            @foreach ($damageOptions as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <label class="f-13 mb-1">Disposition</label>
                                                        <select name="disposition" class="form-control form-control-sm">
                                                            <option value="">--</option>
                                                            @foreach ($dispositionOptions as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="f-13 mb-1">Est. repair cost (SAR)</label>
                                                        <input type="number" step="0.01" min="0" name="estimated_repair_cost" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="f-13 mb-1">Replacement value (SAR)</label>
                                                        <input type="number" step="0.01" min="0" name="replacement_value" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="f-13 mb-1">Recommended recovery (SAR)</label>
                                                        <input type="number" step="0.01" min="0" name="recommended_recovery_amount" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="f-13 mb-1">Deduct from settlement?</label>
                                                        <select name="deduct_from_settlement" class="form-control form-control-sm">
                                                            <option value="no">No</option>
                                                            <option value="yes">Yes - send to Finance</option>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-8 mb-2">
                                                        <label class="f-13 mb-1">Recovery reason</label>
                                                        <input type="text" name="recovery_reason" class="form-control form-control-sm">
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <label class="f-13 mb-1">Signed return form (optional)</label>
                                                        <input type="file" name="return_document" class="form-control-file f-13" accept=".pdf,.png,.jpg,.jpeg">
                                                    </div>
                                                    <div class="col-md-12 mb-2">
                                                        <label class="f-13 mb-1">Inspector notes</label>
                                                        <textarea name="inspector_notes" rows="2" class="form-control form-control-sm"></textarea>
                                                    </div>
                                                </div>

                                                <button type="button" class="btn btn-primary btn-sm record-asset-return">
                                                    <i class="fa fa-save mr-1"></i> Record return &amp; inspection
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- ── Returned already ── --}}
                    <h5 class="heading-h4 mt-4 mb-2">Returned &amp; inspected</h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="thead-light"><tr><th>RT ref</th><th>Asset</th><th>Serial</th><th>Outcome / grade</th><th>Recovery (SAR)</th><th></th></tr></thead>
                            <tbody>
                                @forelse (($returnedForms ?? collect()) as $rt)
                                    <tr>
                                        <td>{{ $rt->reference }}</td>
                                        <td>{{ $rt->asset->name ?? '--' }}</td>
                                        <td>{{ $rt->serial->serial_no ?? '--' }}</td>
                                        <td class="f-12">{{ \Illuminate\Support\Str::limit((string) $rt->disposition_notes, 90) ?: ucfirst((string) $rt->outcome) }}</td>
                                        <td>{{ $rt->recommended_recovery_amount !== null ? number_format((float) $rt->recommended_recovery_amount, 2) : '--' }}</td>
                                        <td><a href="{{ route('company-assets.return-form.pdf', $rt->id) }}" class="btn btn-xs btn-outline-primary" target="_blank"><i class="fa fa-file-pdf-o"></i> RT form</a></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-muted">No assets returned through this screen yet.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- ── Clearance-level block ── --}}
                    <form id="it-clearance-form" autocomplete="off">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <h5 class="heading-h4 mt-4 mb-2">7. IT data &amp; security clearance</h5>
                        @foreach ($itDataSecurity as $key => $label)
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="checkbox" name="confirm[{{ $key }}]" id="cf-{{ $key }}" value="1"
                                       @checked(!empty($savedConfirm[$key])) @disabled($issued)>
                                <label class="form-check-label f-13" for="cf-{{ $key }}">{{ $label }}</label>
                            </div>
                        @endforeach
                        <div class="form-row mt-2">
                            <div class="col-md-4 mb-2">
                                <label class="f-13 mb-1">IT officer name</label>
                                <input type="text" name="inspector_name" class="form-control form-control-sm"
                                       value="{{ data_get($saved, 'inspector_name') }}" @disabled($issued)>
                            </div>
                            <div class="col-md-8 mb-2">
                                <label class="f-13 mb-1">IT remarks</label>
                                <input type="text" name="it_remarks" class="form-control form-control-sm"
                                       value="{{ data_get($saved, 'it_remarks') }}" @disabled($issued)>
                            </div>
                        </div>
                        <div class="mb-2">
                            @foreach ($itDecisions as $v => $t)
                                <label class="d-block f-13 mb-1">
                                    <input type="radio" name="it_clearance_decision" value="{{ $v }}" @checked($termination->it_clearance_decision === $v) @disabled($issued)> {{ $t }}
                                </label>
                            @endforeach
                        </div>

                        @unless ($issued)
                            <button type="button" class="btn btn-primary mt-2" id="issue-it-clearance" @disabled($pendingAssets->isNotEmpty())>
                                <i class="fa fa-check mr-1"></i> Issue IT Clearance
                            </button>
                            @if ($pendingAssets->isNotEmpty())
                                <span class="text-muted f-13 ml-2">Return &amp; inspect every asset above to enable this.</span>
                            @endif
                        @else
                            <div class="alert alert-success mt-2 mb-0">
                                <i class="fa fa-lock mr-1"></i> Issued on
                                {{ optional($termination->it_clearance_issued_at)->format(company()->date_format . ' H:i') }}
                                by {{ $termination->itClearanceIssuedBy->name ?? '--' }}.
                            </div>
                        @endunless
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('body').off('click', '.send-it-reminder').on('click', '.send-it-reminder', function () {
        $.easyAjax({
            type: 'POST',
            url: "{{ route('employees.it-clearance.reminder', $employee->id) }}",
            blockUI: true,
            data: { '_token': "{{ csrf_token() }}" }
        });
    });

    $('body').off('click', '.record-asset-return').on('click', '.record-asset-return', function () {
        var $form = $(this).closest('.asset-return-form');
        var assignmentId = $form.data('assignment');
        if (!$form[0].checkValidity()) { $form[0].reportValidity(); return; }
        $.easyAjax({
            type: 'POST',
            url: "{{ route('employees.it-clearance.return-asset', [$employee->id, 'ASSIGNMENT_ID']) }}".replace('ASSIGNMENT_ID', assignmentId),
            blockUI: true,
            file: true,
            data: new FormData($form[0]),
            success: function (res) {
                if (res.status === 'success') { window.location.href = res.redirectUrl || window.location.href; }
            }
        });
    });

    $('body').off('click', '#issue-it-clearance').on('click', '#issue-it-clearance', function () {
        Swal.fire({
            title: 'Issue IT clearance?',
            text: 'The data & security confirmation and decision below will be locked into the record.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, issue',
            customClass: { confirmButton: 'btn btn-primary mr-2', cancelButton: 'btn btn-secondary' },
            buttonsStyling: false
        }).then(function (r) {
            if (!r.isConfirmed) return;
            $.easyAjax({
                type: 'POST',
                url: "{{ route('employees.it-clearance.issue', $employee->id) }}",
                blockUI: true,
                disableButton: true,
                buttonSelector: '#issue-it-clearance',
                data: $('#it-clearance-form').serialize(),
                success: function (res) {
                    if (res.status === 'success') { window.location.href = res.redirectUrl || window.location.href; }
                }
            });
        });
    });
</script>
