@extends('layouts.app')

@section('content')
    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center form-heading-background">
            <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize">
                Add Recruitment Candidate
            </h4>

        </div>

        <div class="bg-white rounded p-4">
            <form action="{{ route('hr-candidates.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">

                    <div class="col-md-6">
                        <x-forms.text fieldId="name" fieldLabel="Name" fieldName="name" fieldRequired="true"
                            fieldPlaceholder="Name">
                        </x-forms.text>
                        @error('name')
                            <div style="color: rgb(184, 0, 0)">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <x-forms.text fieldId="email" fieldLabel="Email" fieldName="email" fieldRequired="true"
                            fieldPlaceholder="Email">
                        </x-forms.text>
                        @error('email')
                            <div style="color: rgb(184, 0, 0)">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <x-forms.text fieldId="mobile" fieldLabel="Mobile" fieldName="mobile" fieldRequired="false"
                            fieldPlaceholder="Mobile">
                        </x-forms.text>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Branch</label>
                            <select name="branch_id" class="form-control select-picker" data-live-search="true">
                                <option value="">Select Branch</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('branch_id')
                            <div style="color: rgb(184, 0, 0)">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Job Opening</label>
                            <select name="job_opening_id" class="form-control select-picker" data-live-search="true">
                                <option value="">General (no specific opening)</option>
                                @foreach ($jobOpenings as $job)
                                    <option value="{{ $job->id }}">{{ $job->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('job_opening_id')
                            <div style="color: rgb(184, 0, 0)">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Designation</label>
                            <select name="designation_id" class="form-control select-picker" data-live-search="true">
                                <option value="">Select Designation</option>
                                @foreach ($designations as $d)
                                    <option value="{{ $d->id }}">{{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('designation_id')
                            <div style="color: rgb(184, 0, 0)">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="f-14 f-w-500">Resume</label>
                            <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx">
                            <small class="text-muted">PDF or Word, max 5MB</small>
                        </div>
                        @error('resume')
                            <div style="color: rgb(184, 0, 0)">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                </div>

                <div class="d-flex justify-content-start mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-save mr-1"></i>
                        Stage Candidate
                    </button>

                </div>

            </form>
        </div>
    </div>
@endsection