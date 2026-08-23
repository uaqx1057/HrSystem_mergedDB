@extends('layouts.app')
@section('content')
    <div class="content-wrapper">
        <div class="card">
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
        </div>

        @if (!in_array($candidate->status, ['rejected', 'converted']))
            <div class="card mt-3">
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
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#approveModal" style="padding: 0.25rem 0.5rem;">
                        Approve &amp; Start Onboarding
                    </button>
                </div>
            </div>

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
                                    <input type="text" name="rejection_reason" class="form-control height-35" data-size="8" required>
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
                                <h5 class="modal-title">Approve &amp; Start Onboarding</h5>
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
                                    <input type="number" step="0.01" name="basic_salary" class="form-control height-35" data-size="8"
                                        value="{{ $candidate->basic_salary }}">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-success">Approve &amp; Start Onboarding</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-body">
                    <h5>Schedule interview</h5>
                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                        data-target="#scheduleInterviewModal">
                        Schedule on calendar
                    </button>
                </div>
            </div>

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
                                    <input type="text" name="round" class="form-control height-35" data-size="8"
                                        placeholder="Round (e.g. HR Screening)" required>
                                </div>
                                <div class="form-group">
                                    <label>Start date/time</label>
                                    <input type="datetime-local" name="start_date_time" data-size="8" class="form-control height-35" required>
                                </div>
                                <div class="form-group">
                                    <label>End date/time</label>
                                    <input type="datetime-local" name="end_date_time" data-size="8" class="form-control height-35" required>
                                </div>
                                <div class="form-group">
                                    <label>Location / link</label>
                                    <input type="text" name="where" class="form-control height-35" data-size="8"
                                        placeholder="Location / link">
                                </div>
                                <div class="form-group">
                                    <label>Interviewers</label>
                                    <select name="interviewer_ids[]" class="form-control select2-interviewers height-35"  multiple>
                                        @foreach ($interviewers as $u)
                                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Schedule on calendar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <div class="card mt-3">
            <div class="card-body">
                <h5>Interviews</h5>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Round</th>
                            <th>When</th>
                            <th>Status</th>
                            <th>Outcome</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($candidate->interviews as $interview)
                            <tr>
                                <td>{{ $interview->round }}</td>
                                <td>{{ $interview->event?->start_date_time }}</td>
                                <td>{{ $interview->status }}</td>
                                <td>{{ $interview->outcome }}</td>
                                <td>
                                    @if ($interview->status !== 'completed')
                                        <form method="POST"
                                            action="{{ route('hr-interview-schedules.outcome', $interview) }}"
                                            class="form-inline">
                                            @csrf
                                            <select name="outcome" class="form-control form-control-sm mr-1" style="height: 32px !important;">
                                                <option value="pass">Pass</option>
                                                <option value="fail">Fail</option>
                                                <option value="pending">Pending</option>
                                            </select>
                                            <button class="btn btn-sm btn-outline-primary">Record outcome</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- @if ($candidate->onboardingCase)
            <div class="card mt-3">
                <div class="card-body">
                    <h5>Pre-hire onboarding checklist ({{ $candidate->onboardingCase->status }})</h5>
                    <ul class="list-unstyled">
                        @foreach ($candidate->onboardingCase->tasks as $task)
                            <li class="mb-1">
                                <form method="POST" action="{{ route('hr-candidate-onboarding.tasks.update', $task) }}"
                                    class="form-inline">
                                    @csrf
                                    <input type="hidden" name="complete" value="0">
                                    <input type="checkbox" name="complete" value="1" onchange="this.form.submit()"
                                        @checked($task->status == 'completed') class="mr-2">
                                    {{ $task->title }} {{ $task->status }}
                                </form>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif --}}

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

                    <form method="POST" action="{{ route('hr-candidates.onboarding_checklist.save', $candidate) }}" enctype="multipart/form-data">
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
                                    <span class="badge badge-pill {{ $case->documents_verified ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $case->documents_verified ? 'Done' : 'Pending' }}
                                    </span>
                                </div>

                                <div class="row p-3">
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Employee Type <span class="text-danger">*</span></label>
                                        <select name="employee_type" id="employee_type" class="form-control height-35" required>
                                            <option value="expat" @selected(old('employee_type', $candidate->employee_type) === 'expat')>Expat</option>
                                            <option value="saudi" @selected(old('employee_type', $candidate->employee_type) === 'saudi')>Saudi</option>
                                        </select>
                                    </div>

                                    {{-- EXPAT ONLY --}}
                                    <div class="col-lg-3 col-md-6 mb-2 expat-only-field">
                                        <label class="f-14 text-dark-grey">Iqama No</label>
                                        <input type="text" class="form-control height-35" name="iqama_no" id="iqama_no"
                                            placeholder="Iqama No" value="{{ old('iqama_no', $candidate->iqama_no) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2 expat-only-field">
                                        <label class="f-14 text-dark-grey">Iqama Profession</label>
                                        <input type="text" class="form-control height-35" name="iqama_profession" id="iqama_profession"
                                            placeholder="Iqama Profession" value="{{ old('iqama_profession', $candidate->iqama_profession) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2 expat-only-field">
                                        <label class="f-14 text-dark-grey">Iqama Expiry Date</label>
                                        <input type="text" class="form-control height-35 datepicker-field" name="iqama_expiry_date" id="iqama_expiry_date"
                                            placeholder="DD-MM-YYYY" autocomplete="off"
                                            value="{{ old('iqama_expiry_date', $candidate->iqama_expiry_date?->format(company()->date_format)) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2 expat-only-field">
                                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                            fieldLabel="Iqama Image" fieldName="iqama_image" fieldId="iqama_image"
                                            :fieldValue="optional($candidate->documents->firstWhere('document_type', 'iqama'))->file_url" />
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2 expat-only-field">
                                        <x-forms.file allowedFileExtensions="pdf png jpg jpeg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                            fieldLabel="Qiwa Contract File" fieldName="qiva_contract" fieldId="qiva_contract"
                                            :fieldValue="optional($candidate->documents->firstWhere('document_type', 'qiva_contract'))->file_url" />
                                    </div>

                                    {{-- SAUDI ONLY --}}
                                    <div class="col-lg-3 col-md-6 mb-2 saudi-only-field d-none">
                                        <label class="f-14 text-dark-grey">National ID</label>
                                        <input type="text" class="form-control height-35" name="national_id" id="national_id"
                                            placeholder="National ID" value="{{ old('national_id', $candidate->national_id) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2 saudi-only-field d-none">
                                        <label class="f-14 text-dark-grey">National ID Expiry Date</label>
                                        <input type="text" class="form-control height-35 datepicker-field" name="national_id_expiry_date" id="national_id_expiry_date"
                                            placeholder="DD-MM-YYYY" autocomplete="off"
                                            value="{{ old('national_id_expiry_date', $candidate->national_id_expiry_date?->format(company()->date_format)) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2 saudi-only-field d-none">
                                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                            fieldLabel="National ID Image" fieldName="national_id_image" fieldId="national_id_image"
                                            :fieldValue="optional($candidate->documents->firstWhere('document_type', 'national_id'))->file_url" />
                                    </div>

                                    {{-- SHARED --}}
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Passport No</label>
                                        <input type="text" class="form-control height-35" name="passport_no" id="passport_no"
                                            placeholder="Passport No" value="{{ old('passport_no', $candidate->passport_no) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Passport Expiry Date</label>
                                        <input type="text" class="form-control height-35 datepicker-field" name="passport_expiry_date" id="passport_expiry_date"
                                            placeholder="DD-MM-YYYY" autocomplete="off"
                                            value="{{ old('passport_expiry_date', $candidate->passport_expiry_date?->format(company()->date_format)) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <x-forms.file allowedFileExtensions="png jpg jpeg svg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                            fieldLabel="Passport Image" fieldName="passport_image" fieldId="passport_image"
                                            :fieldValue="optional($candidate->documents->firstWhere('document_type', 'passport'))->file_url" />
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <x-forms.file allowedFileExtensions="pdf png jpg jpeg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                            fieldLabel="Company Contract File" fieldName="company_contract" fieldId="company_contract"
                                            :fieldValue="optional($candidate->documents->firstWhere('document_type', 'company_contract'))->file_url" />
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Probation Time</label>
                                        <input type="text" class="form-control height-35" name="probation_time" id="probation_time"
                                            placeholder="Probation Time" value="{{ old('probation_time', $candidate->probation_time) }}">
                                    </div>
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
                                    <span class="badge badge-pill {{ $case->compensation_confirmed ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $case->compensation_confirmed ? 'Done' : 'Pending' }}
                                    </span>
                                </div>

                                <div class="row p-3">
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Designation</label>
                                        <select name="designation_id" class="form-control height-35">
                                            <option value="">Select Designation</option>
                                            @foreach (\App\Models\Designation::allDesignations() as $d)
                                                <option value="{{ $d->id }}" @selected(old('designation_id', $candidate->designation_id) == $d->id)>{{ $d->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Department</label>
                                        <select name="department_id" class="form-control height-35">
                                            <option value="">Select Department</option>
                                            @foreach (\App\Models\Team::where('company_id', user()->company_id)->orderBy('team_name')->get() as $d)
                                                <option value="{{ $d->id }}" @selected(old('department_id', $candidate->department_id) == $d->id)>{{ $d->team_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Branch</label>
                                        <select name="branch_id" class="form-control height-35">
                                            <option value="">Select Branch</option>
                                            @foreach (\App\Models\Branch::orderBy('name')->get() as $b)
                                                <option value="{{ $b->id }}" @selected(old('branch_id', $candidate->branch_id) == $b->id)>{{ $b->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Basic Salary</label>
                                        <input type="number" step="0.01" class="form-control height-35" name="basic_salary"
                                            value="{{ old('basic_salary', $candidate->basic_salary) }}">
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
                                    <span class="badge badge-pill {{ $case->bank_details_collected ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $case->bank_details_collected ? 'Done' : 'Pending' }}
                                    </span>
                                </div>

                                <div class="row p-3">
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Bank Name</label>
                                        <input type="text" class="form-control height-35" name="bank_name" value="{{ old('bank_name', $candidate->bank_name) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">IBAN Number</label>
                                        <input type="text" class="form-control height-35" name="iban_number" value="{{ old('iban_number', $candidate->iban_number) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">Account Number</label>
                                        <input type="text" class="form-control height-35" name="account_number" value="{{ old('account_number', $candidate->account_number) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <label class="f-14 text-dark-grey">SWIFT Code</label>
                                        <input type="text" class="form-control height-35" name="swift_code" value="{{ old('swift_code', $candidate->swift_code) }}">
                                    </div>
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <x-forms.file allowedFileExtensions="pdf png jpg jpeg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                            fieldLabel="Bank Document" fieldName="bank_document" fieldId="bank_document"
                                            :fieldValue="optional($candidate->documents->firstWhere('document_type', 'bank_account'))->file_url" />
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
                                    <span class="badge badge-pill {{ $case->contract_signed ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $case->contract_signed ? 'Done' : 'Pending' }}
                                    </span>
                                </div>

                                <div class="row p-3">
                                    <div class="col-lg-3 col-md-6 mb-2">
                                        <x-forms.file allowedFileExtensions="pdf png jpg jpeg bmp" class="mr-0 mr-lg-2 mr-md-2 cropper"
                                            fieldLabel="Signed Contract Document" fieldName="contract_document" fieldId="contract_document"
                                            :fieldValue="optional($candidate->documents->firstWhere('document_type', 'contract_signed'))->file_url" />
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
                                    <span class="badge badge-pill {{ $case->manager_signoff ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $case->manager_signoff ? 'Done' : 'Pending' }}
                                    </span>
                                </div>
                            </li>

                        </ul>

                        <div class="alert alert-info mt-3 mb-3 py-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="convert_to_employee" value="1"
                                    id="convert_to_employee" class="custom-control-input"
                                    @checked($isConverted || $case->convert_to_employee)
                                    @disabled($isConverted)>
                                <label class="custom-control-label f-w-500" for="convert_to_employee">
                                    Convert this candidate to an employee once all items above are checked
                                </label>
                            </div>
                            <small class="text-muted d-block mt-1">
                                Until this is checked (and every item above is done), progress here only updates the candidate record.
                                Checking it and completing the checklist creates the real employee account — from then on, any
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
    </div>
@endsection

@push('styles')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(function () {
        $('.select2-interviewers').select2({
            placeholder: 'Select interviewers',
            width: '100%',
            dropdownParent: $('#scheduleInterviewModal')
        });
    });
</script>
<script>
    $(function () {
        ['iqama_expiry_date', 'national_id_expiry_date', 'passport_expiry_date'].forEach(function (id) {
            datepicker('#' + id, { position: 'bl', ...datepickerConfig });
        });
    });
</script>
<script>
    $(function () {
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
@endpush
