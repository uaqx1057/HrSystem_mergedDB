@extends('layouts.app')

@section('content')
    <div class="content-wrapper">

        <div class="d-flex justify-content-between align-items-center mb-3">

        </div>

        <div class="card mb-3">
            <div class="card-body">
                <form method="GET" action="{{ route('asset-loss-deductions.index') }}">
                    <div class="row align-items-end">

                        <div class="col-md-4">
                            <label class="f-14 f-w-500">Employee</label>
                            <select name="employee_id" class="form-control select-picker" data-live-search="true">
                                <option value="">-- All Employees --</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee->id }}"
                                        {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                        {{ $employee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="f-14 f-w-500">Company Assets</label>
                            <select name="company_asset_id" class="form-control select-picker" data-live-search="true">
                                <option value="">-- All Assets --</option>
                                @foreach ($companyAssets as $asset)
                                    <option value="{{ $asset->id }}"
                                        {{ request('company_asset_id') == $asset->id ? 'selected' : '' }}>
                                        {{ $asset->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary mr-2">
                                <i class="fa fa-filter mr-1"></i> Filter
                            </button>
                            <a href="{{ route('asset-loss-deductions.index') }}" class="btn btn-secondary">
                                <i class="fa fa-times mr-1"></i> Reset
                            </a>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header form-heading-background d-flex justify-content-between align-items-center">
                <span>Asset Loss Deductions</span>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered text-nowrap">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Company Asset</th>
                            <th>Serial No</th>
                            <th>Loss Amount</th>
                            <th>Deducted Amount</th>
                            <th>Remaining</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($assetDeductions as $deduction)
                            <tr>
                                <td>{{ optional($deduction->employee)->name }}</td>
                                <td>{{ optional($deduction->companyAsset)->name }}</td>
                                <td>{{ optional($deduction->assetLoss)->serial_no }}</td>
                                <td>{{ $deduction->loss_amount }}</td>
                                <td>{{ $deduction->deducted_amount }}</td>
                                <td>{{ $deduction->loss_amount - $deduction->deducted_amount }}</td>
                                <td class="{{ $deduction->status == 'Deducted' ? 'text-success' : 'text-warning' }}">
                                    <strong>{{ ucfirst($deduction->status) }}</strong></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Not Available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $assetDeductions->links() }}
            </div>
        </div>
    </div>
@endsection
