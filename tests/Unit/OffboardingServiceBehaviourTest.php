<?php

namespace Tests\Unit;

use App\Models\EmployeeTermination;
use App\Models\HrOffboardingCase;
use App\Models\HrOffboardingTask;
use App\Models\User;
use App\Services\OffboardingService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OffboardingServiceBehaviourTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('activitylog.enabled', false);
        DB::purge('sqlite');
        Queue::fake();

        Schema::create('users', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('company_id')->nullable();
            $t->string('name')->nullable();
            $t->string('status')->default('active');
            $t->unsignedInteger('branch_id')->nullable();
            $t->timestamps();
        });
        Schema::create('employee_details', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->string('employee_type')->nullable();
            $t->unsignedInteger('reporting_to')->nullable();
        });
        // User::$with auto-loads these three on every query.
        Schema::create('client_details', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
        });
        Schema::create('leaves', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('user_id');
        });
        Schema::create('employee_terminations', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('offboarding_case_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('company_id');
            $t->unsignedInteger('initiated_by')->nullable();
            $t->string('exit_type')->nullable();
            $t->string('reason')->nullable();
            $t->string('terminate_reason')->nullable();
            $t->date('resignation_date')->nullable();
            $t->date('last_working_date')->nullable();
            $t->string('status')->default('pending');
            $t->timestamps();
        });
        Schema::create('hr_offboarding_cases', function (Blueprint $t) {
            $t->increments('id');
            $t->string('reference')->nullable();
            $t->unsignedInteger('company_id');
            $t->unsignedInteger('employee_id');
            $t->string('exit_type')->nullable();
            $t->string('reason')->nullable();
            $t->date('resignation_date')->nullable();
            $t->date('last_working_date')->nullable();
            $t->string('status')->default('open');
            $t->string('approval_status')->default('awaiting_approval');
            $t->unsignedInteger('initiated_by')->nullable();
            $t->unsignedInteger('approved_by')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->unsignedInteger('rejected_by')->nullable();
            $t->timestamp('rejected_at')->nullable();
            $t->text('rejected_reason')->nullable();
            $t->unsignedInteger('completed_by')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->unsignedInteger('reverted_by')->nullable();
            $t->timestamp('reverted_at')->nullable();
            $t->text('revert_reason')->nullable();
            $t->timestamp('access_revoked_at')->nullable();
            $t->timestamps();
        });
        Schema::create('hr_offboarding_tasks', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('case_id');
            $t->string('title');
            $t->string('category')->default('custom');
            $t->boolean('is_required')->default(true);
            $t->string('owner_type')->default('hr');
            $t->unsignedInteger('assigned_to')->nullable();
            $t->string('status')->default('pending');
            $t->date('due_date')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->unsignedInteger('completed_by')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('hr_offboarding_task_templates', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('company_id');
            $t->string('exit_type')->nullable();
            $t->string('employee_type')->nullable();
            $t->string('category');
            $t->string('title');
            $t->string('owner_type')->default('hr');
            $t->boolean('is_required')->default(true);
            $t->unsignedInteger('sort_order')->default(0);
            $t->timestamps();
        });
        Schema::create('hr_lifecycle_events', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('subject_user_id');
            $t->unsignedInteger('company_id');
            $t->string('event');
            $t->unsignedInteger('actor_id')->nullable();
            $t->text('meta')->nullable();
            $t->timestamp('created_at')->nullable();
        });
        Schema::create('hr_system_sync_jobs', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('employee_id');
            $t->unsignedInteger('offboarding_case_id')->nullable();
            $t->string('operation');
            $t->string('status')->default('pending');
            $t->text('systems')->nullable();
            $t->unsignedInteger('attempts')->default(0);
            $t->text('last_error')->nullable();
            $t->timestamp('processed_at')->nullable();
            $t->timestamps();
        });

        User::withoutGlobalScopes()->forceCreate(['id' => 10, 'company_id' => 1, 'name' => 'Test Employee', 'status' => 'active']);
        DB::table('employee_details')->insert(['user_id' => 10, 'employee_type' => 'expat']);
        User::withoutGlobalScopes()->forceCreate(['id' => 99, 'company_id' => 1, 'name' => 'HR Actor', 'status' => 'active']);
    }

    private function employee(): User
    {
        return User::withoutGlobalScopes()->findOrFail(10);
    }

    public function test_request_creates_one_awaiting_case_and_blocks_duplicates(): void
    {
        $svc = new OffboardingService();
        $case = $svc->request($this->employee(), 99, 'termination', ['reason' => 'Restructure', 'last_working_date' => '2026-10-31']);

        $this->assertSame('awaiting_approval', $case->approval_status);
        $this->assertSame('open', $case->status);
        $this->assertStringStartsWith('HR-', $case->reference);
        $this->assertSame(0, EmployeeTermination::count(), 'no legal termination until approval');

        $this->expectException(ValidationException::class);
        $svc->request($this->employee(), 99, 'termination', ['reason' => 'again', 'last_working_date' => '2026-10-31']);
    }

    public function test_approve_creates_exactly_one_termination_and_the_clearance_grid(): void
    {
        $svc = new OffboardingService();
        $case = $svc->request($this->employee(), 99, 'termination', ['reason' => 'x', 'last_working_date' => '2026-10-31']);

        $approved = $svc->approve($case, 99);

        $this->assertSame('approved', $approved->approval_status);
        $this->assertSame(1, EmployeeTermination::where('offboarding_case_id', $case->id)->count());
        $this->assertSame('pending', EmployeeTermination::first()->status);
        $this->assertGreaterThanOrEqual(5, HrOffboardingTask::where('case_id', $case->id)->count());
        $this->assertTrue(
            HrOffboardingTask::where('case_id', $case->id)->where('title', 'like', 'Line manager%')->exists(),
            'HR-0111 departmental grid is seeded'
        );
        Queue::assertPushed(\App\Jobs\ProcessHrSystemSyncJob::class);

        // Idempotent-ish: a second approve is rejected, not a second termination.
        $this->expectException(ValidationException::class);
        $svc->approve($case->fresh(), 99);
    }

    public function test_reject_creates_no_termination_and_cancels_the_case(): void
    {
        $svc = new OffboardingService();
        $case = $svc->request($this->employee(), 99, 'termination', ['reason' => 'x', 'last_working_date' => '2026-10-31']);

        $rejected = $svc->reject($case, 99, 'Manager retained the role');

        $this->assertSame('rejected', $rejected->approval_status);
        $this->assertSame('cancelled', $rejected->status);
        $this->assertSame('Manager retained the role', $rejected->rejected_reason);
        $this->assertSame(0, EmployeeTermination::count());
    }

    public function test_approve_adopts_a_legacy_pending_termination_instead_of_duplicating(): void
    {
        $legacy = EmployeeTermination::create([
            'user_id' => 10, 'company_id' => 1, 'exit_type' => 'termination',
            'status' => 'pending', 'last_working_date' => '2026-10-31',
        ]);

        $svc = new OffboardingService();
        $case = $svc->request($this->employee(), 99, 'termination', ['reason' => 'x', 'last_working_date' => '2026-10-31']);
        $svc->approve($case, 99);

        $this->assertSame(1, EmployeeTermination::count(), 'adopted, not duplicated');
        $this->assertSame($case->id, (int) $legacy->fresh()->offboarding_case_id);
    }

    public function test_task_completion_moves_case_to_completion_pending_then_complete_and_revert(): void
    {
        $svc = new OffboardingService();
        $case = $svc->request($this->employee(), 99, 'termination', ['reason' => 'x', 'last_working_date' => '2026-10-31']);
        $svc->approve($case, 99);

        foreach (HrOffboardingTask::where('case_id', $case->id)->where('is_required', true)->get() as $task) {
            $svc->completeTask($task, 99, true);
        }
        $this->assertSame('completion_pending', $case->fresh()->status);

        $svc->complete($case->fresh(), 99);
        $done = $case->fresh();
        $this->assertSame('completed', $done->status);
        $this->assertSame(99, (int) $done->completed_by);
        $this->assertNotNull($done->completed_at);

        $svc->revert($case->fresh(), 99, 'Reinstated');
        $this->assertSame('reverted', $case->fresh()->status);
    }
}
