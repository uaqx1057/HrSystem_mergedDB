<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Models\GlobalSetting;
use App\Models\HrCandidate;
use App\Models\HrCandidateDocument;
use App\Models\HrJobOpening;
use App\Models\User;
use App\Notifications\CandidateApplicationReceived;
use App\Notifications\NewCandidateApplicationReceived;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CareerController extends Controller
{
    public function index()
    {
        // "Open" now means status = open AND (no close date, or close date hasn't passed).
        $this->jobOpenings = HrJobOpening::where('status', 'open')
            ->where(function ($q) {
                $q->whereNull('closes_at')
                  ->orWhere('closes_at', '>=', now()->toDateString());
            })
            ->latest()
            ->get();

        return view('careers.index', $this->data);
    }

    public function show(string $slug)
    {
        $this->jobOpening = HrJobOpening::where('public_slug', $slug)
            ->where('status', 'open')
            ->firstOrFail();

        return view('careers.show', $this->data);
    }

    public function apply(?string $slug = null)
    {
        $this->jobOpening = $slug
            ? HrJobOpening::where('public_slug', $slug)->where('status', 'open')->firstOrFail()
            : null;

        // If this job has a close date that's passed, don't let people land on the form at all.
        if ($this->jobOpening && $this->isClosed($this->jobOpening)) {
            return redirect()->route('careers.show', $this->jobOpening->public_slug)
                ->with('error', 'Applications for this role closed on '.$this->jobOpening->closes_at->format('d M Y').'.');
        }

        $this->salutations = \App\Enums\Salutation::cases();
        $this->countries = countries();
        $this->maritalStatuses = \App\Enums\MaritalStatus::cases();

        return view('careers.apply', $this->data);
    }

    public function store(Request $request)
    {
        $jobOpening = $request->filled('job_opening_slug')
            ? HrJobOpening::where('public_slug', $request->job_opening_slug)->where('status', 'open')->first()
            : null;

        if ($jobOpening && $this->isClosed($jobOpening)) {
            return back()->withErrors([
                'job_opening_slug' => 'Sorry, applications for this role closed on '.$jobOpening->closes_at->format('d M Y').'.',
            ])->withInput();
        }

        $employeeType = $request->input('employee_type', 'expat');

        $rules = [
            'salutation' => 'nullable|string|max:20',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'date_of_birth' => 'nullable|date|before:-15 years',
            'image' => 'nullable|file|max:5120|mimes:png,jpg,jpeg,svg,bmp',

            'employee_type' => 'required|in:saudi,expat',
            'iqama_no' => $employeeType === 'expat' ? 'required|string|max:50' : 'nullable|string|max:50',
            'iqama_profession' => $employeeType === 'expat' ? 'required|string|max:100' : 'nullable|string|max:100',
            'iqama_expiry_date' => $employeeType === 'expat' ? 'required|date' : 'nullable|date',
            'iqama_image' => 'nullable|file|max:5120|mimes:png,jpg,jpeg,svg,bmp',
            'national_id' => $employeeType === 'saudi' ? 'required|string|max:50' : 'nullable|string|max:50',
            'national_id_expiry_date' => $employeeType === 'saudi' ? 'required|date' : 'nullable|date',
            'national_id_image' => 'nullable|file|max:5120|mimes:png,jpg,jpeg,svg,bmp',
            'passport_no' => 'nullable|string|max:50',
            'passport_expiry_date' => 'nullable|date',
            'passport_image' => 'nullable|file|max:5120|mimes:png,jpg,jpeg,svg,bmp',

            'country_id' => 'nullable|exists:countries,id',
            'mobile' => 'nullable|string|max:30',
            'gender' => 'nullable|in:male,female,others',
            'basic_salary' => 'nullable|numeric|min:0',
            'address' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:5000',

            'linkedin_username' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|max:30',
        ];

        if (global_setting()->google_recaptcha_status == 'active') {
            $rules['g-recaptcha-response'] = 'required';
        }

        $request->validate($rules);

        if ($request->filled('g-recaptcha-response') && !GlobalSetting::validateGoogleRecaptcha($request->input('g-recaptcha-response'))) {
            return back()->withErrors(['g-recaptcha-response' => __('auth.recaptchaFailed')])->withInput();
        }

        $companyId = $jobOpening->company_id ?? \App\Models\Company::where('status', 'active')->value('id');

        $candidate = HrCandidate::create([
            'company_id' => $companyId,
            'salutation' => $request->salutation,
            'name' => $request->name,
            'email' => $request->email,
            'date_of_birth' => $request->date_of_birth,
            'mobile' => $request->mobile,
            'country_id' => $request->country_id,
            'gender' => $request->gender,
            'address' => $request->address,
            'branch_id' => $jobOpening->branch_id ?? null,
            'job_opening_id' => $jobOpening->id ?? null,
            'designation' => $jobOpening->title ?? null,
            'designation_id' => $jobOpening->designation_id ?? null,
            'department_id' => $jobOpening->department_id ?? null,
            'employee_type' => $employeeType,
            'iqama_no' => $request->iqama_no,
            'iqama_profession' => $request->iqama_profession,
            'iqama_expiry_date' => $request->iqama_expiry_date,
            'national_id' => $request->national_id,
            'national_id_expiry_date' => $request->national_id_expiry_date,
            'passport_no' => $request->passport_no,
            'passport_expiry_date' => $request->passport_expiry_date,
            'basic_salary' => $request->basic_salary,
            'marital_status' => $request->marital_status,
            'linkedin_username' => $request->linkedin_username,
            'source' => 'public_application',
            'notes' => $request->notes,
            'status' => HrCandidate::STATUS_APPLIED,
        ]);

        $fileMap = [
            'image' => 'profile_picture',
            'iqama_image' => 'iqama',
            'national_id_image' => 'national_id',
            'passport_image' => 'passport',
            'resume' => 'resume',
        ];

        $fileMap = [
            'image'        => ['type' => 'profile_picture',           'dir' => 'avatar'],
            'iqama_image'        => ['type' => 'iqama',           'dir' => 'iqama'],
            'national_id_image'  => ['type' => 'national_id',     'dir' => 'national_id'],
            'passport_image'     => ['type' => 'passport',        'dir' => 'passport'],
            'resume'       => ['type' => 'resume',    'dir' => 'candidate-documents'],
        ];

        foreach ($fileMap as $field => $meta) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                if($meta['dir'] == 'avatar'){
                    $stored = Files::uploadLocalOrS3($file, $meta['dir'], 300);
                    HrCandidateDocument::create([
                        'candidate_id' => $candidate->id,
                        'document_type' => $meta['type'],
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $stored,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                } else{
                    $stored = Files::uploadLocalOrS3($file, $meta['dir']);
                    HrCandidateDocument::create([
                        'candidate_id' => $candidate->id,
                        'document_type' => $meta['type'],
                        'original_name' => $file->getClientOriginalName(),
                        'stored_path' => $stored,
                        'mime_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }
        }

        \Illuminate\Support\Facades\Notification::route('mail', $candidate->email)
            ->notify(new CandidateApplicationReceived($candidate));

        $admins = User::allAdmins($companyId);

        $resumeDocument = HrCandidateDocument::where('candidate_id', $candidate->id)
            ->where('document_type', 'resume')
            ->latest()
            ->first();

        \Illuminate\Support\Facades\Notification::send(
            $admins,
            new NewCandidateApplicationReceived($candidate, $resumeDocument)
        );

        return back()->with('success', 'Thank you — your application has been received.');
    }

    protected function isClosed(HrJobOpening $jobOpening): bool
    {
        return $jobOpening->closes_at && now()->toDateString() > $jobOpening->closes_at->toDateString();
    }
}
