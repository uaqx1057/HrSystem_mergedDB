<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Event;
use App\Models\EventAttendee;
use App\Models\HrCandidate;
use App\Models\HrInterviewSchedule;
use App\Models\HrJobOpening;
use App\Notifications\CandidateRejected;
use App\Notifications\InterviewScheduled;
use App\Services\CandidateOnboardingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Carbon\Carbon;
use App\Models\Designation;

use App\DataTables\HrCandidateDataTable;
use App\Helper\Files;
use App\Models\HrCandidateDocument;


class HrCandidateController extends AccountBaseController
{
    private function auth()
    {
        abort_403(!in_array(user()->permission('edit_employees'), ['all', 'branch'], true));
    }

    public function index(Request $request, HrCandidateDataTable $dataTable)
    {
        $this->pageTitle = 'Recruitment candidates';
        $this->auth();
        $this->branches = Branch::orderBy('name')->get();
        $this->jobOpenings = HrJobOpening::where('company_id', user()->company_id)->orderBy('title')->get();

        return $dataTable->render('hr-candidates.index', $this->data);
    }

    public function create(Request $request)
    {
        $this->pageTitle = 'Recruitment candidates';
        $this->auth();
        $this->branches = Branch::orderBy('name')->get();
        $this->designations = Designation::allDesignations();
        $this->jobOpenings = HrJobOpening::where('company_id', user()->company_id)->orderBy('title')->get();

        return view('hr-candidates.create', $this->data);
    }

    public function store(Request $r)
    {
        $this->auth();
        $d = $r->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'mobile' => 'nullable|string|max:30',
            'branch_id' => 'nullable|exists:branches,id',
            'job_opening_id' => 'nullable|exists:hr_job_openings,id',
            'designation_id' => 'nullable|exists:designations,id',
            'notes' => 'nullable|string',
            'resume' => [
                'nullable',
                'file',
                'max:5120',
                function ($attribute, $value, $fail) {
                    $ext = strtolower($value->getClientOriginalExtension());
                    if (!in_array($ext, ['pdf', 'doc', 'docx'])) {
                        $fail('The '.$attribute.' must be a pdf, doc, or docx file.');
                    }
                },
            ],
        ]);

        $jobOpening = !empty($d['job_opening_id']) ? HrJobOpening::find($d['job_opening_id']) : null;
        $designation = !empty($d['designation_id']) ? Designation::find($d['designation_id']) : null;

        $candidate = HrCandidate::create(collect($d)->except('resume')->toArray() + [
            'company_id' => user()->company_id,
            'created_by' => user()->id,
            'status' => HrCandidate::STATUS_NEW,
            'source' => 'manual',
            'designation' => $jobOpening->title ?? $designation?->name,
            'department_id' => $jobOpening->department_id ?? null,
        ]);

        if ($r->hasFile('resume')) {
            $file = $r->file('resume');
            $stored = Files::uploadLocalOrS3($file, 'candidate-documents');
            HrCandidateDocument::create([
                'candidate_id' => $candidate->id,
                'document_type' => 'resume',
                'original_name' => $file->getClientOriginalName(),
                'stored_path' => $stored,
                'mime_type' => $file->getClientMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return redirect()->route('hr-candidates.index')->with('success', 'Candidate staged.');
    }
    public function saveOnboardingChecklist(Request $request, HrCandidate $candidate)
    {
        $this->auth();
        abort_403($candidate->company_id != user()->company_id);
        abort_if(is_null($candidate->onboardingCase), 404);

        $dateFormat = $this->company->date_format;
        $employeeType = $request->input('employee_type');

        $data = $request->validate([
            'employee_type' => 'required|in:saudi,expat',
            'iqama_no' => $employeeType === 'expat' ? 'required|string|max:50' : 'nullable|string|max:50',
            'iqama_profession' => $employeeType === 'expat' ? 'required|string|max:100' : 'nullable|string|max:100',
            'iqama_expiry_date' => $employeeType === 'expat' ? 'required|date_format:"' . $dateFormat . '"' : 'nullable|date_format:"' . $dateFormat . '"',
            'national_id' => $employeeType === 'saudi' ? 'required|string|max:50' : 'nullable|string|max:50',
            'national_id_expiry_date' => $employeeType === 'saudi' ? 'required|date_format:"' . $dateFormat . '"' : 'nullable|date_format:"' . $dateFormat . '"',
            'passport_no' => 'nullable|string|max:50',
            'passport_expiry_date' => 'nullable|date_format:"' . $dateFormat . '"',
            'probation_time' => 'nullable|string|max:100',
            'department_id' => 'nullable|exists:teams,id',
            'designation_id' => 'nullable|exists:designations,id',
            'branch_id' => 'nullable|exists:branches,id',
            'basic_salary' => 'nullable|numeric',
            'bank_name' => 'nullable|string|max:255',
            'iban_number' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'items' => 'nullable|array',
            'items.documents_verified' => 'nullable|boolean',
            'items.compensation_confirmed' => 'nullable|boolean',
            'items.bank_details_collected' => 'nullable|boolean',
            'items.contract_signed' => 'nullable|boolean',
            'items.manager_signoff' => 'nullable|boolean',
            'convert_to_employee' => 'nullable|boolean',
            'iqama_image' => 'nullable|file|max:5120',
            'national_id_image' => 'nullable|file|max:5120',
            'passport_image' => 'nullable|file|max:5120',
            'qiva_contract' => 'nullable|file|max:5120',
            'company_contract' => 'nullable|file|max:5120',
            'bank_document' => 'nullable|file|max:5120',
            'contract_document' => 'nullable|file|max:5120',
        ]);

        foreach (['iqama_expiry_date', 'national_id_expiry_date', 'passport_expiry_date'] as $field) {
            $data[$field] = !empty($data[$field])
                ? \Carbon\Carbon::createFromFormat($dateFormat, $data[$field])->format('Y-m-d')
                : null;
        }

        $candidate->fill(collect($data)->only([
            'employee_type', 'iqama_no', 'iqama_profession', 'iqama_expiry_date',
            'national_id', 'national_id_expiry_date', 'passport_no', 'passport_expiry_date',
            'probation_time', 'department_id', 'designation_id', 'branch_id', 'basic_salary',
            'bank_name', 'iban_number', 'account_number', 'swift_code',
        ])->toArray());
        $candidate->save();

        $fileMap = [
            'iqama_image' => 'iqama',
            'national_id_image' => 'national_id',
            'passport_image' => 'passport',
            'qiva_contract' => 'qiva_contract',
            'company_contract' => 'company_contract',
            'bank_document' => 'bank_account',
            'contract_document' => 'contract_signed',
        ];

        foreach ($fileMap as $field => $type) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $stored = Files::uploadLocalOrS3($file, 'candidate-documents');
                HrCandidateDocument::updateOrCreate(
                    ['candidate_id' => $candidate->id, 'document_type' => $type],
                    [
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $stored,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]
                );
            }
        }

        $items = $data['items'] ?? [];
        $candidate->onboardingCase->update([
            'documents_verified' => !empty($items['documents_verified']),
            'compensation_confirmed' => !empty($items['compensation_confirmed']),
            'bank_details_collected' => !empty($items['bank_details_collected']),
            'contract_signed' => !empty($items['contract_signed']),
            'manager_signoff' => !empty($items['manager_signoff']),
            'convert_to_employee' => !empty($data['convert_to_employee']),
        ]);

        $case = $candidate->onboardingCase->fresh();
        $allChecked = $case->documents_verified
            && $case->compensation_confirmed
            && $case->bank_details_collected
            && $case->contract_signed
            && $case->manager_signoff;

        $service = app(CandidateOnboardingService::class);
        $message = 'Onboarding checklist saved.';

        if ($candidate->status === HrCandidate::STATUS_CONVERTED && $candidate->converted_employee_id) {
            // Already an employee — every save from here on keeps both records in sync.
            $service->convertToEmployee($candidate->fresh());
            $message = 'Onboarding checklist saved and employee record updated.';
        } elseif ($case->convert_to_employee && $allChecked) {
            $service->convertToEmployee($candidate->fresh());
            $case->update(['status' => 'completed', 'completed_at' => now()]);
            $message = 'All checklist items complete — candidate converted to employee.';
        }

        return back()->with('success', $message);
    }
    public function show(HrCandidate $candidate)
    {
        $this->auth();
        abort_403($candidate->company_id != user()->company_id);
        $this->candidate = $candidate->load(['documents', 'interviews.event', 'interviews.scheduledBy', 'jobOpening', 'onboardingCase.tasks']);
        $this->interviewers = \App\Models\User::allEmployees(null, true);

        return view('hr-candidates.show', $this->data);
    }

    public function updateStatus(Request $request, HrCandidate $candidate)
    {
        $this->auth();
        abort_403($candidate->company_id != user()->company_id);
        $request->validate(['status' => 'required|in:new,applied,screening,interviewed']);
        $candidate->update(['status' => $request->status]);

        return back()->with('success', __('messages.updateSuccess'));
    }

    public function reject(Request $request, HrCandidate $candidate)
    {
        $this->auth();
        abort_403($candidate->company_id != user()->company_id);
        $data = $request->validate(['rejection_reason' => 'required|string|max:1000']);
        $candidate->update(['status' => HrCandidate::STATUS_REJECTED, 'rejection_reason' => $data['rejection_reason']]);

        if ($candidate->email) {
            Notification::route('mail', $candidate->email)->notify(new CandidateRejected($candidate));
        }

        return back()->with('success', 'Candidate rejected.');
    }

    public function approve(Request $request, HrCandidate $candidate)
    {
        $this->auth();
        abort_403($candidate->company_id != user()->company_id);
        abort_403(in_array($candidate->status, [HrCandidate::STATUS_REJECTED, HrCandidate::STATUS_CONVERTED], true));

        $data = $request->validate([
            'branch_id' => 'nullable|exists:branches,id',
            'department_id' => 'nullable|exists:teams,id',
            'designation_id' => 'nullable|exists:designations,id',
            'basic_salary' => 'nullable|numeric',
        ]);

        $candidate->fill($data);
        $candidate->status = HrCandidate::STATUS_ONBOARDING;
        $candidate->save();

        app(CandidateOnboardingService::class)->startCase($candidate);

        if ($candidate->email) {
            Notification::route('mail', $candidate->email)->notify(new \App\Notifications\CandidateApprovedOnboardingStarted($candidate));
        }

        return back()->with('success', 'Candidate approved. Pre-hire onboarding checklist started.');
    }

    public function scheduleInterview(Request $request, HrCandidate $candidate)
    {
        $this->auth();
        abort_403($candidate->company_id != user()->company_id);

        // dd($request->all());
        $data = $request->validate([
            'round' => 'required|string|max:100',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after:start_date_time',
            'where' => 'nullable|string|max:255',
            'interviewer_ids' => 'nullable|array|min:1',
            'interviewer_ids.*' => 'exists:users,id',
        ]);

        $interview = DB::transaction(function () use ($data, $candidate) {
            $event = Event::create([
                'event_name' => 'Interview: ' . $candidate->name . ' (' . $data['round'] . ')',
                'where' => $data['where'] ?? 'Office',
                'description' => 'Candidate interview for ' . ($candidate->jobOpening->title ?? $candidate->designation ?? 'an open role'),
            ]);
            $event->label_color = '#1d82f5';
            $event->repeat = 'no';
            $event->repeat_type = 'day';
            $event->send_reminder = 'yes';
            $event->remind_type = 'hour';
            $event->remind_time = 1;
            $event->start_date_time = Carbon::parse($data['start_date_time'])->format('Y-m-d H:i:s');
            $event->end_date_time = Carbon::parse($data['end_date_time'])->format('Y-m-d H:i:s');
            $event->added_by = user()->id;
            $event->save();

            if (isset($data['interviewer_ids'])) {
                foreach ($data['interviewer_ids'] as $userId) {
                    EventAttendee::firstOrCreate(['user_id' => $userId, 'event_id' => $event->id]);
                }
            }

            $interview = HrInterviewSchedule::create([
                'candidate_id' => $candidate->id,
                'event_id' => $event->id,
                'round' => $data['round'],
                'scheduled_by' => user()->id,
                'status' => 'scheduled',
            ]);

            $candidate->update(['status' => HrCandidate::STATUS_INTERVIEW_SCHEDULED]);

            return $interview;
        });

        if ($candidate->email) {
            Notification::route('mail', $candidate->email)->notify(new InterviewScheduled($candidate, $interview->event));
        }

        return back()->with('success', 'Interview scheduled.');
    }

    public function recordInterviewOutcome(Request $request, HrInterviewSchedule $interview)
    {
        $this->auth();
        abort_403($interview->candidate->company_id != user()->company_id);

        $data = $request->validate([
            'outcome' => 'required|in:pass,fail,pending',
            'feedback' => 'nullable|string|max:2000',
        ]);

        $interview->update($data + ['status' => 'completed']);
        $interview->candidate->update(['status' => HrCandidate::STATUS_INTERVIEWED]);

        return back()->with('success', 'Interview outcome recorded.');
    }

    public function handoff(HrCandidate $candidate)
    {
        $this->auth();
        abort_403($candidate->company_id !== user()->company_id || $candidate->status === 'converted');
        $candidate->update(['status' => 'handoff']);

        return redirect()->route('employees.create', ['candidate_id' => $candidate->id, 'name' => $candidate->name, 'email' => $candidate->email, 'mobile' => $candidate->mobile, 'branch_id' => $candidate->branch_id]);
    }
}

