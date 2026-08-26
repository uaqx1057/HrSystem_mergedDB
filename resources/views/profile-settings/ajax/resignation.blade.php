<div class="col-xl-12 col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4">
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    @if ($resignation && $resignation->status !== 'rejected')
        <div class="card bg-white border-0 b-shadow-4">
            <div class="card-body">
                <h4 class="mb-3">Resignation request</h4>
                <x-cards.data-row label="Status" :value="ucfirst($resignation->status)" />
                <x-cards.data-row label="Resignation date" :value="optional($resignation->resignation_date)->format(company()->date_format) ?? '--'" />
                <x-cards.data-row label="Last working date" :value="optional($resignation->last_working_date)->format(company()->date_format) ?? '--'" />
                <x-cards.data-row label="Reason" :value="$resignation->reason ?: $resignation->terminate_reason ?: '--'" />
            </div>
        </div>
    @elseif ($user->status === 'Active')
        <div class="card bg-white border-0 b-shadow-4">
            <div class="card-body">
                <h4 class="mb-3">{{ $resignation ? 'Resignation rejected - submit again' : 'Submit resignation' }}</h4>
                @if ($resignation)
                    <div class="alert alert-warning">Your previous resignation request was rejected. You may submit a new request.</div>
                @endif
                <p class="text-muted">Submit your resignation for HR review and clearance.</p>
                <div id="resignation-form">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <div class="form-group">
                        <label for="resignation-reason">Reason</label>
                        <textarea class="form-control" id="resignation-reason" name="reason" required>{{ old('reason') }}</textarea>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="resignation-date">Resignation date</label>
                            <input class="form-control height-35 f-14" id="resignation-date" type="date" name="resignation_date" value="{{ old('resignation_date') }}" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="last-working-date">Last working date</label>
                            <input class="form-control height-35 f-14" id="last-working-date" type="date" name="last_working_date" value="{{ old('last_working_date') }}" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <button class="btn btn-warning" id="submit-resignation" type="button">Submit resignation</button>
                </div>
            </div>
        </div>
    @else
        <div class="alert alert-info">Resignation cannot be submitted while your account is inactive.</div>
    @endif
</div>

<script>
    $('body').off('click', '#submit-resignation').on('click', '#submit-resignation', function () {
        $.easyAjax({
            url: "{{ route('employees.resignation') }}",
            type: 'POST',
            data: $('#resignation-form input, #resignation-form textarea').serialize(),
            container: '#resignation-form',
            blockUI: true,
            success: function (response) {
                if (response.status === 'success') {
                    window.location.reload();
                }
            }
        });
    });
</script>
