<?php

namespace App\Http\Controllers;

use App\Helper\Files;
use App\Models\GlobalSetting;
use App\Models\HrCandidate;
use App\Models\HrCandidateDocument;
use App\Models\HrJobOpening;
use App\Notifications\CandidateApplicationReceived;
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

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'nullable|string|max:30',
            'cover_note' => 'nullable|string|max:5000',
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
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'branch_id' => $jobOpening->branch_id ?? null,
            'job_opening_id' => $jobOpening->id ?? null,
            'designation' => $jobOpening->title ?? null,
            'designation_id' => $jobOpening->designation_id ?? null,
            'department_id' => $jobOpening->department_id ?? null,
            'source' => 'public_application',
            'cover_note' => $request->cover_note,
            'status' => HrCandidate::STATUS_APPLIED,
        ]);

        if ($request->hasFile('resume')) {
            $file = $request->file('resume');
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

        \Illuminate\Support\Facades\Notification::route('mail', $candidate->email)
            ->notify(new CandidateApplicationReceived($candidate));

        return back()->with('success', 'Thank you — your application has been received.');
    }

    protected function isClosed(HrJobOpening $jobOpening): bool
    {
        return $jobOpening->closes_at && now()->toDateString() > $jobOpening->closes_at->toDateString();
    }
}