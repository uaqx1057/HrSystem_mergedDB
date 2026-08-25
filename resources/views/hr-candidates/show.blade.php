@extends('layouts.app')
@push('datatable-styles')
    @include('sections.datatable_css')
@endpush
@section('filter-section')
    <!-- FILTER START -->
    <div class="d-flex filter-box project-header bg-white">
        <div class="mobile-close-overlay w-100 h-100" id="close-client-overlay"></div>
        <div class="project-menu d-lg-flex" id="mob-client-detail">

            <a class="d-none close-it" href="javascript:;" id="close-client-detail">
                <i class="fa fa-times"></i>
            </a>

            <nav class="tabs">
                <ul class="-primary">
                    <li>
                        <x-tab :href="route('hr-candidates.show', [
                            'candidate' => $candidate->id,
                            'tab' => 'detail',
                        ])" text="Candidate Detail" class="detail" ajax="false" />
                    </li>
                    @if ($candidate->status !== 'applied')
                        <li>
                            <x-tab :href="route('hr-candidates.show', [
                                'candidate' => $candidate->id,
                                'tab' => 'interview',
                            ])" text="Schedule Interview" class="interview" ajax="false" />
                        </li>
                        @if ($candidate->status == 'onboarding' || $candidate->status == 'converted')
                            <li>
                                <x-tab :href="route('hr-candidates.show', [
                                    'candidate' => $candidate->id,
                                    'tab' => 'pre_hire',
                                ])" text="Pre-hire Onboarding" class="pre_hire" ajax="false" />
                            </li>
                        @endif
                    @endif
                </ul>
            </nav>
        </div>
        <a class="mb-0 d-block d-lg-none text-dark-grey ml-auto mr-2 border-left-grey" onclick="openClientDetailSidebar()">
            <i class="fa fa-ellipsis-v"></i>
        </a>
    </div>
    <!-- PROJECT HEADER END -->
@endsection
@section('content')
    <div class="content-wrapper">

        @if ($activeTab === 'detail')
            <div class="card bg-white border-0 b-shadow-4 mb-3">
                <div
                    class="card-header form-heading-background  border-bottom-grey text-capitalize justify-content-between p-20">
                    <div class="row">
                        <div class="col-md-10 col-10">
                            <h3 class="heading-h1">Candidate Detail</h3>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    <div class="text-right d-flex justify-content-end">
                        @if ($candidate->status == 'applied')
                            <form method="POST" action="{{ route('hr-candidates.update_status', $candidate) }}"
                                class="ml-2">
                                @csrf
                                <input type="hidden" name="status" value="screening">
                                <button class="btn btn-sm btn-primary">Short list this candidate</button>
                            </form>
                        @endif

                        @if ($candidate->status == 'screening')
                            <button type="button" class="btn btn-primary btn-sm ml-2" data-toggle="modal"
                                data-target="#scheduleInterviewModal">
                                Schedule Interview
                            </button>
                        @endif

                        @if (
                            $candidate->interviews->isNotEmpty() &&
                                $candidate->interviews->every('outcome', 'pass') &&
                                !in_array($candidate->status, ['rejected', 'onboarding']))
                            <button type="button" class="btn btn-primary btn-sm ml-2" data-toggle="modal"
                                data-target="#approveModal">
                                Accept
                            </button>


                            @if ($candidate->interviews->isNotEmpty())
                                <button type="button" class="btn btn-outline-danger btn-sm ml-2" data-toggle="modal"
                                    data-target="#rejectModal">
                                    Reject
                                </button>
                            @endif
                        @endif

                        <a href="{{ route('hr-candidates.index') }}" class="btn btn-sm btn-secondary ml-2">Back</a>
                    </div>
                    <x-cards.data-row label="Full Name" :value="($candidate->salutation ? ucfirst($candidate->salutation) . ' ' : '') . $candidate->name" />
                    <x-cards.data-row label="Gender" :value="ucfirst($candidate->gender) ?? '-'" />
                    <x-cards.data-row label="Date of Birth" :value="$candidate->date_of_birth
                        ? \Carbon\Carbon::parse($candidate->date_of_birth)->format('d M Y')
                        : '-'" />
                    <x-cards.data-row label="Marital Status" :value="ucfirst($candidate->marital_status) ?? '-'" />

                    <x-cards.data-row label="Email" :value="$candidate->email" />
                    <x-cards.data-row label="Country" :value="$candidate->country->nicename ?? '-'" />
                    <x-cards.data-row label="Mobile" :value="$candidate->mobile ?? '-'" />
                    <x-cards.data-row label="Address" :value="$candidate->address ?? '-'" />
                    <x-cards.data-row label="LinkedIn" :value="$candidate->linkedin_username ?? '-'" />

                    <x-cards.data-row label="Employee Type" :value="ucfirst($candidate->employee_type)" />

                    @if ($candidate->employee_type == 'saudi')
                        <x-cards.data-row label="National ID" :value="$candidate->national_id ?? '-'" />
                        <x-cards.data-row label="National ID Expiry" :value="$candidate->national_id_expiry_date
                            ? \Carbon\Carbon::parse($candidate->national_id_expiry_date)->format('d M Y')
                            : '-'" />
                    @else
                        <x-cards.data-row label="Iqama Number" :value="$candidate->iqama_no ?? '-'" />
                        <x-cards.data-row label="Iqama Profession" :value="$candidate->iqama_profession ?? '-'" />
                        <x-cards.data-row label="Iqama Expiry" :value="$candidate->iqama_expiry_date
                            ? \Carbon\Carbon::parse($candidate->iqama_expiry_date)->format('d M Y')
                            : '-'" />
                    @endif

                    <x-cards.data-row label="Passport Number" :value="$candidate->passport_no ?? '-'" />
                    <x-cards.data-row label="Passport Expiry" :value="$candidate->passport_expiry_date
                        ? \Carbon\Carbon::parse($candidate->passport_expiry_date)->format('d M Y')
                        : '-'" />

                    <x-cards.data-row label="Designation" :value="$candidate->designation ?? ($candidate->jobOpening->title ?? '-')" />

                    <x-cards.data-row label="Expect Salary" :value="$candidate->basic_salary ? number_format($candidate->basic_salary, 2) : '-'" />
                    {{-- <x-cards.data-row label="Application Source" :value="str_replace('_', ' ', ucfirst($candidate->source))" /> --}}
                    <x-cards.data-row label="Status" :value="ucfirst($candidate->status)" />

                    @foreach ($candidate->documents as $doc)
                        @php
                            $fileDir = match ($doc->document_type) {
                                'profile_picture' => 'avatar',
                                'iqama' => 'iqama',
                                'national_id' => 'national_id',
                                'passport' => 'passport',
                                'bank_account', 'contract_signed', 'resume' => 'candidate-documents',
                                default => 'candidate-documents',
                            };
                            $url = asset_url_local_s3($fileDir . '/' . $doc->stored_path);
                            $file_url =
                                '<a role="button" href="' .
                                $url .
                                '" target="_blank">' .
                                strtoupper($doc->document_type) .
                                '</a>';
                        @endphp

                        <x-cards.data-row :label="ucfirst($doc->document_type)" :value="$file_url" html="true" />
                    @endforeach

                    @if ($candidate->notes)
                        <x-cards.data-row label="Other Detail" :value="$candidate->notes" />
                    @endif
                </div>
            </div>


            {{-- <div class="card">
            <div class="card-body">
                <h4>{{ $candidate->name }} <small
                        class="text-muted">{{ ucwords(str_replace('_', ' ', $candidate->status)) }}</small></h4>
                <p>{{ $candidate->email }} &middot; {{ $candidate->mobile }}</p>
                <p>Applying for: {{ $candidate->jobOpening?->title ?? ($candidate->designation ?? 'General application') }}
                </p>
                @if ($candidate->cover_note)
                    <p><strong>Cover note:</strong><br>{{ $candidate->cover_note }}</p>
                @endif

                <h5>Documents</h5>
                <ul>
                    @forelse($candidate->documents as $doc)
                        <li><a href="{{ $doc->file_url }}" target="_blank">{{ $doc->original_name }}</a>
                            ({{ $doc->document_type }})
                        </li>
                    @empty
                        <li>No documents uploaded.</li>
                    @endforelse
                </ul>
            </div>
        </div> --}}

            @if (!in_array($candidate->status, ['rejected', 'converted']))
                {{-- <div class="card mt-3">
                    <div class="card-body">
                        <h5>Review actions</h5>

                        <button type="button" class="btn btn-outline-primary btn-sm" data-toggle="modal"
                            data-target="#updateStatusModal">
                            Update status
                        </button>
                        <button type="button" class="btn btn-outline-danger btn-sm" data-toggle="modal"
                            data-target="#rejectModal">
                            Reject
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#approveModal" style="padding: 0.25rem 0.5rem;">
                            Approve &amp; Start Onboarding
                        </button>
                    </div>
                </div> --}}

                {{-- Update Status Modal --}}
                <div class="modal fade" id="updateStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="POST" action="{{ route('hr-candidates.update_status', $candidate) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Update Status</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Status</label>
                                        <select name="status" class="form-control height-35" data-size="8">
                                            @foreach (['new', 'applied', 'screening', 'interviewed'] as $s)
                                                <option value="{{ $s }}" @selected($candidate->status == $s)>
                                                    {{ ucwords($s) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Update status</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Reject Modal --}}
                <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="POST" action="{{ route('hr-candidates.reject', $candidate) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reject Candidate</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Rejection reason</label>
                                        <input type="text" name="rejection_reason" class="form-control height-35"
                                            data-size="8" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Reject</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Approve & Start Onboarding Modal --}}
                <div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="POST" action="{{ route('hr-candidates.approve', $candidate) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Accept Candidate</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Department</label>
                                        <select name="department_id" class="form-control height-35" data-size="8">
                                            <option value="">Department</option>
                                            @foreach (\App\Models\Team::where('company_id', user()->company_id)->orderBy('team_name')->get() as $d)
                                                <option value="{{ $d->id }}" @selected($candidate->department_id == $d->id)>
                                                    {{ $d->team_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Designation</label>
                                        <select name="designation_id" class="form-control height-35" data-size="8">
                                            <option value="">Designation</option>
                                            @foreach (\App\Models\Designation::allDesignations() as $d)
                                                <option value="{{ $d->id }}" @selected($candidate->designation_id == $d->id)>
                                                    {{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>Basic salary</label>
                                        <input type="number" step="0.01" name="basic_salary"
                                            class="form-control height-35" data-size="8"
                                            value="{{ $candidate->basic_salary }}">
                                    </div>

                                    <div class="form-group">
                                        <label>Probation Time</label>
                                        <input type="text" name="probation_time" class="form-control height-35"
                                            data-size="8" placeholder="In month"
                                            value="{{ $candidate->probation_time }}">
                                    </div>

                                    <hr>
                                    <label class="f-w-500 mb-2">Allowances</label>
                                    <div id="approve-allowances-rows"></div>
                                    <button type="button" id="approve-add-allowance-btn"
                                        class="btn btn-outline-primary btn-sm mb-2">
                                        <i class="fa fa-plus mr-1"></i> Add Allowance
                                    </button>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Accept</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- <div class="card mt-3">
                    <div class="card-body">
                        <h5>Schedule interview</h5>
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                            data-target="#scheduleInterviewModal">
                            Schedule on calendar
                        </button>
                    </div>
                </div> --}}

                {{-- Schedule Interview Modal --}}
                <div class="modal fade" id="scheduleInterviewModal" tabindex="-1" role="dialog" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <form method="POST" action="{{ route('hr-candidates.schedule_interview', $candidate) }}">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Schedule Interview</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <label>Round</label>
                                        <input type="text" name="round" class="form-control height-35"
                                            data-size="8" placeholder="Round (e.g. HR Screening)" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Start date/time</label>
                                        <input type="datetime-local" id="start_date_time" name="start_date_time"
                                            data-size="8" class="form-control height-35" required>
                                    </div>
                                    <div class="form-group">
                                        <label>End date/time</label>
                                        <input type="datetime-local" id="end_date_time" name="end_date_time"
                                            data-size="8" class="form-control height-35" required>
                                    </div>
                                    <div class="form-group">
                                        <label>Location / link</label>
                                        <input type="text" name="where" class="form-control height-35"
                                            data-size="8" placeholder="Location / link">
                                    </div>
                                    <div class="form-group">
                                        <label>Interviewers</label>
                                        <select name="interviewer_ids[]"
                                            class="form-control select2-interviewers height-35" multiple>
                                            @foreach ($interviewers as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal"
                                        style="padding: 0.25rem 0.5rem;">Cancel</button>
                                    <button type="submit" class="btn btn-primary"
                                        style="padding: 0.25rem 0.5rem;">Schedule
                                        on calendar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            @endif

        @endif

        @if ($activeTab === 'interview')
            <div class="d-flex flex-column w-tables rounded mt-3 bg-white">
                {!! $dataTable->table(['class' => 'table table-hover border-0 w-100']) !!}
            </div>
            @push('scripts')
                @include('sections.datatable_js')
            @endpush
        @endif

        @if ($activeTab === 'pre_hire')

            @if ($candidate->onboardingCase)
                @php
                    $case = $candidate->onboardingCase;
                    $isConverted = $candidate->status === 'converted' && $candidate->converted_employee_id;
                @endphp
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Pre-hire Onboarding Checklist</h5>
                            <span class="badge {{ $isConverted ? 'badge-success' : 'badge-warning' }} text-uppercase">
                                {{ $isConverted ? 'Converted to Employee' : $case->status }}
                            </span>
                        </div>

                        <form method="POST" action="{{ route('hr-candidates.onboarding_checklist.save', $candidate) }}"
                            enctype="multipart/form-data">
                            @csrf

                            <ul class="list-group list-group-flush">

                                {{-- 1. Documents --}}
                                <li class="list-group-item px-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="items[documents_verified]" value="1"
                                                id="check_documents_verified" class="custom-control-input"
                                                @checked($case->documents_verified)>
                                            <label class="custom-control-label" for="check_documents_verified">
                                                Verify submitted documents (ID/Iqama/Passport/certificates)
                                            </label>
                                        </div>
                                        <span
                                            class="badge badge-pill {{ $case->documents_verified ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $case->documents_verified ? 'Done' : 'Pending' }}
                                        </span>
                                    </div>


                                </li>

                                {{-- 2. Compensation --}}
                                <li class="list-group-item px-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="items[compensation_confirmed]" value="1"
                                                id="check_compensation_confirmed" class="custom-control-input"
                                                @checked($case->compensation_confirmed)>
                                            <label class="custom-control-label" for="check_compensation_confirmed">
                                                Confirm compensation, designation, branch & department assignment
                                            </label>
                                        </div>
                                        <span
                                            class="badge badge-pill {{ $case->compensation_confirmed ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $case->compensation_confirmed ? 'Done' : 'Pending' }}
                                        </span>
                                    </div>

                                    <div class="row p-3">
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">Designation</label>
                                            <select name="designation_id" class="form-control height-35">
                                                <option value="">Select Designation</option>
                                                @foreach (\App\Models\Designation::allDesignations() as $d)
                                                    <option value="{{ $d->id }}" @selected(old('designation_id', $candidate->designation_id) == $d->id)>
                                                        {{ $d->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">Department</label>
                                            <select name="department_id" class="form-control height-35">
                                                <option value="">Select Department</option>
                                                @foreach (\App\Models\Team::where('company_id', user()->company_id)->orderBy('team_name')->get() as $d)
                                                    <option value="{{ $d->id }}" @selected(old('department_id', $candidate->department_id) == $d->id)>
                                                        {{ $d->team_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">Branch</label>
                                            <select name="branch_id" class="form-control height-35">
                                                <option value="">Select Branch</option>
                                                @foreach (\App\Models\Branch::orderBy('name')->get() as $b)
                                                    <option value="{{ $b->id }}" @selected(old('branch_id', $candidate->branch_id) == $b->id)>
                                                        {{ $b->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">Basic Salary</label>
                                            <input type="number" step="0.01" class="form-control height-35"
                                                name="basic_salary"
                                                value="{{ old('basic_salary', $candidate->basic_salary) }}">
                                        </div>

                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">Probation Time</label>
                                            <input type="text" name="probation_time" class="form-control height-35"
                                            data-size="8" placeholder="In month"
                                            value="{{ $candidate->probation_time }}">
                                        </div>

                                        <div class="col-lg-12 mb-2">
                                            <label class="f-14 text-dark-grey">Allowances</label>
                                            <div id="prehire-allowances-rows">
                                                @foreach ($candidate->allowances as $i => $allowance)
                                                    <div class="row allowance-row p-2 mb-2 mx-0"
                                                        data-index="{{ $i }}">
                                                        <div class="col-lg-5 col-md-5 px-1">
                                                            <input type="text" class="form-control height-35 f-14"
                                                                name="allowances[{{ $i }}][name]"
                                                                value="{{ old("allowances.$i.name", $allowance->name) }}"
                                                                placeholder="Allowance name" required>
                                                        </div>
                                                        <div class="col-lg-4 col-md-4 px-1">
                                                            <input type="number" class="form-control height-35 f-14"
                                                                name="allowances[{{ $i }}][amount]"
                                                                value="{{ old("allowances.$i.amount", $allowance->amount) }}"
                                                                placeholder="Amount" min="0" step="0.01"
                                                                required>
                                                        </div>
                                                        <input type="hidden" name="allowances[{{ $i }}][id]"
                                                            value="{{ $allowance->id }}">
                                                        <div class="col-lg-2 col-md-2 px-1 d-flex align-items-center">
                                                            <button type="button"
                                                                class="btn btn-danger btn-sm remove-prehire-allowance-btn">
                                                                <i class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <button type="button" id="prehire-add-allowance-btn"
                                                class="btn btn-outline-primary btn-sm mt-1">
                                                <i class="fa fa-plus mr-1"></i> Add Allowance
                                            </button>
                                        </div>
                                    </div>
                                </li>

                                {{-- 3. Bank details --}}
                                <li class="list-group-item px-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="items[bank_details_collected]" value="1"
                                                id="check_bank_details_collected" class="custom-control-input"
                                                @checked($case->bank_details_collected)>
                                            <label class="custom-control-label" for="check_bank_details_collected">
                                                Confirm bank account details collected (for payroll setup)
                                            </label>
                                        </div>
                                        <span
                                            class="badge badge-pill {{ $case->bank_details_collected ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $case->bank_details_collected ? 'Done' : 'Pending' }}
                                        </span>
                                    </div>

                                    <div class="row p-3">
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">Bank Name</label>
                                            <input type="text" class="form-control height-35" name="bank_name"
                                                value="{{ old('bank_name', $candidate->bank_name) }}">
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">IBAN Number</label>
                                            <input type="text" class="form-control height-35" name="iban_number"
                                                value="{{ old('iban_number', $candidate->iban_number) }}">
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">Account Number</label>
                                            <input type="text" class="form-control height-35" name="account_number"
                                                value="{{ old('account_number', $candidate->account_number) }}">
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <label class="f-14 text-dark-grey">SWIFT Code</label>
                                            <input type="text" class="form-control height-35" name="swift_code"
                                                value="{{ old('swift_code', $candidate->swift_code) }}">
                                        </div>
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <x-forms.file allowedFileExtensions="pdf png jpg jpeg bmp"
                                                class="mr-0 mr-lg-2 mr-md-2 cropper" fieldLabel="Bank Document"
                                                fieldName="bank_document" fieldId="bank_document" :fieldValue="optional(
                                                    $candidate->documents->firstWhere('document_type', 'bank_account'),
                                                )->file_url" />
                                        </div>
                                    </div>
                                </li>

                                {{-- 4. Contract --}}
                                <li class="list-group-item px-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="items[contract_signed]" value="1"
                                                id="check_contract_signed" class="custom-control-input"
                                                @checked($case->contract_signed)>
                                            <label class="custom-control-label" for="check_contract_signed">
                                                Employment contract signed (upload the signed copy)
                                            </label>
                                        </div>
                                        <span
                                            class="badge badge-pill {{ $case->contract_signed ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $case->contract_signed ? 'Done' : 'Pending' }}
                                        </span>
                                    </div>

                                    <div class="row p-3">
                                        <div class="col-lg-3 col-md-6 mb-2">
                                            <x-forms.file allowedFileExtensions="pdf png jpg jpeg bmp"
                                                class="mr-0 mr-lg-2 mr-md-2 cropper" fieldLabel="Signed Contract Document"
                                                fieldName="contract_document" fieldId="contract_document"
                                                :fieldValue="optional(
                                                    $candidate->documents->firstWhere(
                                                        'document_type',
                                                        'contract_signed',
                                                    ),
                                                )->file_url" />
                                        </div>
                                    </div>
                                </li>

                                {{-- 5. Manager sign-off --}}
                                <li class="list-group-item px-0">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="custom-control custom-checkbox">
                                            <input type="checkbox" name="items[manager_signoff]" value="1"
                                                id="check_manager_signoff" class="custom-control-input"
                                                @checked($case->manager_signoff)>
                                            <label class="custom-control-label" for="check_manager_signoff">
                                                Manager / final sign-off
                                            </label>
                                        </div>
                                        <span
                                            class="badge badge-pill {{ $case->manager_signoff ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $case->manager_signoff ? 'Done' : 'Pending' }}
                                        </span>
                                    </div>
                                </li>

                            </ul>

                            <div class="alert alert-info mt-3 mb-3 py-2">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="convert_to_employee" value="1"
                                        id="convert_to_employee" class="custom-control-input"
                                        @checked($isConverted || $case->convert_to_employee) @disabled($isConverted)>
                                    <label class="custom-control-label f-w-500" for="convert_to_employee">
                                        Convert this candidate to an employee once all items above are checked
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-1">
                                    Until this is checked (and every item above is done), progress here only updates the
                                    candidate record.
                                    Checking it and completing the checklist creates the real employee account — from then
                                    on,
                                    any
                                    further edits saved here also update that employee record.
                                </small>
                            </div>

                            <div class="text-right mt-3">
                                <button type="submit" class="btn btn-primary btn-sm">
                                    Save Checklist
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        @endif
    </div>
@endsection

@push('styles')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <script>
        $(function() {
            $('.select2-interviewers').select2({
                placeholder: 'Select interviewers',
                width: '100%',
                dropdownParent: $('#scheduleInterviewModal')
            });
        });
    </script>
    <script>
        $(function() {
            ['iqama_expiry_date', 'national_id_expiry_date', 'passport_expiry_date'].forEach(function(id) {
                datepicker('#' + id, {
                    position: 'bl',
                    ...datepickerConfig
                });
            });
        });
    </script>
    <script>
        $(function() {
            function toggleEmployeeTypeFields() {
                var isSaudi = $('#employee_type').val() === 'saudi';

                $('.expat-only-field').toggleClass('d-none', isSaudi);
                $('.expat-only-field').find('input, select, textarea').prop('disabled', isSaudi);

                $('.saudi-only-field').toggleClass('d-none', !isSaudi);
                $('.saudi-only-field').find('input, select, textarea').prop('disabled', !isSaudi);

                $('#iqama_no, #iqama_profession').prop('required', !isSaudi);
                $('#national_id').prop('required', isSaudi);

                $('#passport_no').prop('required', !isSaudi);
                $('label[for="passport_no"] sup').toggle(!isSaudi);
            }

            $('#employee_type').on('change', toggleEmployeeTypeFields);
            toggleEmployeeTypeFields(); // run on load
        });
    </script>
    <script>
        $('.cropper').on('dropify.fileReady', function(e) {
            var inputId = $(this).find('input').attr('id');
            var url = "{{ route('cropper', ':element') }}";
            url = url.replace(':element', inputId);
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });
    </script>
    <script>
        const activeTab = "{{ $activeTab }}";
        $('.project-menu .' + activeTab).addClass('active');
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            const startDateTime = document.getElementById('start_date_time');
            const endDateTime = document.getElementById('end_date_time');

            function getCurrentDateTime() {
                const now = new Date();

                now.setMinutes(now.getMinutes() - now.getTimezoneOffset());

                return now.toISOString().slice(0, 16);
            }

            const now = getCurrentDateTime();

            // Disable past date/time
            startDateTime.min = now;
            endDateTime.min = now;

            // End date/time must be after start date/time
            startDateTime.addEventListener('change', function() {
                endDateTime.min = startDateTime.value;

                if (endDateTime.value && endDateTime.value <= startDateTime.value) {
                    endDateTime.value = '';
                }
            });

        });
    </script>

    <script>
        $(function() {
            var approveAllowanceIndex = 0;

            function addApproveAllowanceRow() {
                var row = `
                <div class="row allowance-row p-2 mb-2 mx-0" data-index="${approveAllowanceIndex}">
                    <div class="col-6 px-1">
                        <input type="text" class="form-control height-35 f-14"
                               name="allowances[${approveAllowanceIndex}][name]"
                               placeholder="Allowance name" required>
                    </div>
                    <div class="col-5 px-1">
                        <input type="number" class="form-control height-35 f-14"
                               name="allowances[${approveAllowanceIndex}][amount]"
                               placeholder="Amount" min="0" step="0.01" required>
                    </div>
                    <div class="col-1 px-1 d-flex align-items-center">
                        <button type="button" class="btn btn-danger btn-sm remove-approve-allowance-btn">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>`;
                $('#approve-allowances-rows').append(row);
                approveAllowanceIndex++;
            }

            $('#approve-add-allowance-btn').on('click', function() {
                addApproveAllowanceRow();
            });

            $(document).on('click', '.remove-approve-allowance-btn', function() {
                $(this).closest('.allowance-row').remove();
                $('#approve-allowances-rows .allowance-row').each(function(i) {
                    $(this).attr('data-index', i);
                    $(this).find('[name]').each(function() {
                        var newName = $(this).attr('name').replace(/\[\d+\]/, '[' + i +
                            ']');
                        $(this).attr('name', newName);
                    });
                });
                approveAllowanceIndex = $('#approve-allowances-rows .allowance-row').length;
            });

            // Reset rows every time the modal is reopened
            $('#approveModal').on('show.bs.modal', function() {
                $('#approve-allowances-rows').empty();
                approveAllowanceIndex = 0;
            });
        });
    </script>

    <script>
        $(function() {
            var prehireAllowanceIndex = {{ $candidate->allowances->count() }};

            function addPrehireAllowanceRow() {
                var row = `
                <div class="row allowance-row p-2 mb-2 mx-0" data-index="${prehireAllowanceIndex}">
                    <div class="col-lg-5 col-md-5 px-1">
                        <input type="text" class="form-control height-35 f-14"
                               name="allowances[${prehireAllowanceIndex}][name]"
                               placeholder="Allowance name" required>
                    </div>
                    <div class="col-lg-4 col-md-4 px-1">
                        <input type="number" class="form-control height-35 f-14"
                               name="allowances[${prehireAllowanceIndex}][amount]"
                               placeholder="Amount" min="0" step="0.01" required>
                    </div>
                    <div class="col-lg-2 col-md-2 px-1 d-flex align-items-center">
                        <button type="button" class="btn btn-danger btn-sm remove-prehire-allowance-btn">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>`;
                $('#prehire-allowances-rows').append(row);
                prehireAllowanceIndex++;
            }

            $('#prehire-add-allowance-btn').on('click', function() {
                addPrehireAllowanceRow();
            });

            $(document).on('click', '.remove-prehire-allowance-btn', function() {
                $(this).closest('.allowance-row').remove();
            });
        });
    </script>
@endpush
