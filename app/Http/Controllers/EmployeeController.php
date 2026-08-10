<?php

namespace App\Http\Controllers;

use App\DataTables\BranchEmployeeDataTable;
use App\DataTables\EmployeeBankAccountDataTable;
use App\DataTables\EmployeesDataTable;
use App\DataTables\InsuranceDataTable;
use App\DataTables\LeaveDataTable;
use App\DataTables\OnboardingDataTable;
use App\DataTables\PendingTerminationDataTable;
use App\DataTables\TerminatedDataTable;
use App\DataTables\ProjectsDataTable;
use App\DataTables\TasksDataTable;
use App\DataTables\TicketDataTable;
use App\DataTables\TimeLogsDataTable;
use App\Enums\Salutation;
use App\Models\Company;
use App\Models\EmployeeAllowance;
use App\Models\Vehicle;
use App\Scopes\ActiveScope;
use App\Scopes\CompanyScope;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\Task;
use App\Models\Team;
use App\Models\Branch;
use App\Models\User;
use App\Services\EmployeeSystemSyncService;
use App\Helper\Files;
use App\Helper\Reply;
use App\Http\Requests\Admin\Employee\ImportProcessRequest;
use App\Http\Requests\Admin\Employee\ImportRequest;
use App\Http\Requests\Admin\Employee\StoreRequest;
use App\Http\Requests\Admin\Employee\ChangePasswordRequest;
use App\Http\Requests\Admin\Employee\UpdateRequest;
use App\Http\Requests\User\CreateInviteLinkRequest;
use App\Http\Requests\User\InviteEmailRequest;
use App\Imports\EmployeeImport;
use App\Jobs\ImportEmployeeJob;
use App\Models\Appreciation;
use App\Models\Attendance;
use App\Models\Designation;
use App\Models\EmployeeDetails;
use App\Models\HrCandidate;
use App\Models\HrEmployeeEditState;
use App\Models\HrOnboardingCase;
use App\Models\EmployeeSkill;
use App\Models\LanguageSetting;
use App\Models\Leave;
use App\Models\LeaveType;
use App\Models\Module;
use App\Models\Notification;
use App\Models\Passport;
use App\Models\ProjectTimeLog;
use App\Models\ProjectTimeLogBreak;
use App\Models\RoleUser;
use App\Models\Skill;
use App\Models\TaskboardColumn;
use App\Models\Ticket;
use App\Models\UniversalSearch;
use App\Models\UserActivity;
use App\Models\UserInvitation;
use App\Models\VisaDetail;
use App\Models\EmployeeDependant;
use App\Models\EmployeeBankAccount;
use App\Models\EmployeeTermination;
use App\Models\AssetAssignment;
use App\Models\CompanyAsset;
use App\Models\AdvanceSalary;
use App\Mail\TerminationClearanceRequestMail;
use App\Mail\TerminationCompletedMail;
use App\Services\EmployeeLifecycle;
use App\Services\HrAccess;

use App\Traits\ImportExcel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Models\UserAuth;
use Symfony\Component\Mailer\Exception\TransportException;
use App\Models\PackageUpdateNotify;
use Illuminate\Support\Facades\Hash;
use App\Mail\TerminationRevertedMail;
class EmployeeController extends AccountBaseController
{
    use ImportExcel;

    public function __construct(private BranchEmployeeDataTable $branchEmployeeDataTable)
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.employees';
        $this->middleware(function ($request, $next) {
            abort_403(!in_array('employees', $this->user->modules));

            return $next($request);
        });
    }

    public function index()
    {
        $tab = request('tab');
        $this->activeTab = $tab ?: 'employee';

        switch ($tab) {
            case 'pending-termination':
                return $this->pendingTerminationList();

            case 'onboard':
                return $this->onboardList();

            case 'terminated':
                return $this->terminatedList();

            default:
                return $this->employeesList();
        }
    }

    public function employeesList()
    {
        $viewPermission = user()->permission('view_employees');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $this->activeTab = 'employee';
        $this->employees = User::allEmployees();
        $this->skills = Skill::all();
        $this->departments = Team::all();
        $this->designations = Designation::allDesignations();
        $this->totalEmployees = count($this->employees);
        $this->roles = Role::where('name', '<>', 'client')->orderBy('id')->get();
        $this->view = 'employees.ajax.employee-list';
        $dataTable = new EmployeesDataTable();

        return $dataTable->render('employees.index', $this->data);
    }

    public function pendingTerminationList()
    {
        $viewPermission = user()->permission('view_pending_termination_employees');
        $itPermission = user()->permission('manage_it_clearance');
        $financePermission = user()->permission('manage_finance_clearance');

        abort_403(
            !in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch'])
            && !in_array($itPermission, ['all', 'branch'])
            && !in_array($financePermission, ['all', 'branch'])
        );

        $this->activeTab = 'pending-termination';
        $this->employees = User::allEmployees();
        $this->skills = Skill::all();
        $this->departments = Team::all();
        $this->designations = Designation::allDesignations();
        $this->totalEmployees = count($this->employees);
        $this->roles = Role::where('name', '<>', 'client')->orderBy('id')->get();
        $this->view = 'employees.ajax.pending-termination-list';
        $dataTable = new PendingTerminationDataTable();

        return $dataTable->render('employees.index', $this->data);
    }

    public function onboardList()
    {
        $managePermission = user()->permission('manage_onboarding_employees');
        abort_403(!in_array($managePermission, ['all', 'added', 'owned', 'both', 'branch']));

        $this->activeTab = 'onboard';
        $this->employees = User::allEmployees();
        $this->skills = Skill::all();
        $this->departments = Team::all();
        $this->designations = Designation::allDesignations();
        $this->totalEmployees = count($this->employees);
        $this->roles = Role::where('name', '<>', 'client')->orderBy('id')->get();
        $this->view = 'employees.ajax.onboard-list';
        $dataTable = new OnboardingDataTable();

        return $dataTable->render('employees.index', $this->data);
    }

    public function terminatedList()
    {
        $viewPermission = user()->permission('view_terminated_employees');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $this->activeTab = 'terminated';
        $this->employees = User::allEmployees();
        $this->skills = Skill::all();
        $this->departments = Team::all();
        $this->designations = Designation::allDesignations();
        $this->totalEmployees = count($this->employees);
        $this->roles = Role::where('name', '<>', 'client')->orderBy('id')->get();
        $this->view = 'employees.ajax.terminated-list';
        $dataTable = new TerminatedDataTable();

        return $dataTable->render('employees.index', $this->data);
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->pageTitle = __('app.addEmployee');

        $addPermission = user()->permission('add_employees');
        abort_403(!in_array($addPermission, ['all', 'added', 'branch']));

        $this->teams = Team::all();
        $this->designations = Designation::allDesignations();
        if ($addPermission == 'branch') {
            $currentBranchId = user()->branch_id;
            abort_403(is_null($currentBranchId));
            $this->branches = Branch::where('id', $currentBranchId)->get();
        } else {
            $this->branches = Branch::get();
        }

        $this->skills = Skill::all()->pluck('name')->toArray();
        $this->countries = countries();
        $this->lastEmployeeID = EmployeeDetails::count();
        $this->checkifExistEmployeeId = EmployeeDetails::select('id')->where('employee_id', ($this->lastEmployeeID + 1))->first();
        $this->employees = User::allEmployees(null, true);
        $this->languages = LanguageSetting::where('status', 'enabled')->get();
        $this->salutations = Salutation::cases();
        $this->candidate = request('candidate_id')
            ? HrCandidate::whereKey(request('candidate_id'))->where('company_id', user()->company_id)->where('status', 'handoff')->first()
            : null;

        $userRoles = user()->roles->pluck('name')->toArray();

        $this->companies = Company::where('status', 'active')->orderBy('id')->get();
        $this->vehicles = Vehicle::orderBy('id')->get();
        // dd($this->vehicles);

        if (in_array('admin', $userRoles)) {
            $this->roles = Role::where('name', '<>', 'client')->get();
        } else {
            $this->roles = Role::whereNotIn('name', ['admin', 'client'])->get();
        }

        $employee = new EmployeeDetails();

        if ($employee->getCustomFieldGroupsWithFields()) {
            $this->fields = $employee->getCustomFieldGroupsWithFields()->fields;
        }

        $this->view = 'employees.ajax.create';

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        return view('employees.create', $this->data);

    }

    public function assignRole(Request $request)
    {
        $changeEmployeeRolePermission = user()->permission('change_employee_role');

        abort_403($changeEmployeeRolePermission != 'all');

        $userId = $request->userId;
        $roleId = $request->role;
        $employeeRole = Role::where('name', 'employee')->first();

        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($userId);

        RoleUser::where('user_id', $user->id)->delete();
        $user->roles()->attach($employeeRole->id);

        if ($employeeRole->id != $roleId) {
            $user->roles()->attach($roleId);
        }

        $user->assignUserRolePermission($roleId);

        $userSession = new AppSettingController();
        $userSession->deleteSessions([$user->id]);

        return Reply::success(__('messages.roleAssigned'));
    }

    /**
     * @param StoreRequest $request
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     */
    public function store(StoreRequest $request)
    {
        $addPermission = user()->permission('add_employees');
        abort_403(!in_array($addPermission, ['all', 'added', 'branch']));

        if ($addPermission == 'branch') {
            $currentBranchId = user()->branch_id;
            abort_403(is_null($currentBranchId) || ($request->branch_id && $request->branch_id != $currentBranchId));
        }

        // WORKSUITESAAS
        $company = company();

        if (!is_null($company->employees) && $company->employees->count() >= $company->package->max_employees) {
            return Reply::error(__('superadmin.maxEmployeesLimitReached'));
        }

        DB::beginTransaction();
        try {

            $userAuth = UserAuth::createUserAuthCredentials($request->email, $request->password);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->mobile = $request->mobile;
            $user->country_id = $request->country;
            $user->salutation = $request->salutation;
            $user->country_phonecode = $request->country_phonecode;
            $user->gender = $request->gender;
            $user->locale = 'en';
            $user->status = $request->status;
            $user->user_auth_id = $userAuth->id;
            $user->branch_id = $request->branch_id ?? ($addPermission == 'branch' ? user()->branch_id : null);
            $user->dark_theme       = 1;

            if ($request->has('login')) {
                $user->login = $request->login;
            }

            // if ($request->has('email_notifications')) {
            //     $user->email_notifications = $request->email_notifications ? 1 : 0;
            // }

            if ($request->hasFile('image')) {
                Files::deleteFile($user->image, 'avatar');
                $user->image = Files::uploadLocalOrS3($request->image, 'avatar', 300);
            }

            if ($request->has('telegram_user_id')) {
                $user->telegram_user_id = $request->telegram_user_id;
            }

            $user->save();

            // $tags = json_decode($request->tags);

            // if (!empty($tags)) {
            //     foreach ($tags as $tag) {
            //         // check or store skills
            //         $skillData = Skill::firstOrCreate(['name' => $tag->value]);

            //         // Store user skills
            //         $skill = new EmployeeSkill();
            //         $skill->user_id = $user->id;
            //         $skill->skill_id = $skillData->id;
            //         $skill->save();
            //     }
            // }

            if ($user->id) {
                $employee = new EmployeeDetails();
                $employee->user_id = $user->id;
                $this->employeeData($request, $employee);
                $employee->save();
                try {
                    app(\App\Services\BioTimeService::class)->createEmployee($user, $employee);
                } catch (\Exception $e) {
                    \Log::error('BioTime sync failed: ' . $e->getMessage());
                }
                $this->saveDependants($request, $employee);
                $this->saveAllowances($request, $employee);
                $this->saveEmployeeBankAccounts($request, $employee);

                // To add custom fields data
                if ($request->custom_fields_data) {
                    $employee->updateCustomFieldData($request->custom_fields_data);
                }
            }

            $employeeRole = Role::where('name', 'employee')->first();
            $user->attachRole($employeeRole);

            if ($employeeRole->id != $request->role) {
                $otherRole = Role::where('id', $request->role)->first();
                $user->attachRole($otherRole);
            }

            if ($employeeRole->id != $request->role) {
                $user->assignUserRolePermission($otherRole->id);
            } else{
                $user->assignUserRolePermission($employeeRole->id);
            }

            $this->logSearchEntry($user->id, $user->name, 'employees.show', 'employee');

            // Commit Transaction
            DB::commit();

            if ($request->filled('candidate_id')) {
                $candidate = HrCandidate::whereKey($request->candidate_id)->where('company_id', user()->company_id)->where('status', 'handoff')->first();
                if ($candidate) {
                    $candidate->update(['status' => 'converted', 'converted_employee_id' => $user->id]);
                    $case = HrOnboardingCase::firstOrCreate(
                        ['employee_id' => $user->id, 'status' => 'open'],
                        ['company_id' => $user->company_id, 'template_name' => $employee->employee_type ?? 'expat', 'due_date' => now()->addDays(14), 'initiated_by' => user()->id]
                    );
                    if ($case->wasRecentlyCreated) {
                        foreach (['Verify employee profile and documents', 'Set up bank and payroll', 'Assign insurance', 'Assign required assets', 'Grant DMS/DOBS access', 'Manager confirmation'] as $title) {
                            DB::table('hr_onboarding_tasks')->insert(['case_id' => $case->id, 'title' => $title, 'owner_type' => 'hr', 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()]);
                        }
                    }
                }
            }

            // WORKSUITESAAS
            session()->forget('company');

        } catch (TransportException $e) {
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Please configure SMTP details to add employee. Visit Settings -> notification setting to set smtp ' . $e->getMessage(), 'smtp_error');
        } catch (\Exception $e) {
            logger($e->getMessage());
            // Rollback Transaction
            DB::rollback();

            return Reply::error('Some error occurred when inserting the data. Please try again or contact support ' . $e->getMessage());
        }


        if (request()->add_more == 'true') {
            $html = $this->create();

            return Reply::successWithData(__('messages.recordSaved'), ['html' => $html, 'add_more' => true]);
        }

        if ($request->for_onboarding == 'onboard') {
            return Reply::successWithData(
                __('messages.recordSaved'),
                [
                    'redirectUrl' => route('employees.index', ['tab' => 'onboard'])
                ]
            );
        }

        return Reply::successWithData(__('messages.recordSaved'), ['redirectUrl' => route('employees.index')]);
    }

    /**
     * @param Request $request
     * @return array
     */
    public function applyQuickAction(Request $request)
    {
        switch ($request->action_type) {
            case 'delete':
                $this->deleteRecords($request);
                // WORKSUITESAAS
                session()->forget('company');
                return Reply::success(__('messages.deleteSuccess'));
            case 'change-status':
                $company = Company::with(['package', 'employees'])->where('id', user()->company_id)->first();

                $updateIds = explode(',', str_replace('on,', '', $request->row_ids));

                if ($request->status == 'active' && !is_null($company->employees) && ($company->employees->count() + count($updateIds)) > $company->package->max_employees) {
                    return Reply::error(__('superadmin.maxEmployeesLimitReached'));
                }

                $this->changeStatus($request);

                return Reply::success(__('messages.updateSuccess'));
            default:
                return Reply::error(__('messages.selectAction'));
        }
    }

    private function deleteEmployee(User $user)
    {

        $universalSearches = UniversalSearch::where('searchable_id', $user->id)->where('module_type', 'employee')->get();

        if ($universalSearches) {
            foreach ($universalSearches as $universalSearch) {
                UniversalSearch::destroy($universalSearch->id);
            }
        }


        Notification::whereNull('read_at')
            ->where(function ($q) use ($user) {
                $q->where('data', 'like', '{"id":' . $user->id . ',%');
                $q->orWhere('data', 'like', '%,"name":' . $user->name . ',%');
                $q->orWhere('data', 'like', '%,"user_one":' . $user->id . ',%');
                $q->orWhere('data', 'like', '%,"user_id":' . $user->id . ',%');
            })->delete();

        $deleteSession = new AppSettingController();
        $deleteSession->deleteSessions([$user->id]);
        $user->delete();

    }

    protected function deleteRecords($request)
    {
        $deletePermission = user()->permission('delete_employees');
        $rowIds = explode(',', $request->row_ids);
        $query = User::withoutGlobalScope(ActiveScope::class)->whereIn('id', $rowIds);

        if ($deletePermission == 'all') {
            $users = $query->get();
        } elseif ($deletePermission == 'added') {
            $users = $query->whereHas('employeeDetail', function ($q) {
                $q->where('added_by', user()->id);
            })->get();
        } elseif ($deletePermission == 'branch' && !is_null(user()->branch_id)) {
            $users = $query->where('branch_id', user()->branch_id)->get();
        } else {
            abort_403(true);
        }

        if ($users->count() !== count($rowIds)) {
            abort_403(true);
        }

        $users->each(function ($user) {
            $this->deleteEmployee($user);
        });

    }

    protected function changeStatus($request)
    {
        $editPermission = user()->permission('edit_employees');
        $updateIds = explode(',', str_replace('on,', '', $request->row_ids));
        $query = User::withoutGlobalScope(ActiveScope::class)->whereIn('id', $updateIds);

        if ($editPermission == 'all') {
            $users = $query->get();
        } elseif ($editPermission == 'added') {
            $users = $query->whereHas('employeeDetail', function ($q) {
                $q->where('added_by', user()->id);
            })->get();
        } elseif ($editPermission == 'owned') {
            $users = $query->where('id', user()->id)->get();
        } elseif ($editPermission == 'both') {
            $users = $query->where(function ($q) {
                $q->where('id', user()->id)
                    ->orWhereHas('employeeDetail', function ($q2) {
                        $q2->where('added_by', user()->id);
                    });
            })->get();
        } elseif ($editPermission == 'branch' && !is_null(user()->branch_id)) {
            $users = $query->where('branch_id', user()->branch_id)->get();
        } else {
            abort_403(true);
        }

        if ($users->count() !== count($updateIds)) {
            abort_403(true);
        }

        User::withoutGlobalScope(ActiveScope::class)->whereIn('id', $users->pluck('id')->toArray())->update(['status' => $request->status]);
        clearCompanyValidPackageCache(user()->company_id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $this->employee = User::withoutGlobalScope(ActiveScope::class)->with('employeeDetail', 'reportingTeam')->findOrFail($id);
        $this->emailCountInCompanies = User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->where('email', $this->employee->email)
            ->whereNotNull('email')
            ->count();

        $this->editPermission = user()->permission('edit_employees');

        $userRoles = $this->employee->roles->pluck('name')->toArray();

        abort_403(
            in_array('admin', $userRoles)
            && !in_array('admin', user_roles())
            && $this->editPermission !== 'all'
        );

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->employee->employeeDetail->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->employee->id == user()->id)
            || ($this->editPermission == 'both' && ($this->employee->id == user()->id || $this->employee->employeeDetail->added_by == user()->id))
            || ($this->editPermission == 'branch' && !is_null(user()->branch_id) && $this->employee->branch_id == user()->branch_id)
        ));

        $this->pageTitle = __('app.update') . ' ' . __('app.employee');
        $this->skills = Skill::all()->pluck('name')->toArray();
        $this->teams = Team::allDepartments();
        $this->designations = Designation::allDesignations();
        if ($this->editPermission == 'branch') {
            $currentBranchId = user()->branch_id;
            $this->branches = Branch::where('id', $currentBranchId)->get();
        } else {
            $this->branches = Branch::get();
        }
        $this->countries = countries();
        $this->languages = LanguageSetting::where('status', 'enabled')->get();
        $exceptUsers = [$id];
        $this->roles = Role::where('name', '<>', 'client')->get();
        $this->userRoles = $this->employee->roles->pluck('name')->toArray();
        $this->salutations = Salutation::cases();

        $this->companies = Company::where('status', 'active')->orderBy('id')->get();
        $this->vehicles = Vehicle::orderBy('id')->get();

        /** @phpstan-ignore-next-line */
        if (count($this->employee->reportingTeam) > 0) {
            /** @phpstan-ignore-next-line */
            $exceptUsers = array_merge($this->employee->reportingTeam->pluck('user_id')->toArray(), $exceptUsers);
        }

        $this->employees = User::allEmployees($exceptUsers, true);

        $this->existingAllowances = EmployeeAllowance::where('employee_id', $this->employee->id)->get();
        $this->existingBankAccounts = EmployeeBankAccount::where('employee_id', $this->employee->id)->get();
        $this->editState = HrEmployeeEditState::firstOrCreate(
            ['company_id' => $this->employee->company_id, 'employee_id' => $this->employee->id],
            ['version' => 0]
        );

        if (!is_null($this->employee->employeeDetail)) {
            $this->employeeDetail = $this->employee->employeeDetail->withCustomFields();

            if ($this->employeeDetail->getCustomFieldGroupsWithFields()) {
                $this->fields = $this->employeeDetail->getCustomFieldGroupsWithFields()->fields;
            }
        }

        if (request()->ajax()) {
            $html = view('employees.ajax.edit', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'employees.ajax.edit';

        return view('employees.create', $this->data);

    }

    /**
     * @param UpdateRequest $request
     * @param int $id
     * @return array
     * @throws \Froiden\RestAPI\Exceptions\RelatedResourceNotFoundException
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function update(UpdateRequest $request, $id)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);
        $editState = HrEmployeeEditState::firstOrCreate(
            ['company_id' => $user->company_id, 'employee_id' => $user->id],
            ['version' => 0]
        );
        abort_if($request->has('edit_version') && (int) $request->edit_version !== (int) $editState->version, 409, 'This employee was updated by another user. Refresh before saving again.');
        $currentUser = user();
        $editPermission = $currentUser->permission('edit_employees');

        abort_403(!(
            $editPermission == 'all'
            || ($editPermission == 'added' && $user->employeeDetail->added_by == $currentUser->id)
            || ($editPermission == 'owned' && $user->id == $currentUser->id)
            || ($editPermission == 'both' && ($user->id == $currentUser->id || $user->employeeDetail->added_by == $currentUser->id))
            || ($editPermission == 'branch' && !is_null($currentUser->branch_id) && $user->branch_id == $currentUser->branch_id)
        ));

        if ($editPermission == 'branch') {
            abort_403(is_null($currentUser->branch_id) || ($request->branch_id && $request->branch_id != $currentUser->branch_id));
        }

        $user->name = $request->name;

        $user->mobile = $request->mobile;
        $user->country_id = $request->country;
        $user->salutation = $request->salutation;
        $user->country_phonecode = $request->country_phonecode;
        $user->gender = $request->gender;
        $user->locale = 'en';
        $user->branch_id = $request->branch_id ?? ($editPermission == 'branch' ? $currentUser->branch_id : null);

        if (request()->has('status')) {

            if (request()->status == 'active' && !checkCompanyCanAddMoreEmployees(user()->company_id) && $user->status != 'active') {
                return Reply::error(__('superadmin.maxEmployeesLimitReached'));
            }

            $user->status = $request->status;
            PackageUpdateNotify::where('company_id', user()->company_id)->where('user_id', $user->id)->delete();
        }

        if ($id != user()->id) {
            $user->login = $request->login;
        }

        if ($request->has('email_notifications')) {
            $user->email_notifications = $request->email_notifications;
        }

        if ($request->image_delete == 'yes') {
            Files::deleteFile($user->image, 'avatar');
            $user->image = null;
        }

        if ($request->hasFile('image')) {

            Files::deleteFile($user->image, 'avatar');
            $user->image = Files::uploadLocalOrS3($request->image, 'avatar', 300);
        }

        if ($request->has('telegram_user_id')) {
            $user->telegram_user_id = $request->telegram_user_id;
        }

        $user->save();

        cache()->forget('user_is_active_' . $user->id);

        $roleId = request()->role;

        $userRole = Role::where('id', request()->role)->first();

        if ($roleId != '' && $userRole->name != $user->user_other_role) {

            $employeeRole = Role::where('name', 'employee')->first();

            $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($user->id);

            RoleUser::where('user_id', $user->id)->delete();
            $user->roles()->attach($employeeRole->id);

            if ($employeeRole->id != $roleId) {
                $user->roles()->attach($roleId);
            }

            $user->assignUserRolePermission($roleId);

            $userSession = new AppSettingController();
            $userSession->deleteSessions([$user->id]);
        }

        // $tags = json_decode($request->tags);

        // if (!empty($tags)) {
        //     EmployeeSkill::where('user_id', $user->id)->delete();

        //     foreach ($tags as $tag) {
        //         // Check or store skills
        //         $skillData = Skill::firstOrCreate(['name' => $tag->value]);

        //         // Store user skills
        //         $skill = new EmployeeSkill();
        //         $skill->user_id = $user->id;
        //         $skill->skill_id = $skillData->id;
        //         $skill->save();
        //     }
        // }

        $employee = EmployeeDetails::where('user_id', '=', $user->id)->first();

        if (empty($employee)) {
            $employee = new EmployeeDetails();
            $employee->user_id = $user->id;
        }

        $this->employeeData($request, $employee);

        // $employee->last_date = null;
        $employee->basic_salary = $request->basic_salary;
        $employee->vehicle_allocation = $request->vehicle_allocation;

        // if ($request->last_date != '') {
        //     $employee->last_date = Carbon::createFromFormat($this->company->date_format, $request->last_date)->format('Y-m-d');
        // }

        $employee->save();
        $editState->update([
            'last_saved_step' => 5,
            'version' => $editState->version + 1,
            'last_saved_by' => $currentUser->id,
            'last_saved_at' => now(),
        ]);
        try {
            app(\App\Services\BioTimeService::class)->createEmployee($user, $employee);
        } catch (\Exception $e) {
            \Log::error('BioTime sync failed: ' . $e->getMessage());
        }
        $this->saveDependants($request, $employee);
        $this->saveAllowances($request, $employee);
        $this->saveEmployeeBankAccounts($request, $employee);

        // To add custom fields data
        if ($request->custom_fields_data) {
            $employee->updateCustomFieldData($request->custom_fields_data);
        }

        if (user()->id == $user->id) {
            session()->forget('user');
        }

        if ($request->has('for-onboarding')) {
            return Reply::successWithData(
                __('messages.updateSuccess'),
                [
                    'redirectUrl' => route('employees.index', ['tab' => 'onboard'])
                ]
            );
        }
        return Reply::successWithData(__('messages.updateSuccess'), ['redirectUrl' => route('employees.index')]);
    }

    /**
     * Persist one section of the edit-employee wizard without requiring the
     * remaining sections to be valid. This endpoint is not used by create.
     */
    public function saveStep(Request $request, $id)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);
        $this->authorizeEmployeeStepSave($user, $request);

        $step = (int) $request->input('step');
        abort_unless(in_array($step, [1, 2, 3, 4, 5], true), 422);

        $employee = EmployeeDetails::firstOrNew(['user_id' => $user->id]);
        $request->validate($this->employeeStepRules($step, $employee));

        $nextVersion = null;
        DB::transaction(function () use ($request, $step, $user, $employee, &$nextVersion) {
            $editState = HrEmployeeEditState::where('company_id', $user->company_id)
                ->where('employee_id', $user->id)->lockForUpdate()
                ->firstOrCreate(['company_id' => $user->company_id, 'employee_id' => $user->id], ['version' => 0]);
            abort_if((int) $request->input('edit_version', 0) !== (int) $editState->version, 409, 'This employee was updated by another user. Refresh before saving again.');
            if ($step === 1) {
                $user->fill($request->only(['name', 'salutation', 'branch_id']));
                if ($request->hasFile('image')) {
                    Files::deleteFile($user->image, 'avatar');
                    $user->image = Files::uploadLocalOrS3($request->image, 'avatar', 300);
                }
                $employee->employee_id = $request->employee_id;
                $employee->department_id = $request->department;
                $employee->designation_id = $request->designation;
            }

            if ($step === 2) {
                foreach (['employee_type', 'iqama_no', 'iqama_profession', 'national_id', 'probation_time', 'passport_no', 'sponsor_kafala'] as $field) {
                    if ($request->has($field)) $employee->{$field} = $request->input($field);
                }
                $this->saveEmployeeStepDates($request, $employee, ['national_id_expiry_date', 'iqama_expiry_date', 'passport_expiry_date', 'sponsorship_transfer_date']);
                $this->saveEmployeeStepFiles($request, $employee, ['national_id_image' => 'national_id', 'iqama_image' => 'iqama', 'passport_image' => 'passport', 'qiva_contract' => 'contracts', 'company_contract' => 'contracts']);
            }

            if ($step === 3) {
                $user->fill($request->only(['mobile', 'country_id', 'country_phonecode', 'gender', 'locale']));
                foreach (['address', 'reporting_to', 'basic_salary', 'vehicle_allocation'] as $field) {
                    if ($request->has($field)) $employee->{$field} = $request->input($field);
                }
                $this->saveEmployeeStepDates($request, $employee, ['date_of_birth', 'joining_date']);
            }

            if ($step === 4) {
                foreach (['login', 'email_notifications', 'telegram_user_id', 'status'] as $field) {
                    if ($request->has($field) && ($field !== 'login' || $user->id !== user()->id)) $user->{$field} = $request->input($field);
                }
                foreach (['slack_username', 'employment_type', 'marital_status', 'no_of_dependants'] as $field) {
                    if ($request->has($field)) $employee->{$field} = $request->input($field);
                }
                $this->saveEmployeeStepDates($request, $employee, ['probation_end_date', 'notice_period_start_date', 'notice_period_end_date', 'internship_end_date', 'contract_end_date']);
            }

            $user->save();
            $employee->save();

            if ($step === 4 && $request->boolean('dependants_present')) $this->saveDependants($request, $employee);
            if ($step === 5 && $request->boolean('allowances_present')) $this->saveAllowances($request, $employee);
            if ($step === 5 && $request->boolean('bank_accounts_present')) $this->saveEmployeeBankAccounts($request, $employee);
            $editState->update(['last_saved_step' => $step, 'version' => $editState->version + 1, 'last_saved_by' => user()->id, 'last_saved_at' => now()]);
            $nextVersion = $editState->version;
        });

        app(EmployeeSystemSyncService::class)->syncEmployeeProfileToLinkedSystems($user->fresh());

        return Reply::successWithData(__('messages.updateSuccess'), ['editVersion' => $nextVersion]);
    }

    private function authorizeEmployeeStepSave(User $employee, Request $request): void
    {
        $currentUser = user();
        $permission = $currentUser->permission('edit_employees');
        abort_403(
            $employee->hasRole('admin')
            && !in_array('admin', user_roles())
            && $permission !== 'all'
        );
        abort_403(!(
            $permission === 'all'
            || ($permission === 'added' && optional($employee->employeeDetail)->added_by === user()->id)
            || ($permission === 'owned' && $employee->id === user()->id)
            || ($permission === 'both' && ($employee->id === user()->id || optional($employee->employeeDetail)->added_by === user()->id))
            || ($permission === 'branch' && HrAccess::canAccessEmployeeBranch($currentUser, $employee, 'employees'))
        ));
        abort_403($permission === 'branch' && !HrAccess::hasAllBranchAccess($currentUser, 'employees') && $request->has('branch_id') && (int) $request->branch_id !== (int) $currentUser->branch_id);
    }

    private function employeeStepRules(int $step, EmployeeDetails $employee): array
    {
        $date = 'nullable|date_format:"' . $this->company->date_format . '"';

        return match ($step) {
            1 => ['employee_id' => 'required|max:50|unique:employee_details,employee_id,' . $employee->id . ',id,company_id,' . company()->id, 'name' => 'required|max:50', 'department' => 'required', 'designation' => 'required', 'branch_id' => 'nullable|exists:branches,id', 'image' => 'nullable|image'],
            2 => array_merge(['employee_type' => 'required|in:saudi,expat', 'national_id_expiry_date' => $date, 'iqama_expiry_date' => $date, 'passport_expiry_date' => $date, 'sponsorship_transfer_date' => $date], request()->input('employee_type') === 'saudi' ? ['national_id' => 'required|string|max:50', 'national_id_expiry_date' => 'required|date_format:"' . $this->company->date_format . '"'] : ['iqama_no' => 'required|string|max:50', 'iqama_profession' => 'required|string|max:100', 'iqama_expiry_date' => 'required|date_format:"' . $this->company->date_format . '"']),
            3 => ['date_of_birth' => $date, 'joining_date' => $date, 'basic_salary' => 'nullable|numeric'],
            4 => ['probation_end_date' => $date, 'notice_period_start_date' => $date, 'notice_period_end_date' => $date, 'internship_end_date' => $date, 'contract_end_date' => $date, 'dependants.*.name' => 'required_with:dependants.*.relation', 'dependants.*.relation' => 'required_with:dependants.*.name', 'dependants.*.date_of_birth' => $date],
            5 => [
                'allowances.*.name' => 'required_with:allowances.*.amount',
                'allowances.*.amount' => 'required_with:allowances.*.name|numeric|min:0',
                'bank_accounts.*.bank_name' => 'required_with:bank_accounts.*.iban_number,bank_accounts.*.account_number,bank_accounts.*.swift_code|string|max:255',
                'bank_accounts.*.iban_number' => 'required_with:bank_accounts.*.bank_name,bank_accounts.*.account_number,bank_accounts.*.swift_code|string|max:255',
                'bank_accounts.*.account_number' => 'nullable|string|max:255',
                'bank_accounts.*.swift_code' => 'nullable|string|max:255',
                'bank_accounts.*.is_main_account' => 'nullable|boolean',
            ],
        };
    }

    private function saveEmployeeStepDates(Request $request, EmployeeDetails $employee, array $fields): void
    {
        foreach ($fields as $field) {
            if ($request->has($field)) $employee->{$field} = filled($request->input($field)) ? Carbon::createFromFormat($this->company->date_format, $request->input($field))->format('Y-m-d') : null;
        }
    }

    private function saveEmployeeStepFiles(Request $request, EmployeeDetails $employee, array $fields): void
    {
        foreach ($fields as $field => $directory) {
            if ($request->hasFile($field)) {
                Files::deleteFile($employee->{$field}, $directory);
                $employee->{$field} = Files::uploadLocalOrS3($request->file($field), $directory);
            }
        }
    }

    /**
     * @param int $id
     * @return array
     */
    public function destroy($id)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);
        $this->deletePermission = user()->permission('delete_employees');

        abort_403(!(
            $this->deletePermission == 'all'
            || ($this->deletePermission == 'added' && $user->employeeDetail->added_by == user()->id)
            || ($this->deletePermission == 'branch' && !is_null(user()->branch_id) && $user->branch_id == user()->branch_id)
        ));


        if ($user->hasRole('admin') && !in_array('admin', user_roles())) {
            return Reply::error(__('messages.adminCannotDelete'));
        }

        PackageUpdateNotify::where('company_id', $user->company_id)->where('user_id', $user->id)->delete();

        $this->deleteEmployee($user);

        // WORKSUITESAAS
        $employeeDetail = EmployeeDetails::where('employee_id', $id)->first();
        EmployeeDependant::where('employee_id', $id)->delete();
        EmployeeAllowance::where('employee_id', $id)->delete();
        session()->forget('company');

        return Reply::success(__('messages.deleteSuccess'));

    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->viewPermission = user()->permission('view_employees');

        $this->employee = User::with([
            'employeeDetail',
            'employeeDetail.designation',
            'employeeDetail.department',
            'appreciations',
            'appreciations.award',
            'appreciations.award.awardIcon',
            'employeeDetail.reportingTo',
            'country',
            'emergencyContacts',
            'reportingTeam' => function ($query) {
                $query->join('users', 'users.id', '=', 'employee_details.user_id');
                $query->where('users.status', '=', 'active');
            },
            'reportingTeam.user',
            'leaveTypes',
            'leaveTypes.leaveType',
            'appreciationsGrouped',
            'appreciationsGrouped.award',
            'appreciationsGrouped.award.awardIcon'
        ])
            ->withoutGlobalScope(ActiveScope::class)
            ->withOut('clientDetails', 'role')
            ->withCount('member', 'agents', 'openTasks')
            ->findOrFail($id);

        $this->employeeLanguage = LanguageSetting::where('language_code', $this->employee->locale)->first();
        $this->employeeLifecycle = EmployeeLifecycle::summary($this->employee);
        $this->employeeInsurances = \App\Models\Insurance::where('employee_id', $id)
            ->orderBy('expiry_date', 'desc')
            ->get();
        // Leave balance history
        $joiningDate = $this->employee->employeeDetail->joining_date
            ? \Carbon\Carbon::parse($this->employee->employeeDetail->joining_date)
            : null;

        $leaveHistory = [];

        if ($joiningDate) {
            $now = now($this->company->timezone);
            $cursor = $joiningDate->copy()->startOfMonth();

            while ($cursor->lte($now)) {
                $monthLabel = $cursor->translatedFormat('M Y');

                $fullTaken = \App\Models\Leave::where('user_id', $id)
                    ->where('status', 'approved')
                    ->whereNull('half_day_type')
                    ->whereMonth('leave_date', $cursor->month)
                    ->whereYear('leave_date', $cursor->year)
                    ->count();

                $halfTaken = \App\Models\Leave::where('user_id', $id)
                    ->where('status', 'approved')
                    ->whereNotNull('half_day_type')
                    ->whereMonth('leave_date', $cursor->month)
                    ->whereYear('leave_date', $cursor->year)
                    ->count();

                $taken = $fullTaken + ($halfTaken / 2);

                $leaveHistory[] = [
                    'month' => $monthLabel,
                    'earned' => 2.5,
                    'taken' => $taken,
                    'balance' => 2.5 - $taken,
                ];

                $cursor->addMonth();
            }

            // Homeland badge: 1 ticket per completed year
            $yearsCompleted = (int) $joiningDate->diffInYears(now($this->company->timezone));
            $this->homelandTickets = $yearsCompleted;
        }
        $leaveHistory = array_reverse($leaveHistory);
        $this->leaveHistory = $leaveHistory;
        $this->joiningDate = $joiningDate;

        $this->ticketHistory = $this->getAirTicketStats($id);

        if (!$this->employee->hasRole('employee')) {
            abort(404);
        }

        if ($this->employee->status == 'deactive' && !in_array('admin', user_roles())) {
            abort(403);
        }

        abort_403(in_array('client', user_roles()));

        $tab = request('tab');

        if (
            $this->viewPermission == 'all'
            || ($this->viewPermission == 'added' && $this->employee->employeeDetail->added_by == user()->id)
            || ($this->viewPermission == 'owned' && $this->employee->employeeDetail->user_id == user()->id)
            || ($this->viewPermission == 'both' && ($this->employee->employeeDetail->user_id == user()->id || $this->employee->employeeDetail->added_by == user()->id))
            || ($this->viewPermission == 'branch' && $this->employee->branch_id == user()->branch_id)
        ) {

            if ($tab == '') {  // Works for profile

                $this->fromDate = now()->timezone($this->company->timezone)->startOfMonth()->toDateString();
                $this->toDate = now()->timezone($this->company->timezone)->endOfMonth()->toDateString();

                $this->lateAttendance = Attendance::whereBetween(DB::raw('DATE(`clock_in_time`)'), [$this->fromDate, $this->toDate])
                    ->where('late', 'yes')->where('user_id', $id)->count();

                $this->leavesTaken = Leave::selectRaw('count(*) as count, SUM(if(duration="half day", 1, 0)) AS halfday')
                    ->where('user_id', $id)
                    ->where('status', 'approved')
                    ->whereBetween(DB::raw('DATE(`leave_date`)'), [$this->fromDate, $this->toDate])
                    ->first();

                $this->leavesTaken = (!is_null($this->leavesTaken)) ? $this->leavesTaken->count - ($this->leavesTaken->halfday * 0.5) : 0;

                $this->taskChart = $this->taskChartData($id);
                $this->ticketChart = $this->ticketChartData($id);

                if (!is_null($this->employee->employeeDetail)) {
                    $this->employeeDetail = $this->employee->employeeDetail->withCustomFields();

                    $customFields = $this->employeeDetail->getCustomFieldGroupsWithFields();

                    if (!empty($customFields)) {
                        $this->fields = $customFields->fields;
                    }
                }

                $taskBoardColumn = TaskboardColumn::completeColumn();

                $this->taskCompleted = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
                    ->where('task_users.user_id', $id)
                    ->where('tasks.board_column_id', $taskBoardColumn->id)
                    ->count();

                $hoursLogged = ProjectTimeLog::where('user_id', $id)->sum('total_minutes');
                $breakMinutes = ProjectTimeLogBreak::userBreakMinutes($id);

                $timeLog = intdiv($hoursLogged - $breakMinutes, 60);

                $this->hoursLogged = $timeLog;
            }

        }

        $this->pageTitle = $this->employee->name;
        $viewDocumentPermission = user()->permission('view_documents');
        $viewImmigrationPermission = user()->permission('view_immigration');

        switch ($tab) {
            case 'system-access':
                abort_403(!in_array('admin', user_roles()));
                $this->systemAccessDms  = \App\Models\EmployeeSystemAccess::where('employee_id', $id)->where('system', 'dms')->first();
                $this->systemAccessDobs = \App\Models\EmployeeSystemAccess::where('employee_id', $id)->where('system', 'dobs')->first();
                $this->dmsRoles  = DB::table('roles')->where('name', '!=', 'client')->pluck('name', 'id');
                $this->dobsRoles = ['FleetManager', 'FinanceManager', 'HR', 'OpsManager', 'OpsSupervisor', 'SuperAdmin'];
                $this->view = 'employees.ajax.system-access';
                break;
            case 'tickets':
                return $this->tickets();
            case 'projects':
                return $this->projects();
            case 'insurance':
                return $this->insurance($id);
            case 'employee-bank-account':
                return $this->employeeBankAccounts($id);

            case 'tasks':
                return $this->tasks();
            case 'leaves':
                return $this->leaves();
            case 'timelogs':
                return $this->timelogs();
            case 'documents':
                abort_403(($viewDocumentPermission == 'none'));
                $this->view = 'employees.ajax.documents';
                break;
            case 'company-assets':
                $viewCompanyAssetPermission = user()->permission('view_company_assets');
                $assignCompanyAssetPermission = user()->permission('assign_company_asset_to_employee');
                $viewAssignmentPermission = user()->permission('view_assign_company_assets_to_employee');
                $uploadSignaturePermission = user()->permission('upload_signature_assign_company_assets_to_employee');

                abort_403(
                    !in_array($viewCompanyAssetPermission, ['all', 'added', 'owned', 'both', 'branch'])
                    && !in_array($assignCompanyAssetPermission, ['all', 'added', 'branch'])
                    && !in_array($viewAssignmentPermission, ['all', 'added', 'owned', 'both', 'branch'])
                    && !in_array($uploadSignaturePermission, ['all', 'added', 'owned', 'both', 'branch'])
                );

                $this->employeeAssetAssignments = AssetAssignment::with([
                    'asset.branch',
                    'asset.department',
                ])
                    ->where('employee_id', $id)
                    ->orderByDesc('id')
                    ->get();
                $this->companyAssetEmployeeId = $id;
                $this->view = 'employees.ajax.company-assets';
                break;
            case 'emergency-contacts':
                $this->view = 'employees.ajax.emergency-contacts';
                break;
            case 'appreciation':
                $viewAppreciationPermission = user()->permission('view_appreciation');
                abort_403(!in_array($viewAppreciationPermission, ['all', 'added', 'owned', 'both']));

                $this->appreciations = $this->appreciation($this->employee->id);
                $this->view = 'employees.ajax.appreciations';
                break;
            case 'leaves-quota':
                $this->leaveQuota($id);
                $this->leavesTakenByUser = Leave::byUserCount($this->employee);
                $this->leaveTypes = LeaveType::byUser($this->employee);
                $this->employeeLeavesQuotas = $this->employee->leaveTypes;
                $this->employeeLeavesQuota = clone $this->employeeLeavesQuotas;

                $totalLeaves = 0;

                foreach ($this->leaveTypes as $key => $leavesCount) {
                    $leavesCountCheck = $leavesCount->leaveTypeCodition($leavesCount, $this->userRole);

                    if ($leavesCountCheck && $this->employeeLeavesQuotas[$key]->leave_type_id == $leavesCount->id) {
                        $totalLeaves += $this->employeeLeavesQuotas[$key]->no_of_leaves;
                    }
                }

                $this->allowedLeaves = $totalLeaves;
                $this->view = 'employees.ajax.leaves_quota';
                break;
            case 'shifts':
                abort_403(user()->permission('view_shift_roster') != 'all' || !in_array('attendance', user_modules()));
                $this->view = 'employees.ajax.shifts';
                break;
            case 'permissions':
                abort_403(user()->permission('manage_role_permission_setting') != 'all');

                $this->modulesData = Module::with('permissions')->withCount('customPermissions')->get();
                $this->view = 'employees.ajax.permissions';
                break;

            case 'activity':
                $this->activities = UserActivity::where('user_id', $id)->orderBy('id', 'desc')->get();
                $this->view = 'employees.ajax.activity';
                break;

            case 'immigration':
                abort_403($viewImmigrationPermission == 'none');
                $this->passport = Passport::with('country')->where('user_id', $this->employee->id)->first();
                $this->visa = VisaDetail::with('country')->where('user_id', $this->employee->id)->get();
                $this->view = 'employees.ajax.immigration';
                break;

            case 'link-branch':
                $linkBranchPermission = $this->employee->permission('link_to_branch');
                abort_403(!(in_array($linkBranchPermission, ['all', 'owned', 'both'])));
                return $this->branchEmployee();

            default:
                $this->view = 'employees.ajax.profile';
                break;
        }

        if (request()->ajax()) {
            $html = view($this->view, $this->data)->render();

            return Reply::dataOnly(['views' => $this->view, 'status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->activeTab = $tab ?: 'profile';

        return view('employees.show', $this->data);
    }

    public function assignCompanyAsset($id)
    {
        $assignPermission = user()->permission('assign_company_asset_to_employee');
        abort_403(!in_array($assignPermission, ['all', 'added', 'branch']));

        $employee = User::allEmployees()->where('id', $id)->first();
        abort_if(is_null($employee), 404);

        $assetsQuery = CompanyAsset::with(['branch', 'serials'])
            ->where('available_qty', '>', 0)
            ->orderBy('name');

        if ($assignPermission == 'added') {
            $assetsQuery->where('added_by', user()->id);
        }

        if ($assignPermission == 'branch' && !hr_has_all_branch_access('company_assets')) {
            $assetsQuery->where('branch_id', user()->branch_id);
        }

        $assets = $assetsQuery->get();
        $assignedSerials = AssetAssignment::whereIn('company_asset_id', $assets->pluck('id'))
            ->get()
            ->groupBy('company_asset_id')
            ->map(function ($rows) {
                return $rows->pluck('serial_no')->all();
            });

        $this->assignableAssets = $assets->map(function ($asset) use ($assignedSerials) {
            $usedSerials = $assignedSerials->get($asset->id, []);
            $serials = $asset->serials
                ->whereNotIn('serial_no', $usedSerials)
                ->pluck('serial_no')
                ->values()
                ->all();

            return [
                'id' => $asset->id,
                'name' => $asset->name,
                'catalog' => $asset->catalog,
                'branch' => $asset->branch?->name,
                'serials' => $serials,
            ];
        })->filter(function ($asset) {
            return !empty($asset['serials']);
        })->values();

        $this->companyAssetEmployeeId = $id;
        $this->employee = $employee;

        if (request()->ajax()) {
            $html = view('employees.ajax.assign-company-asset', $this->data)->render();

            return Reply::dataOnly([
                'status' => 'success',
                'html' => $html,
                'title' => __('app.menu.assignCompanyAsset'),
            ]);
        }

        $this->view = 'employees.ajax.assign-company-asset';

        return view('employees.show', $this->data);
    }

    public function getAirTicketStats(int $employeeId): array
    {
        $employee = User::withoutGlobalScopes()->with(['employeeDetails', 'airTicket'])
            ->findOrFail($employeeId);

        $joiningDate = $employee->employeeDetails?->joining_date;

        if (!$joiningDate) {
            return [
                'total_earned'    => 0,
                'total_used'      => 0,
                'total_remaining' => 0,
            ];
        }

        $totalEarned    = (int) \Carbon\Carbon::parse($joiningDate)->diffInYears(now());
        $totalUsed      = $employee->airTicket->count();
        $totalRemaining = max(0, $totalEarned - $totalUsed); // ✅ max(0) prevents negative value

        return [
            'total_earned'    => $totalEarned,
            'total_used'      => $totalUsed,
            'total_remaining' => $totalRemaining,
        ];
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function taskChartData($id)
    {
        $taskStatus = TaskboardColumn::all();
        $data['labels'] = $taskStatus->pluck('column_name');
        $data['colors'] = $taskStatus->pluck('label_color');
        $data['values'] = [];

        foreach ($taskStatus as $label) {
            $data['values'][] = Task::join('task_users', 'task_users.task_id', '=', 'tasks.id')
                ->where('task_users.user_id', $id)->where('tasks.board_column_id', $label->id)->count();
        }

        return $data;
    }

    /**
     * XXXXXXXXXXX
     *
     * @return array
     */
    public function ticketChartData($id)
    {
        $labels = ['open', 'pending', 'resolved', 'closed'];
        $data['labels'] = [__('app.open'), __('app.pending'), __('app.resolved'), __('app.closed')];
        $data['colors'] = ['#D30000', '#FCBD01', '#2CB100', '#1d82f5'];
        $data['values'] = [];

        foreach ($labels as $label) {
            $data['values'][] = Ticket::where('agent_id', $id)->where('status', $label)->count();
        }

        return $data;
    }

    public function byDepartment($id, $permission = null)
    {
        $users = User::join('employee_details', 'employee_details.user_id', '=', 'users.id');

        if ($id != 0) {
            $users = $users->where('employee_details.department_id', $id);
        }

        if ($permission === 'branch' && !hr_has_all_branch_access('employees')) {
            abort_403(is_null(user()->branch_id));
            $users = $users->where('users.branch_id', user()->branch_id);
        }

        $users = $users->select('users.*')->get();

        $options = '';

        foreach ($users as $item) {
            $options .= '<option  data-content="<div class=\'d-inline-block mr-1\'><img class=\'taskEmployeeImg rounded-circle\' src=' . $item->image_url . ' ></div>  ' . $item->name . '" value="' . $item->id . '"> ' . $item->name . ' </option>';
        }

        return Reply::dataOnly(['status' => 'success', 'data' => $options]);
    }

    public function appreciation($employeeID)
    {
        $viewAppreciationPermission = user()->permission('view_appreciation');

        if ($viewAppreciationPermission == 'none') {
            return [];
        }

        $appreciations = Appreciation::with(['award', 'award.awardIcon', 'awardTo'])->select('id', 'award_id', 'award_to', 'award_date', 'image', 'summary', 'created_at');
        $appreciations->join('awards', 'awards.id', '=', 'appreciations.award_id');

        if ($viewAppreciationPermission == 'added') {
            $appreciations->where('appreciations.added_by', user()->id);
        }

        if ($viewAppreciationPermission == 'owned') {
            $appreciations->where('appreciations.award_to', user()->id);
        }

        if ($viewAppreciationPermission == 'both') {
            $appreciations->where(function ($q) {
                $q->where('appreciations.added_by', '=', user()->id);

                $q->orWhere('appreciations.award_to', '=', user()->id);
            });
        }

        $appreciations = $appreciations->select('appreciations.*')->where('appreciations.award_to', $employeeID)->get();

        return $appreciations;
    }

    public function projects()
    {

        $viewPermission = user()->permission('view_employee_projects');
        abort_403(!in_array($viewPermission, ['all']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'employees.ajax.projects';

        $dataTable = new ProjectsDataTable();

        return $dataTable->render('employees.show', $this->data);

    }

    public function insurance($employeeId)
    {

        $viewPermission = user()->permission('view_employees');
        abort_403(!in_array($viewPermission, ['all']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'employees.ajax.insurance';

        $dataTable = new InsuranceDataTable($employeeId, 0);

        return $dataTable->render('employees.show', $this->data);

    }

    public function employeeBankAccounts($employeeId)
    {
        $viewPermission = user()->permission('view_employee_bank_account');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'employees.ajax.employee-bank-account';

        $dataTable = new EmployeeBankAccountDataTable($employeeId);

        return $dataTable->render('employees.show', $this->data);
    }

    public function tickets()
    {
        $viewPermission = user()->permission('view_tickets');
        abort_403(!(in_array($viewPermission, ['all']) && in_array('tickets', user_modules())));
        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->tickets = Ticket::all();
        $this->view = 'employees.ajax.tickets';
        $dataTable = new TicketDataTable();

        return $dataTable->render('employees.show', $this->data);

    }

    public function branchEmployee()
    {
        $tab = request('tab');
        $this->activeTab = $tab ?: 'link-branch';
        $this->view = 'employees.branches.ajax.index';
        $dataTable = $this->branchEmployeeDataTable->with('employee_id', $this->employee->id)->with('employee', $this->employee);

        return $dataTable->render('employees.show', $this->data);

    }

    public function tasks()
    {
        $viewPermission = user()->permission('view_employee_tasks');
        abort_403(!in_array($viewPermission, ['all']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->taskBoardStatus = TaskboardColumn::all();
        $this->view = 'employees.ajax.tasks';

        $dataTable = new TasksDataTable();

        return $dataTable->render('employees.show', $this->data);
    }

    public function leaves()
    {

        $viewPermission = user()->permission('view_leaves_taken');
        abort_403(!in_array($viewPermission, ['all']));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->leaveTypes = LeaveType::all();
        $this->view = 'employees.ajax.leaves';

        $dataTable = new LeaveDataTable();

        return $dataTable->render('employees.show', $this->data);
    }

    public function timelogs()
    {

        $viewPermission = user()->permission('view_employee_timelogs');
        abort_403(!(in_array($viewPermission, ['all']) && in_array('timelogs', user_modules())));

        $tab = request('tab');
        $this->activeTab = $tab ?: 'profile';
        $this->view = 'employees.ajax.timelogs';

        $dataTable = new TimeLogsDataTable();

        return $dataTable->render('employees.show', $this->data);
    }

    /**
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function inviteMember()
    {
        abort_403(!in_array(user()->permission('add_employees'), ['all']));

        return view('employees.ajax.invite_member', $this->data);

    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function sendInvite(InviteEmailRequest $request)
    {
        $emails = json_decode($request->email);

        if (!empty($emails)) {
            foreach ($emails as $email) {
                $invite = new UserInvitation();
                $invite->user_id = user()->id;
                $invite->email = $email->value;
                $invite->message = $request->message;
                $invite->invitation_type = 'email';
                $invite->invitation_code = sha1(time() . user()->id);
                $invite->save();
            }
        }

        return Reply::success(__('messages.inviteEmailSuccess'));
    }

    /**
     * XXXXXXXXXXX
     *
     * @return \Illuminate\Http\Response
     */
    public function createLink(CreateInviteLinkRequest $request)
    {
        $invite = new UserInvitation();
        $invite->user_id = user()->id;
        $invite->invitation_type = 'link';
        $invite->invitation_code = sha1(time() . user()->id);
        $invite->email_restriction = (($request->allow_email == 'selected') ? $request->email_domain : null);
        $invite->save();

        return Reply::successWithData(__('messages.inviteLinkSuccess'), ['link' => route('invitation', $invite->invitation_code)]);
    }

    /**
     * @param mixed $request
     * @param mixed $employee
     */
    public function employeeData($request, $employee): void
    {
        $employee->employee_id = $request->employee_id;
        $employee->address = $request->address;
        $employee->slack_username = $request->slack_username;
        $employee->employee_type = $request->employee_type === 'saudi' ? 'saudi' : 'expat';
        $employee->iqama_no = $request->iqama_no;
        $employee->iqama_designation = $request->iqama_designation;
        $employee->iqama_profession = $request->iqama_profession;

        $employee->national_id = $request->national_id;
        $employee->national_id_expiry_date = $request->national_id_expiry_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->national_id_expiry_date)->format('Y-m-d')
            : null;

        if ($request->hasFile('national_id_image')) {
            Files::deleteFile($employee->national_id_image, 'national_id');
            $employee->national_id_image = Files::uploadLocalOrS3($request->national_id_image, 'national_id');
        }

        $employee->basic_salary = $request->basic_salary;
        $employee->vehicle_allocation = $request->vehicle_allocation;
        if($request->vehicle_allocation == 'yes'){
            $employee->vehicle_id = $request->vehicle_id;
        } else{
            $employee->vehicle_id = null;
        }

        // Onboarding data
        if ($request->has('for-onboarding')) {
            $employee->verify_employee_profile = $request->verify_employee_profile ?? false;
            $employee->setup_bank_and_payroll = $request->setup_bank_and_payroll ?? false;
            // $employee->assign_insurance = $request->assign_insurance ?? false;
            // $employee->assign_required_assets = $request->assign_required_assets ?? false;
            $employee->manager_confirmation = $request->manager_confirmation ?? false;
        }

        // --- NEW FIELDS (Text) ---
        $employee->probation_time = $request->probation_time;

        // Handle Iqama Image
        if ($request->hasFile('iqama_image')) {
            Files::deleteFile($employee->iqama_image, 'iqama'); // Delete old file
            $employee->iqama_image = Files::uploadLocalOrS3($request->iqama_image, 'iqama');
        }

            // --- NEW CONTRACT FILES ---
        if ($request->hasFile('qiva_contract')) {
            Files::deleteFile($employee->qiva_contract, 'contracts'); // Delete old file
            $employee->qiva_contract = Files::uploadLocalOrS3($request->qiva_contract, 'contracts');
        }

        if ($request->hasFile('company_contract')) {
            Files::deleteFile($employee->company_contract, 'contracts'); // Delete old file
            $employee->company_contract = Files::uploadLocalOrS3($request->company_contract, 'contracts');
        }

        $employee->iqama_expiry_date = $request->iqama_expiry_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->iqama_expiry_date)->format('Y-m-d')
            : null;
        $employee->passport_no = $request->passport_no;

        if ($request->hasFile('passport_image')) {
            Files::deleteFile($employee->passport_image, 'passport');
            $employee->passport_image = Files::uploadLocalOrS3($request->passport_image, 'passport');
        }

        $employee->passport_expiry_date = $request->passport_expiry_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->passport_expiry_date)->format('Y-m-d')
            : null;
        $employee->sponsor_kafala = $request->sponsor_kafala;
        $employee->sponsorship_transfer_date = $request->sponsorship_transfer_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->sponsorship_transfer_date)->format('Y-m-d')
            : null;
        $employee->department_id = $request->department;
        $employee->designation_id = $request->designation;
        $employee->reporting_to = $request->reporting_to;
        $employee->joining_date = \Carbon\Carbon::createFromFormat($this->company->date_format, $request->joining_date)->format('Y-m-d');
        $employee->date_of_birth = $request->date_of_birth
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->date_of_birth)->format('Y-m-d')
            : null;
        $employee->calendar_view = 'task,events,holiday,tickets,leaves';
        $employee->probation_end_date = $request->probation_end_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->probation_end_date)->format('Y-m-d')
            : null;
        $employee->notice_period_start_date = $request->notice_period_start_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->notice_period_start_date)->format('Y-m-d')
            : null;
        $employee->notice_period_end_date = $request->notice_period_end_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->notice_period_end_date)->format('Y-m-d')
            : null;
        $employee->marital_status = $request->marital_status;
        $employee->no_of_dependants = $request->no_of_dependants;
        $employee->employment_type = $request->employment_type;
        $employee->internship_end_date = $request->internship_end_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->internship_end_date)->format('Y-m-d')
            : null;
        $employee->contract_end_date = $request->contract_end_date
            ? \Carbon\Carbon::createFromFormat($this->company->date_format, $request->contract_end_date)->format('Y-m-d')
            : null;
    }
    protected function saveAllowances($request, $employee)
    {
        if ($request->has('allowances') && is_array($request->allowances)) {
            // Delete removed allowances
            $submittedIds = collect($request->allowances)
                ->pluck('id')
                ->filter()
                ->toArray();

            EmployeeAllowance::where('employee_id', $employee->user_id)
                ->whereNotIn('id', $submittedIds)
                ->delete();

            foreach ($request->allowances as $allowance) {
                if (!empty($allowance['name']) && $allowance['amount'] !== null) {
                    EmployeeAllowance::updateOrCreate(
                        [
                            'id' => $allowance['id'] ?? null,
                            'employee_id' => $employee->user_id,
                        ],
                        [
                            'name' => $allowance['name'],
                            'amount' => $allowance['amount'],
                        ]
                    );
                }
            }
        } else {
            // All allowances removed, delete all
            EmployeeAllowance::where('employee_id', $employee->user_id)->delete();
        }
    }

    protected function saveEmployeeBankAccounts($request, EmployeeDetails $employee): void
    {
        if (!($request->has('bank_accounts') && is_array($request->bank_accounts))) {
            EmployeeBankAccount::where('employee_id', $employee->user_id)->delete();

            return;
        }

        $submittedIds = collect($request->bank_accounts)->pluck('id')->filter()->toArray();
        EmployeeBankAccount::where('employee_id', $employee->user_id)
            ->whereNotIn('id', $submittedIds)
            ->delete();

        $mainAccountId = null;

        foreach ($request->bank_accounts as $bankAccount) {
            if (empty($bankAccount['bank_name']) && empty($bankAccount['iban_number']) && empty($bankAccount['account_number']) && empty($bankAccount['swift_code'])) {
                continue;
            }

            $account = EmployeeBankAccount::updateOrCreate(
                [
                    'id' => $bankAccount['id'] ?? null,
                    'employee_id' => $employee->user_id,
                ],
                [
                    'bank_name' => $bankAccount['bank_name'] ?? null,
                    'iban_number' => $bankAccount['iban_number'] ?? null,
                    'account_number' => $bankAccount['account_number'] ?? null,
                    'swift_code' => $bankAccount['swift_code'] ?? null,
                    'is_main_account' => !empty($bankAccount['is_main_account']),
                    'added_by' => user()->id,
                ]
            );

            if (!empty($bankAccount['is_main_account'])) {
                $mainAccountId = $account->id;
            }
        }

        if ($mainAccountId) {
            EmployeeBankAccount::where('employee_id', $employee->user_id)
                ->where('id', '!=', $mainAccountId)
                ->update(['is_main_account' => false]);
        }
    }

    private function saveDependants($request, EmployeeDetails $employee): void
    {
        $submitted = $request->input('dependants', []);
        $submittedIds = [];
        foreach ($submitted as $data) {
            $id = $data['id'] ?? null;
            $dep = $id
                ? EmployeeDependant::find($id) ?? new EmployeeDependant()
                : new EmployeeDependant();
            $dep->employee_id = $employee->user_id;
            $dep->name = $data['name'];
            $dep->iqama_no = $data['iqama_no'] ?? null;
            $dep->relation = $data['relation'];
            $dep->date_of_birth = !empty($data['date_of_birth'])
                ? \Carbon\Carbon::createFromFormat($this->company->date_format, $data['date_of_birth'])->format('Y-m-d')
                : null;
            $dep->save();

            $submittedIds[] = $dep->id;
        }
        EmployeeDependant::where('employee_id', $employee->user_id)
            ->whereNotIn('id', $submittedIds)
            ->delete();
    }

    public function importMember()
    {
        $this->pageTitle = __('app.importExcel') . ' ' . __('app.employee');

        $addPermission = user()->permission('add_employees');
        abort_403(!in_array($addPermission, ['all', 'added']));


        if (request()->ajax()) {
            $html = view('employees.ajax.import', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'employees.ajax.import';

        return view('employees.create', $this->data);
    }

    public function importStore(ImportRequest $request)
    {
        $this->importFileProcess($request, EmployeeImport::class);

        $view = view('employees.ajax.import_progress', $this->data)->render();

        return Reply::successWithData(__('messages.importUploadSuccess'), ['view' => $view]);
    }

    public function importProcess(ImportProcessRequest $request)
    {
        $batch = $this->importJobProcess($request, EmployeeImport::class, ImportEmployeeJob::class);

        return Reply::successWithData(__('messages.importProcessStart'), ['batch' => $batch]);
    }

    public function leaveQuota($id)
    {
        $roles = User::with('roles')->findOrFail($id);
        $userRole = [];

        $userRoles = $roles->roles->count() > 1 ? $roles->roles->where('name', '!=', 'employee') : $roles->roles;

        foreach ($userRoles as $role) {
            $userRole[] = $role->id;
        }

        $this->userRole = $userRole;
    }

    public function changePassword(ChangePasswordRequest $request)
    {
        $employee = User::withoutGlobalScope(ActiveScope::class)->findOrFail($request->integer('employee_id'));
        $editPermission = user()->permission('edit_employees');

        abort_403(!(
            $editPermission == 'all'
            || ($editPermission == 'added' && $employee->employeeDetail?->added_by == user()->id)
            || ($editPermission == 'owned' && $employee->id == user()->id)
            || ($editPermission == 'both' && ($employee->id == user()->id || $employee->employeeDetail?->added_by == user()->id))
            || ($editPermission == 'branch' && !is_null(user()->branch_id) && $employee->branch_id == user()->branch_id)
        ));

        $userAuth = UserAuth::findOrFail($employee->user_auth_id);
        $userAuth->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => null,
        ])->save();

        (new AppSettingController())->deleteSessions([$employee->id]);

        return Reply::successWithData(__('messages.passwordChanged'), ['html' => '', 'add_more' => true]);

    }
    public function grantSystemAccess(Request $request, $id)
    {
        abort_403(!in_array('admin', user_roles()));

        $request->validate([
            'system' => 'required|in:dms,dobs',
            'role'   => 'required|string',
        ]);

        $hrUser = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);
        $system = $request->system;
        $this->validateSystemRole($system, $request->role);

        DB::transaction(function () use ($hrUser, $system, $request, $id) {

            if ($system === 'dms') {
                // DMS users table uses role_id + is_login_allowed
                $roleId = DB::table('roles')->where('name', $request->role)->value('id');

                $dmsUser = DB::table('users')
                    ->where('email', $hrUser->email)
                    ->whereNotNull('role_id')
                    ->first();

                if ($dmsUser) {
                    DB::table('users')->where('id', $dmsUser->id)->update([
                        'role_id'          => $roleId,
                        'is_login_allowed' => 1,
                        'updated_at'       => now(),
                    ]);
                    $systemUserId = $dmsUser->id;
                } else {
                    $systemUserId = DB::table('users')->insertGetId([
                        'name'             => $hrUser->name,
                        'email'            => $hrUser->email,
                        'password'         => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                        'role_id'          => $roleId,
                        'is_login_allowed' => 1,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ]);
                }
            } else {
                // DOBS uses a separate DB (dobsykjq_dms) and table dobs_user
                // Role must be lowercase to match DOBS role_redirects map
                $dobsRole = strtolower($request->role);
                $dobsDb   = DB::connection('mysql'); // dobs_user is in the same shared DB

                $dobsUser = $dobsDb->table('dobs_user')->where('email', $hrUser->email)->first();

                if ($dobsUser) {
                    $dobsDb->table('dobs_user')->where('id', $dobsUser->id)->update([
                        'role' => $dobsRole,
                    ]);
                    $systemUserId = $dobsUser->id;
                } else {
                    // username is required and unique in dobs_user; use email as username
                    $username = $hrUser->email;
                    // If email is taken as username already (different user), append id
                    if ($dobsDb->table('dobs_user')->where('username', $username)->exists()) {
                        $username = $hrUser->email . '_' . $hrUser->id;
                    }
                    $systemUserId = $dobsDb->table('dobs_user')->insertGetId([
                        'name'     => $hrUser->name,
                        'email'    => $hrUser->email,
                        'username' => $username,
                        'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                        'role'     => $dobsRole,
                    ]);
                }
            }

            \App\Models\EmployeeSystemAccess::updateOrCreate(
                ['employee_id' => $id, 'system' => $system],
                [
                    'system_user_id' => $systemUserId,
                    'role'           => $request->role,
                    'is_active'      => true,
                    'provisioned_at' => now(),
                ]
            );
        });

        app(EmployeeSystemSyncService::class)->syncEmployeeProfileToLinkedSystems($hrUser);

        $systemUrl = $system === 'dms'
            ? config('services.sso.dms_url')
            : config('services.sso.dobs_url');

        try {
            $hrUser->notify(new \App\Notifications\SystemAccessGranted($system, $request->role, $systemUrl));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('SystemAccessGranted email failed for ' . $hrUser->email . ': ' . $e->getMessage());
        }

        return Reply::success('Access granted successfully.');
    }

    public function revokeSystemAccess(Request $request, $id)
    {
        abort_403(!in_array('admin', user_roles()));

        $request->validate(['system' => 'required|in:dms,dobs']);

        $access = \App\Models\EmployeeSystemAccess::where('employee_id', $id)
            ->where('system', $request->system)
            ->firstOrFail();

        $access->update(['is_active' => false]);

        if ($request->system === 'dms') {
            DB::table('users')->where('id', $access->system_user_id)
                ->update(['is_login_allowed' => 0]);
        }
        // DOBS has no active flag; blocking SSO is enough (is_active=false prevents token generation)

        // Invalidate any pending SSO tokens for this employee+system
        DB::table('sso_tokens')
            ->where('employee_id', $id)
            ->where('target_system', $request->system)
            ->whereNull('used_at')
            ->update(['used_at' => now()]);

        return Reply::success('Access revoked.');
    }

    public function updateSystemRole(Request $request, $id)
    {
        abort_403(!in_array('admin', user_roles()));

        $request->validate([
            'system' => 'required|in:dms,dobs',
            'role'   => 'required|string',
        ]);

        $access = \App\Models\EmployeeSystemAccess::where('employee_id', $id)
            ->where('system', $request->system)
            ->firstOrFail();
        $this->validateSystemRole($request->system, $request->role);

        if ($request->system === 'dms') {
            $roleId = DB::table('roles')->where('name', $request->role)->value('id');
            DB::table('users')->where('id', $access->system_user_id)
                ->update(['role_id' => $roleId]);
        } else {
            DB::table('dobs_user')
                ->where('id', $access->system_user_id)
                ->update(['role' => strtolower($request->role)]);
        }

        $access->update(['role' => $request->role]);

        return Reply::success('Role updated.');
    }
    public function ssoLaunch($system)
    {
        $hrUser = user();

        $access = \App\Models\EmployeeSystemAccess::where('employee_id', $hrUser->id)
            ->where('system', $system)
            ->where('is_active', true)
            ->firstOrFail();

        $tokenStr = bin2hex(random_bytes(32)); // 64-char cryptographically secure hex

        // Use UTC_TIMESTAMP() to avoid PHP↔MySQL timezone conversion on TIMESTAMP columns
        DB::table('sso_tokens')->insert([
            'token'          => $tokenStr,
            'employee_id'    => $hrUser->id,
            'target_system'  => $system,
            'system_user_id' => $access->system_user_id,
            'expires_at'     => DB::raw('UTC_TIMESTAMP() + INTERVAL 15 MINUTE'),
            'created_at'     => DB::raw('UTC_TIMESTAMP()'),
            'updated_at'     => DB::raw('UTC_TIMESTAMP()'),
        ]);

        $urls = [
            'dms'  => config('services.sso.dms_url', 'https://dms.speedlogi.sa'),
            'dobs' => config('services.sso.dobs_url', 'https://dobs.speedlogi.sa'),
        ];

        return redirect($urls[$system] . '/sso/login?token=' . $tokenStr);
    }

    private function validateSystemRole(string $system, string $role): void
    {
        $valid = $system === 'dms'
            ? DB::table('roles')->where('name', $role)->where('name', '!=', 'client')->exists()
            : in_array($role, ['FleetManager', 'FinanceManager', 'HR', 'OpsManager', 'OpsSupervisor', 'SuperAdmin'], true);

        abort_unless($valid, 422, 'The selected system role is not allowed.');
    }

    public function terminatePending(Request $request, $id)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);
        $this->terminatePermission = user()->permission('manage_termination_employees');

        abort_403(!(
            $this->terminatePermission == 'all'
            || ($this->terminatePermission == 'branch' && user()->branch_id == 6)
            || ($this->terminatePermission == 'branch' && !is_null(user()->branch_id) && $user->branch_id == user()->branch_id)
        ));

        if ($user->status == 'Terminated') {
            return Reply::error(__('messages.assignmentAlreadyProcessed'));
        }

        $existingPending = EmployeeTermination::where('user_id', $user->id)
            ->where('status', EmployeeTermination::STATUS_PENDING)
            ->exists();

        if ($existingPending) {
            return Reply::error(__('messages.assignmentAlreadyProcessed'));
        }

        $termination = EmployeeTermination::create([
            'user_id' => $user->id,
            'company_id' => $user->company_id,
            'initiated_by' => user()->id,
            'terminate_reason' => $request->terminate_reason,
            'status' => EmployeeTermination::STATUS_PENDING,
        ]);

        $itUsers = User::usersWithPermission('manage_it_clearance', $user->company_id);
        $financeUsers = User::usersWithPermission('manage_finance_clearance', $user->company_id);

        foreach ($itUsers as $itUser) {
            try {
                Mail::to($itUser->email)->send(new TerminationClearanceRequestMail($termination, 'IT'));
            } catch (\Exception $e) {
                Log::error('Failed to send IT clearance request email: ' . $e->getMessage());
            }
        }

        foreach ($financeUsers as $financeUser) {
            try {
                Mail::to($financeUser->email)->send(new TerminationClearanceRequestMail($termination, 'Finance'));
            } catch (\Exception $e) {
                Log::error('Failed to send Finance clearance request email: ' . $e->getMessage());
            }
        }

        return Reply::success(__('messages.pendingTermination'));

    }

    public function showTerminatePending($id)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);

        $viewPermission = user()->permission('view_pending_termination_employees');
        $itPermission = user()->permission('manage_it_clearance');
        $financePermission = user()->permission('manage_finance_clearance');

        abort_403(!(
            in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch'])
            || in_array($itPermission, ['all', 'branch'])
            || in_array($financePermission, ['all', 'branch'])
        ));

        $this->employee = User::with([
            'employeeDetail',
            'employeeDetail.designation',
            'employeeDetail.department',
            'appreciations',
            'appreciations.award',
            'appreciations.award.awardIcon',
            'employeeDetail.reportingTo',
            'country',
            'emergencyContacts',
            'reportingTeam' => function ($query) {
                $query->join('users', 'users.id', '=', 'employee_details.user_id');
                $query->where('users.status', '=', 'active');
            },
            'reportingTeam.user',
            'leaveTypes',
            'leaveTypes.leaveType',
            'appreciationsGrouped',
            'appreciationsGrouped.award',
            'appreciationsGrouped.award.awardIcon'
        ])
            ->withoutGlobalScope(ActiveScope::class)
            ->withOut('clientDetails', 'role')
            ->withCount('member', 'agents', 'openTasks')
            ->findOrFail($id);

        $this->termination = EmployeeTermination::where('user_id', $id)
            ->latest('id')
            ->first();

        $this->assignedAssets = AssetAssignment::with('asset')
            ->where('employee_id', $id)
            ->where('status', 'Assigned')
            ->get();

        $this->pendingAdvances = AdvanceSalary::where('employee_id', $id)
            ->where('status', 'approved')
            ->whereColumn('deducted_amount', '<', 'advance_salary')
            ->get();

        $this->canManageTermination = $this->canManageTermination($user);
        $this->canManageItClearance = in_array($itPermission, ['all', 'branch']);
        $this->canManageFinanceClearance = in_array($financePermission, ['all', 'branch']);

        if (request()->ajax()) {
            $html = view('employees.ajax.show-pending-terminate', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'employees.ajax.show-pending-terminate';
        return view('employees.create', $this->data);

    }

    private function canManageTermination(User $user)
    {
        $permission = user()->permission('manage_termination_employees');

        return $permission == 'all'
            || ($permission == 'branch' && user()->branch_id == 6)
            || ($permission == 'branch' && !is_null(user()->branch_id) && $user->branch_id == user()->branch_id);
    }

    public function completeTermination(Request $request, $id)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);

        abort_403(!$this->canManageTermination($user));

        $termination = EmployeeTermination::where('user_id', $id)
            ->latest('id')
            ->first();

        if (!$termination) {
            return Reply::error(__('messages.employeeNotFound'));
        }

        $request->validate([
            'notice_period_start_date' => 'required|date',
            'notice_period_end_date' => 'required|date|after_or_equal:notice_period_start_date',
        ]);

        if (!$termination->isFullyCleared()) {
            return Reply::error('Both IT and Finance clearance must be issued before completing termination.');
        }

        if (!$user->employeeDetail) {
            return Reply::error('Employee detail record not found.');
        }

        $user->employeeDetail->notice_period_start_date = Carbon::parse($request->notice_period_start_date)->format('Y-m-d');
        $user->employeeDetail->notice_period_end_date = Carbon::parse($request->notice_period_end_date)->format('Y-m-d');
        $user->employeeDetail->last_date = now();
        $user->employeeDetail->save();

        $user->status = 'deactive';
        $user->save();

        $termination->status = EmployeeTermination::STATUS_COMPLETED;
        $termination->completed_by = user()->id;
        $termination->completed_at = now();
        $termination->save();

        // Revoke DMS/DOBS login and push the final notice-period dates to any linked accounts.
        app(EmployeeSystemSyncService::class)->syncEmployeeProfileToLinkedSystems($user->fresh());

        $recipients = collect([$user])
            ->merge(User::usersWithPermission('manage_it_clearance', $user->company_id))
            ->merge(User::usersWithPermission('manage_finance_clearance', $user->company_id))
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationCompletedMail($termination));
            } catch (\Exception $e) {
                Log::error('Failed to send termination completed email: ' . $e->getMessage());
            }
        }

        return Reply::success(__('messages.updateSuccess'));
    }

    public function revertTermination(Request $request, $id)
    {
        $user = User::withoutGlobalScope(ActiveScope::class)->findOrFail($id);

        abort_403(!$this->canManageTermination($user));

        $termination = EmployeeTermination::where('user_id', $id)
            ->whereIn('status', [EmployeeTermination::STATUS_PENDING, EmployeeTermination::STATUS_COMPLETED])
            ->latest('id')
            ->first();

        if (!$termination) {
            return Reply::error('No active or completed termination found to revert.');
        }

        $request->validate([
            'revert_reason' => 'nullable|string|max:1000',
        ]);

        $wasCompleted = $termination->status === EmployeeTermination::STATUS_COMPLETED;

        DB::transaction(function () use ($termination, $user, $request, $wasCompleted) {
            $termination->status = EmployeeTermination::STATUS_REVERTED;
            $termination->reverted_by = user()->id;
            $termination->reverted_at = now();
            $termination->revert_reason = $request->revert_reason;
            $termination->save();

            if ($wasCompleted) {
                $user->status = 'active';
                $user->save();

                if ($user->employeeDetail) {
                    $user->employeeDetail->last_date = null;
                    $user->employeeDetail->notice_period_start_date = null;
                    $user->employeeDetail->notice_period_end_date = null;
                    $user->employeeDetail->save();
                }
            }
        });

        $recipients = collect([$user])
            ->merge(User::usersWithPermission('manage_it_clearance', $user->company_id))
            ->merge(User::usersWithPermission('manage_finance_clearance', $user->company_id))
            ->merge(User::usersWithPermission('manage_termination_employees', $user->company_id))
            ->unique('email');

        foreach ($recipients as $recipient) {
            try {
                Mail::to($recipient->email)->send(new TerminationRevertedMail($termination, $wasCompleted));
            } catch (\Exception $e) {
                Log::error('Failed to send termination reverted email: ' . $e->getMessage());
            }
        }

        return Reply::success($wasCompleted
            ? 'Employee reactivated successfully.'
            : 'Termination reverted successfully.');
    }

    public function showTerminated($id)
    {
        $viewPermission = user()->permission('view_terminated_employees');
        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both', 'branch']));

        $this->employee = User::with([
            'employeeDetail',
            'employeeDetail.designation',
            'employeeDetail.department',
            'country',
        ])
            ->withoutGlobalScope(ActiveScope::class)
            ->withOut('clientDetails', 'role')
            ->findOrFail($id);

        $this->termination = EmployeeTermination::where('user_id', $id)
            ->latest('id')
            ->first();

        if (request()->ajax()) {
            $html = view('employees.ajax.show-terminated', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'employees.ajax.show-terminated';
        return view('employees.create', $this->data);
    }

    public function showOnboard($id)
    {
        $permission = user()->permission('manage_onboarding_employees');
        abort_403(!in_array($permission, ['all', 'added', 'owned', 'both', 'branch']));

        $this->employee = User::with([
            'employeeDetail',
            'employeeDetail.designation',
            'employeeDetail.department',
            'country',
        ])
            ->withoutGlobalScope(ActiveScope::class)
            ->withOut('clientDetails', 'role')
            ->findOrFail($id);

        // abort_403($this->employee->status !== 'onboarding');

        if ($permission == 'added') {
            abort_403(optional($this->employee->employeeDetail)->added_by !== user()->id);
        }

        if ($permission == 'owned') {
            abort_403($this->employee->id !== user()->id);
        }

        if ($permission == 'both') {
            abort_403(!(
                $this->employee->id === user()->id
                || optional($this->employee->employeeDetail)->added_by === user()->id
            ));
        }

        if ($permission == 'branch') {
            abort_403(is_null(user()->branch_id) || $this->employee->branch_id !== user()->branch_id);
        }

        $this->pageTitle = __('app.menu.onboard');

        if (request()->ajax()) {
            $html = view('employees.ajax.show-onboard', $this->data)->render();
            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->employeeLifecycle = EmployeeLifecycle::summary($this->employee);

        $this->view = 'employees.ajax.show-onboard';
        return view('employees.create', $this->data);
    }

    public function editOnboarding($id)
    {

        $this->employee = User::withoutGlobalScope(ActiveScope::class)->with('employeeDetail', 'reportingTeam')->findOrFail($id);
        $this->emailCountInCompanies = User::withoutGlobalScopes([ActiveScope::class, CompanyScope::class])
            ->where('email', $this->employee->email)
            ->whereNotNull('email')
            ->count();

        $this->editPermission = user()->permission('edit_employees');

        $userRoles = $this->employee->roles->pluck('name')->toArray();

        abort_403(
            in_array('admin', $userRoles)
            && !in_array('admin', user_roles())
            && $this->editPermission !== 'all'
        );

        abort_403(!($this->editPermission == 'all'
            || ($this->editPermission == 'added' && $this->employee->employeeDetail->added_by == user()->id)
            || ($this->editPermission == 'owned' && $this->employee->id == user()->id)
            || ($this->editPermission == 'both' && ($this->employee->id == user()->id || $this->employee->employeeDetail->added_by == user()->id))
            || ($this->editPermission == 'branch' && !is_null(user()->branch_id) && $this->employee->branch_id == user()->branch_id)
        ));

        $this->pageTitle = __('app.update') . ' ' . __('app.employee');
        $this->skills = Skill::all()->pluck('name')->toArray();
        $this->teams = Team::allDepartments();
        $this->designations = Designation::allDesignations();
        if ($this->editPermission == 'branch') {
            $currentBranchId = user()->branch_id;
            $this->branches = Branch::where('id', $currentBranchId)->get();
        } else {
            $this->branches = Branch::get();
        }
        $this->countries = countries();
        $this->languages = LanguageSetting::where('status', 'enabled')->get();
        $exceptUsers = [$id];
        $this->roles = Role::where('name', '<>', 'client')->get();
        $this->userRoles = $this->employee->roles->pluck('name')->toArray();
        $this->salutations = Salutation::cases();

        $this->companies = Company::where('status', 'active')->orderBy('id')->get();
        $this->vehicles = Vehicle::orderBy('id')->get();

        /** @phpstan-ignore-next-line */
        if (count($this->employee->reportingTeam) > 0) {
            /** @phpstan-ignore-next-line */
            $exceptUsers = array_merge($this->employee->reportingTeam->pluck('user_id')->toArray(), $exceptUsers);
        }

        $this->employees = User::allEmployees($exceptUsers, true);

        $this->existingAllowances = EmployeeAllowance::where('employee_id', $this->employee->id)->get();
        $this->existingBankAccounts = EmployeeBankAccount::where('employee_id', $this->employee->id)->get();
        $this->editState = HrEmployeeEditState::firstOrCreate(
            ['company_id' => $this->employee->company_id, 'employee_id' => $this->employee->id],
            ['version' => 0]
        );

        if (!is_null($this->employee->employeeDetail)) {
            $this->employeeDetail = $this->employee->employeeDetail->withCustomFields();

            if ($this->employeeDetail->getCustomFieldGroupsWithFields()) {
                $this->fields = $this->employeeDetail->getCustomFieldGroupsWithFields()->fields;
            }
        }

        if (request()->ajax()) {
            $html = view('employees.ajax.edit-onboarding', $this->data)->render();

            return Reply::dataOnly(['status' => 'success', 'html' => $html, 'title' => $this->pageTitle]);
        }

        $this->view = 'employees.ajax.edit-onboarding';

        return view('employees.create', $this->data);

    }

}
