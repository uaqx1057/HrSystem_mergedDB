<?php

namespace Tests\Unit;

use App\Models\EmployeeBankAccount;
use App\Models\EmployeeDetails;
use App\Models\Leave;
use App\Models\User;
use App\Services\HrAccess;
use Carbon\Carbon;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class HrWorkflowRegressionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('hr_access_scopes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('user_id');
            $table->string('module');
            $table->string('scope');
            $table->boolean('is_active')->default(true);
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->unsignedInteger('granted_by')->nullable();
            $table->timestamps();
        });

        Schema::create('hr_leave_approver_delegations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('manager_id');
            $table->unsignedInteger('delegate_id');
            $table->date('starts_at');
            $table->date('ends_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_head_office_branch_never_grants_global_access_without_explicit_scope(): void
    {
        $actor = new User(['company_id' => 1, 'branch_id' => 6]);
        $actor->id = 10;
        $otherBranchEmployee = new User(['company_id' => 1, 'branch_id' => 2]);
        $otherBranchEmployee->id = 20;

        $this->assertFalse(HrAccess::canAccessEmployeeBranch($actor, $otherBranchEmployee, 'leave'));

        DB::table('hr_access_scopes')->insert([
            'company_id' => 1,
            'user_id' => 10,
            'module' => 'leave',
            'scope' => 'all',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertTrue(HrAccess::canAccessEmployeeBranch($actor, $otherBranchEmployee, 'leave'));
    }

    public function test_expired_or_inactive_global_scope_does_not_grant_cross_branch_access(): void
    {
        Carbon::setTestNow('2026-07-25 12:00:00');
        $actor = new User(['company_id' => 1, 'branch_id' => 6]);
        $actor->id = 10;
        $employee = new User(['company_id' => 1, 'branch_id' => 2]);
        $employee->id = 20;

        foreach ([
            ['is_active' => true, 'ends_at' => now()->subDay()],
            ['is_active' => false, 'ends_at' => now()->addDay()],
        ] as $scope) {
            DB::table('hr_access_scopes')->insert($scope + [
                'company_id' => 1,
                'user_id' => 10,
                'module' => 'leave',
                'scope' => 'all',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->assertFalse(HrAccess::canAccessEmployeeBranch($actor, $employee, 'leave'));
    }

    public function test_active_leave_delegation_allows_only_the_named_delegate(): void
    {
        Carbon::setTestNow('2026-07-25 12:00:00');
        DB::table('hr_leave_approver_delegations')->insert([
            'company_id' => 1,
            'manager_id' => 100,
            'delegate_id' => 200,
            'starts_at' => '2026-07-20',
            'ends_at' => '2026-07-30',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $employee = new User(['company_id' => 1, 'branch_id' => 2]);
        $employee->id = 20;
        $employee->setRelation('employeeDetail', new EmployeeDetails(['reporting_to' => 100]));
        $leave = new Leave(['user_id' => 20]);
        $leave->setRelation('user', $employee);

        $delegate = new User(['company_id' => 1, 'branch_id' => 1]);
        $delegate->id = 200;
        $unrelatedUser = new User(['company_id' => 1, 'branch_id' => 1]);
        $unrelatedUser->id = 201;

        $this->assertTrue(HrAccess::canApproveLeave($delegate, $leave, 'owned'));
        $this->assertFalse(HrAccess::canApproveLeave($unrelatedUser, $leave, 'owned'));
    }

    public function test_payroll_preflight_uses_employee_bank_account_key(): void
    {
        $this->assertSame('employee_id', (new EmployeeBankAccount())->employee()->getForeignKeyName());
    }

    public function test_edit_wizard_preserves_step_saves_and_rejects_stale_edits(): void
    {
        $controller = file_get_contents(app_path('Http/Controllers/EmployeeController.php'));
        $view = file_get_contents(resource_path('views/employees/ajax/edit.blade.php'));

        $this->assertStringContainsString("in_array(\$step, [1, 2, 3, 4, 5], true)", $controller);
        $this->assertStringContainsString('lockForUpdate()', $controller);
        $this->assertStringContainsString("'last_saved_step' => \$step", $controller);
        $this->assertStringContainsString("'version' => \$editState->version + 1", $controller);
        $this->assertStringContainsString("abort_if((int) \$request->input('edit_version', 0) !== (int) \$editState->version, 409", $controller);
        $this->assertStringContainsString("'employee_type' => 'required|in:saudi,expat'", $controller);

        $this->assertSame(5, substr_count($view, 'class="btn btn-outline-primary save-step-btn"'));
        $this->assertStringContainsString('var data = new FormData();', $view);
        $this->assertStringContainsString("data.append('edit_version', \$('#edit_version').val())", $view);
        $this->assertStringContainsString("$('#edit-save-status').text('Saved step ' + step", $view);
        $this->assertStringContainsString('}, 60000);', $view);
    }

    public function test_hr_sidebar_compiles_and_contains_the_authorized_worklist_link(): void
    {
        $template = file_get_contents(resource_path('views/sections/menu.blade.php'));
        $compiled = Blade::compileString($template);

        $this->assertStringContainsString("route('hr-worklist.index')", $template);
        $this->assertStringContainsString('HR Worklist', $template);
        $this->assertStringNotContainsString('@php', $compiled);
        $this->assertStringNotContainsString('@if', $compiled);
    }

    public function test_all_new_hr_workflow_routes_are_registered_with_expected_methods(): void
    {
        $expected = [
            'employees.save_step' => 'POST',
            'hr-worklist.index' => 'GET',
            'hr-access-scopes.index' => 'GET',
            'hr-access-scopes.store' => 'POST',
            'hr-access-scopes.revoke' => 'POST',
            'hr-lifecycle.tasks.update' => 'POST',
            'hr-lifecycle.tasks.add' => 'POST',
            'hr-asset-custody.index' => 'GET',
            'hr-asset-custody.acknowledge' => 'POST',
            'hr-asset-custody.return' => 'POST',
            'hr-candidates.store' => 'POST',
            'hr-compliance.probation' => 'POST',
            'hr-compliance.certification' => 'POST',
            'hr-compliance.case.update' => 'POST',
            'hr-attendance-exceptions.store' => 'POST',
            'hr-attendance-exceptions.review' => 'POST',
            'hr-leave-delegations.store' => 'POST',
            'hr-leave-delegations.revoke' => 'POST',
        ];

        $routes = app('router')->getRoutes();
        foreach ($expected as $name => $method) {
            $route = $routes->getByName($name);
            $this->assertNotNull($route, "Missing HR route: {$name}");
            $this->assertContains($method, $route->methods(), "Wrong method for HR route: {$name}");
        }
    }
}
