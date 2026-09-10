@extends('layouts.app')

@php
    $stepPct = 0;
    if ($case && $case->tasks->count()) {
        $stepPct = (int) round($case->tasks->whereIn('status', ['completed', 'waived'])->count() / $case->tasks->count() * 100);
    }
@endphp

@section('content')
<style>
    .rsg-wrap { max-width: 620px; margin: 0 auto; }
    .rsg-card { background:#fff; border:1px solid #e5e7eb; border-radius:14px; box-shadow:0 1px 3px rgba(0,0,0,.04); }
    .rsg-card .rsg-head { padding:18px 22px; border-bottom:1px solid #f1f2f4; }
    .rsg-card .rsg-body { padding:22px; }
    .rsg-title { font-size:16px; font-weight:700; color:#1f2937; margin:0; }
    .rsg-title .fa { color:#1f7a4d; }
    .rsg-sub { color:#6b7280; font-size:12.5px; margin:3px 0 0; }
    .rsg-lbl { display:block; font-weight:600; font-size:11.5px; text-transform:uppercase; letter-spacing:.03em; color:#374151; margin:16px 0 6px; }
    .rsg-lbl:first-child { margin-top:0; }
    .rsg-lbl .opt { font-weight:400; text-transform:none; letter-spacing:0; color:#9ca3af; }
    .rsg-body textarea, .rsg-body input[type=date] { width:100%; border:1px solid #d1d5db; border-radius:9px; padding:9px 12px; font-size:13px; background:#fff; }
    .rsg-body textarea:focus, .rsg-body input[type=date]:focus { border-color:#1f7a4d; box-shadow:0 0 0 3px rgba(31,122,77,.12); outline:none; }
    .rsg-body textarea { min-height:58px; resize:vertical; }
    .rsg-opts { display:flex; gap:8px; }
    .rsg-opt { flex:1; border:1px solid #d1d5db; border-radius:9px; padding:11px 13px; cursor:pointer; transition:border-color .15s, background .15s; }
    .rsg-opt:hover { border-color:#1f7a4d; }
    .rsg-opt input { display:none; }
    .rsg-opt .t { font-weight:600; font-size:12.5px; color:#374151; }
    .rsg-opt .d { font-size:11px; color:#9ca3af; margin-top:2px; }
    .rsg-opt.on { border-color:#1f7a4d; background:#ecfdf3; }
    .rsg-opt.on .t { color:#1f7a4d; }
    .rsg-months { display:flex; gap:6px; margin-top:2px; }
    .rsg-months label { flex:1; text-align:center; border:1px solid #d1d5db; border-radius:9px; padding:8px 0; cursor:pointer; font-weight:600; font-size:12.5px; color:#374151; transition:.15s; }
    .rsg-months input { display:none; }
    .rsg-months label.on { border-color:#1f7a4d; background:#1f7a4d; color:#fff; }
    .rsg-lwd { margin-top:16px; border:1px dashed #1f7a4d; background:#f0fdf4; border-radius:10px; padding:12px 14px; }
    .rsg-lwd .k { font-size:11px; text-transform:uppercase; letter-spacing:.03em; color:#3f6b4f; }
    .rsg-lwd .v { font-size:16px; font-weight:700; color:#1f7a4d; }
    .rsg-btn { margin-top:20px; background:#1f7a4d; border:0; color:#fff; font-weight:600; border-radius:9px; padding:10px 20px; font-size:13px; }
    .rsg-btn:hover { background:#1a6841; color:#fff; }
    .rsg-note { color:#9ca3af; font-size:11.5px; margin-top:10px; }
    .rsg-timeline li { padding:6px 0; font-size:13px; border-bottom:1px solid #f3f4f6; }
    .rsg-timeline li:last-child { border-bottom:0; }
    .rsg-set { margin-top:14px; border:1px solid #e5e7eb; border-radius:10px; padding:12px 14px; font-size:13px; }
</style>

<div class="content-wrapper">
    <div class="rsg-wrap">

        @if ($case)
            {{-- ── Existing request ─────────────────────────── --}}
            <div class="rsg-card">
                <div class="rsg-head d-flex justify-content-between align-items-start">
                    <div>
                        <h3 class="rsg-title"><i class="fa fa-sign-out mr-2"></i>My resignation</h3>
                        <p class="rsg-sub">
                            Requested {{ optional($case->created_at)->format('d M Y') }}
                            &middot; last working day <strong>{{ optional($case->last_working_date)->format('d M Y') ?: '—' }}</strong>
                            &middot; {{ $case->notice_type === 'immediate' ? 'immediate effect' : ($case->notice_months . '-month notice') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <span class="badge badge-pill p-2 badge-{{ $case->status === 'completed' ? 'success' : 'warning' }}">{{ ucfirst(str_replace('_', ' ', $case->status)) }}</span><br>
                        <span class="badge badge-pill p-2 badge-secondary mt-1">{{ ucfirst(str_replace('_', ' ', $case->approval_status)) }}</span>
                    </div>
                </div>
                <div class="rsg-body">
                    @if ($case->approval_status === 'awaiting_approval')
                        <div class="alert alert-info mb-0"><i class="fa fa-clock-o mr-1"></i> Your request is with HR / your manager for approval. You will be notified of the decision.</div>
                    @elseif ($case->approval_status === 'rejected')
                        <div class="alert alert-danger mb-0"><i class="fa fa-times-circle mr-1"></i> Request not approved.<br><strong>Reason:</strong> {{ $case->rejected_reason }}</div>
                    @else
                        <span class="rsg-lbl">Clearance progress</span>
                        <div class="progress mb-2" style="height:6px;">
                            <div class="progress-bar bg-success" style="width: {{ $stepPct }}%"></div>
                        </div>
                        <ul class="list-unstyled rsg-timeline mb-0">
                            @foreach ($case->tasks as $task)
                                <li>
                                    <i class="fa fa-{{ in_array($task->status, ['completed','waived']) ? 'check-circle text-success' : 'circle-o text-muted' }} mr-2"></i>
                                    {{ $task->title }}
                                    <span class="badge badge-light border ml-1">{{ $task->status }}</span>
                                </li>
                            @endforeach
                        </ul>
                        <div class="rsg-set">
                            <strong>Final settlement:</strong>
                            @if (!$settlement)
                                <span class="text-muted">not started</span>
                            @elseif ($settlement->status === \App\Models\HrSettlementForm::STATUS_FINAL)
                                <span class="text-success">finalised &mdash; SAR {{ number_format((float) $settlement->net_amount, 2) }} net</span>
                            @else
                                <span class="text-warning">in preparation</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        @else
            {{-- ── New request ──────────────────────────────── --}}
            <div class="rsg-card">
                <div class="rsg-head">
                    <h3 class="rsg-title"><i class="fa fa-sign-out mr-2"></i>Submit your resignation</h3>
                    <p class="rsg-sub">This goes to HR and your manager for approval. Your last working day is worked out from the date and notice period you choose.</p>
                </div>
                <div class="rsg-body">
                    <form id="rsg-form" method="POST" action="{{ route('employees.resignation') }}">
                        @csrf

                        <label class="rsg-lbl">Reason for resignation</label>
                        <textarea name="reason" maxlength="1000" required placeholder="Briefly, why are you resigning?">{{ old('reason') }}</textarea>

                        <label class="rsg-lbl">Resignation date</label>
                        <input type="date" name="resignation_date" id="rsg-date"
                               value="{{ old('resignation_date', now()->toDateString()) }}"
                               min="{{ now()->toDateString() }}" required>

                        <label class="rsg-lbl">Effect</label>
                        <div class="rsg-opts">
                            <label class="rsg-opt" data-v="notice">
                                <input type="radio" name="notice_type" value="notice" {{ old('notice_type', 'notice') === 'notice' ? 'checked' : '' }}>
                                <div class="t">Serve a notice period</div><div class="d">Work 1&ndash;3 more months</div>
                            </label>
                            <label class="rsg-opt" data-v="immediate">
                                <input type="radio" name="notice_type" value="immediate" {{ old('notice_type') === 'immediate' ? 'checked' : '' }}>
                                <div class="t">Immediate effect</div><div class="d">Last day = resignation date</div>
                            </label>
                        </div>

                        <div id="rsg-months-wrap">
                            <label class="rsg-lbl">Notice period</label>
                            <div class="rsg-months">
                                @foreach ([1, 2, 3] as $m)
                                    <label data-m="{{ $m }}">
                                        <input type="radio" name="notice_months" value="{{ $m }}" {{ (int) old('notice_months', 1) === $m ? 'checked' : '' }}>
                                        <span>{{ $m }} month{{ $m > 1 ? 's' : '' }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="rsg-lwd d-flex justify-content-between align-items-center">
                            <div>
                                <div class="k">Proposed last working day</div>
                                <div class="v" id="rsg-lwd-value">—</div>
                            </div>
                            <i class="fa fa-calendar-check-o fa-2x" style="color:#1f7a4d;opacity:.3;"></i>
                        </div>

                        <button type="submit" class="btn rsg-btn" id="rsg-submit"><i class="fa fa-paper-plane mr-1"></i> Submit resignation</button>
                        <div class="rsg-note">You can only have one open resignation request at a time. HR may adjust the dates before approving.</div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>

@if (!$case)
<script>
    (function () {
        var $form = $('#rsg-form');
        function computeLwd() {
            var effect = $form.find('input[name=notice_type]:checked').val();
            var months = parseInt($form.find('input[name=notice_months]:checked').val(), 10) || 1;
            var base = $('#rsg-date').val();
            if (!base) { $('#rsg-lwd-value').text('—'); return; }
            var d = new Date(base + 'T00:00:00');
            if (effect === 'notice') {
                var target = d.getMonth() + months;
                d.setMonth(target);
            }
            $('#rsg-lwd-value').text(d.toLocaleDateString(undefined, { day: '2-digit', month: 'short', year: 'numeric' }));
        }
        function syncEffect() {
            var v = $form.find('input[name=notice_type]:checked').val();
            $form.find('.rsg-opt').each(function () { $(this).toggleClass('on', $(this).data('v') === v); });
            $('#rsg-months-wrap').toggle(v === 'notice');
            computeLwd();
        }
        function syncMonths() {
            var m = String($form.find('input[name=notice_months]:checked').val());
            $form.find('.rsg-months label').each(function () { $(this).toggleClass('on', String($(this).data('m')) === m); });
            computeLwd();
        }
        $form.on('change', 'input[name=notice_type]', syncEffect);
        $form.on('change', 'input[name=notice_months]', syncMonths);
        $('#rsg-date').on('change input', computeLwd);
        $form.on('submit', function (e) {
            if (!confirm('Submit your resignation for approval?')) { e.preventDefault(); }
        });
        syncEffect();
        syncMonths();
    })();
</script>
@endif
@endsection
