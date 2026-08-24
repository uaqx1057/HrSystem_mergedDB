<?php

namespace App\Services;

use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDetails;
use App\Models\HrCandidate;
use App\Models\HrCandidateOnboardingCase;
use App\Models\HrCandidateOnboardingTask;
use App\Models\HrOnboardingCase;
use App\Models\Role;
use App\Models\User;
use App\Models\UserAuth;
use Illuminate\Support\Facades\DB;

/**
 * Runs the candidate-scoped pre-hire onboarding checklist that sits in front of the
 * existing (unchanged) post-hire HrOnboardingCase system.
 */
class CandidateOnboardingService
{
    const TASKS = [
        'Verify submitted documents (ID/Iqama/Passport/certificates)',
        'Confirm compensation, designation, branch & department assignment',
        'Confirm bank account details collected (for payroll setup)',
        'Employment contract signed (upload the signed copy)',
        'Manager / final sign-off',
    ];

    public function startCase(HrCandidate $candidate): HrCandidateOnboardingCase
    {
        $case = HrCandidateOnboardingCase::create([
            'candidate_id' => $candidate->id,
            'company_id' => $candidate->company_id,
            'status' => 'open',
            'due_date' => now()->addDays(14),
            'initiated_by' => user()->id,
        ]);

        foreach (self::TASKS as $title) {
            HrCandidateOnboardingTask::create([
                'case_id' => $case->id,
                'title' => $title,
                'owner_type' => 'hr',
                'status' => 'pending',
            ]);
        }

        return $case;
    }

    public function syncCaseCompletion(int $caseId): void
    {
        $case = HrCandidateOnboardingCase::findOrFail($caseId);
        $openTasks = HrCandidateOnboardingTask::where('case_id', $caseId)->where('status', '!=', 'completed')->exists();

        if ($openTasks) {
            return;
        }

        $case->update(['status' => 'completed', 'completed_at' => now()]);

        if ($case->convert_to_employee) {
            $this->convertToEmployee($case->candidate);
        }
    }

    /**
     * Central "all checked → convert" rule for the 5-item checklist. Returns true if a
     * conversion happened as a result of this call.
     *
     * NOTE: this still respects the "Convert this candidate to an employee" checkbox as
     * an explicit opt-in — all 5 items being checked alone won't convert unless that box
     * is also ticked. If you'd rather convert automatically the moment all 5 are checked
     * (dropping the checkbox as a gate), remove the `$case->convert_to_employee &&` below.
     */
    public function maybeConvertIfChecklistComplete(HrCandidateOnboardingCase $case): bool
    {
        $allChecked = $case->documents_verified
            && $case->compensation_confirmed
            && $case->bank_details_collected
            && $case->contract_signed
            && $case->manager_signoff;

        if (!$allChecked) {
            return false;
        }

        if ($case->status !== 'completed') {
            $case->update(['status' => 'completed', 'completed_at' => now()]);
        }

        if (!$case->convert_to_employee) {
            return false;
        }

        $this->convertToEmployee($case->candidate);

        return true;
    }

    /**
     * Creates the real employee record from a fully-collected candidate the first time
     * this is called for that candidate, and re-syncs the same fields into the existing
     * employee record on every call after that — so later edits to the candidate's
     * pre-hire data keep flowing into the employee once conversion has happened.
     */
    public function convertToEmployee(HrCandidate $candidate): User
    {
        if ($candidate->status === HrCandidate::STATUS_CONVERTED && $candidate->converted_employee_id) {
            $user = User::withoutGlobalScopes()->findOrFail($candidate->converted_employee_id);
            $this->fillEmployeeFromCandidate($user, $candidate);

            return $user;
        }

        return DB::transaction(function () use ($candidate) {
            $userAuth = UserAuth::createUserAuthCredentials($candidate->email, str()->random(32));

            $user = new User();
            $user->name = $candidate->name;
            $user->email = $candidate->email;
            $user->mobile = $candidate->mobile;
            $user->branch_id = $candidate->branch_id;
            $user->locale = 'en';
            $user->status = 'Pending Onboarding';
            $user->login = 'disable';
            $user->user_auth_id = $userAuth->id;
            $user->dark_theme = 1;
            $user->save();

            $employee = new EmployeeDetails();
            $employee->user_id = $user->id;
            $employee->employee_id = EmployeeDetails::count() + 1;
            $employee->calendar_view = 'task,events,holiday,tickets,leaves';
            $employee->joining_date = now();
            $employee->save();

            $this->fillEmployeeFromCandidate($user, $candidate, $employee);

            $employeeRole = Role::where('name', 'employee')->first();
            $user->attachRole($employeeRole);
            $user->assignUserRolePermission($employeeRole->id);

            $candidate->update([
                'status' => HrCandidate::STATUS_CONVERTED,
                'converted_employee_id' => $user->id,
            ]);

            // Same trigger EmployeeController::store() uses for a manually-added employee — the
            // existing 6-item post-hire checklist continues completely unchanged from here.
            $case = HrOnboardingCase::firstOrCreate(
                ['employee_id' => $user->id, 'status' => 'open'],
                ['company_id' => $user->company_id, 'template_name' => $candidate->employee_type ?? 'expat', 'due_date' => now()->addDays(14), 'initiated_by' => user()?->id ?? $user->id]
            );

            if ($case->wasRecentlyCreated) {
                foreach (['Verify employee profile and documents', 'Set up bank and payroll', 'Assign insurance', 'Assign required assets', 'Grant DMS/DOBS access', 'Manager confirmation'] as $title) {
                    DB::table('hr_onboarding_tasks')->insert(['case_id' => $case->id, 'title' => $title, 'owner_type' => 'hr', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
                }
            }

            return $user;
        });
    }

    /**
     * Copies every pre-hire field the candidate has collected — now including everything
     * gathered by the multistep apply form (salutation, gender, DOB, country, address,
     * marital status, LinkedIn) as well as compensation, bank account, and uploaded files —
     * from the candidate onto the given employee.
     */
    private function fillEmployeeFromCandidate(User $user, HrCandidate $candidate, ?EmployeeDetails $employee = null): void
    {
        $employee = $employee ?? $user->employeeDetail ?? EmployeeDetails::firstOrNew(['user_id' => $user->id]);

        $user->name = $candidate->name;
        $user->mobile = $candidate->mobile;
        $user->branch_id = $candidate->branch_id ?? $user->branch_id;
        $user->salutation = $candidate->salutation;
        $user->gender = $candidate->gender;
        $user->country_id = $candidate->country_id;
        // Reusing the employee form's existing (mislabeled-in-UI) slack_username field for
        // LinkedIn, matching the pattern already used elsewhere — see note below.
        // $user->slack_username = $candidate->linkedin_username;

        $profilePicture = $candidate->documents()->where('document_type', 'profile_picture')->latest()->first();
        if ($profilePicture) {
            $user->image = $profilePicture->stored_path;
        }

        $user->save();

        $employee->user_id = $user->id;
        $employee->department_id = $candidate->department_id;
        $employee->designation_id = $candidate->designation_id;
        $employee->basic_salary = $candidate->basic_salary;
        $employee->employee_type = $candidate->employee_type;
        $employee->slack_username = $candidate->linkedin_username;
        $employee->iqama_no = $candidate->iqama_no;
        $employee->iqama_profession = $candidate->iqama_profession;
        $employee->iqama_expiry_date = $candidate->iqama_expiry_date;
        $employee->national_id = $candidate->national_id;
        $employee->national_id_expiry_date = $candidate->national_id_expiry_date;
        $employee->passport_no = $candidate->passport_no;
        $employee->passport_expiry_date = $candidate->passport_expiry_date;
        $employee->probation_time = $candidate->probation_time;
        $employee->date_of_birth = $candidate->date_of_birth;
        $employee->address = $candidate->address;
        $employee->marital_status = $candidate->marital_status;
        if (empty($employee->joining_date)) {
            $employee->joining_date = now();
        }
        $employee->calendar_view = $employee->calendar_view ?: 'task,events,holiday,tickets,leaves';

        // Carry over whichever candidate-uploaded documents exist onto the matching employee file field.
        $docMap = [
            'iqama' => 'iqama_image',
            'national_id' => 'national_id_image',
            'passport' => 'passport_image',
            'qiva_contract' => 'qiva_contract',
            'company_contract' => 'company_contract',
        ];
        foreach ($docMap as $docType => $employeeField) {
            $doc = $candidate->documents()->where('document_type', $docType)->latest()->first();
            if ($doc) {
                $employee->{$employeeField} = $doc->stored_path;
            }
        }

        $employee->save();

        if ($candidate->bank_name || $candidate->iban_number) {
            EmployeeBankAccount::updateOrCreate(
                ['employee_id' => $user->id, 'is_main_account' => true],
                [
                    'bank_name' => $candidate->bank_name,
                    'iban_number' => $candidate->iban_number,
                    'account_number' => $candidate->account_number,
                    'swift_code' => $candidate->swift_code,
                    'is_main_account' => true,
                    'added_by' => user()?->id ?? $user->id,
                ]
            );
        }
    }
}
