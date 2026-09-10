<?php

namespace Tests\Unit;

use App\Models\AssetRecoveryAllocation;
use App\Models\AssetReturnForm;
use App\Models\EmployeeAssessLoss;
use App\Models\EmployeeTermination;
use App\Models\HrSettlementForm;
use App\Services\AssetRecoveryApprovalService;
use App\Services\AssetRecoveryWaiverService;
use App\Services\HrSettlementService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SettlementAndRecoveryBehaviourTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('activitylog.enabled', false);
        DB::purge('sqlite');

        Schema::create('employee_terminations', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('offboarding_case_id')->nullable();
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('company_id');
            $t->string('exit_type')->nullable();
            $t->string('status')->default('pending');
            $t->date('last_working_date')->nullable();
            $t->date('resignation_date')->nullable();
            $t->timestamps();
        });
        Schema::create('hr_settlement_forms', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('employee_termination_id');
            $t->unsignedInteger('company_id');
            $t->string('status')->default('draft');
            $t->string('policy_version');
            $t->text('inputs');
            $t->text('snapshot')->nullable();
            $t->decimal('total_payable', 15, 2)->default(0);
            $t->decimal('total_recoverable', 15, 2)->default(0);
            $t->decimal('net_amount', 15, 2)->default(0);
            $t->unsignedInteger('prepared_by')->nullable();
            $t->unsignedInteger('finalized_by')->nullable();
            $t->timestamp('finalized_at')->nullable();
            $t->string('pdf_path')->nullable();
            $t->string('document_hash')->nullable();
            $t->timestamps();
        });
        Schema::create('hr_settlement_line_items', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('settlement_form_id');
            $t->string('code');
            $t->string('kind');
            $t->string('description');
            $t->decimal('amount', 15, 2);
            $t->string('source_type')->nullable();
            $t->unsignedInteger('source_id')->nullable();
            $t->text('meta')->nullable();
            $t->timestamps();
        });
        Schema::create('asset_return_forms', function (Blueprint $t) {
            $t->increments('id');
            $t->string('reference')->nullable();
            $t->unsignedInteger('company_id');
            $t->unsignedInteger('company_asset_id')->default(1);
            $t->unsignedInteger('employee_id');
            $t->string('outcome')->default('lost');
            $t->decimal('recommended_recovery_amount', 15, 2)->nullable();
            $t->decimal('approved_recovery_amount', 15, 2)->nullable();
            $t->string('recovery_status')->default('not_required');
            $t->text('recovery_reason')->nullable();
            $t->string('status')->default('final');
            $t->unsignedInteger('recovery_approved_by')->nullable();
            $t->timestamp('recovery_approved_at')->nullable();
            $t->text('snapshot')->nullable();
            $t->timestamps();
        });
        Schema::create('asset_recovery_allocations', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('asset_return_form_id');
            $t->unsignedInteger('employee_assess_loss_id')->nullable();
            $t->string('method');
            $t->decimal('amount', 15, 2);
            $t->unsignedInteger('approved_by')->nullable();
            $t->timestamp('approved_at')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
        });
        Schema::create('employee_assess_losses', function (Blueprint $t) {
            $t->increments('id');
            $t->unsignedInteger('company_asset_id')->default(1);
            $t->unsignedInteger('employee_id');
            $t->unsignedInteger('asset_assignment_history_id')->default(1);
            $t->decimal('loss_amount', 15, 2);
            $t->decimal('deducted_amount', 15, 2)->default(0);
            $t->string('status')->default('pending');
            $t->timestamps();
        });
    }

    private function termination(string $exitType = 'termination'): EmployeeTermination
    {
        return EmployeeTermination::create([
            'user_id' => 10, 'company_id' => 1, 'exit_type' => $exitType,
            'status' => 'pending', 'last_working_date' => '2026-09-30',
        ]);
    }

    public function test_worksheet_net_is_payable_minus_recoverable_and_tags_auto_rows(): void
    {
        $svc = new HrSettlementService();
        $form = $svc->worksheet($this->termination(), 5, [
            'policy_version' => 'test-1',
            'eosb_amount' => 20000, 'leave_encashment' => 3000, 'pending_salary' => 0,
            'notice_payment' => 0, 'manual_payable' => 500,
            'advance_balance' => 1500, 'asset_recovery' => 800, 'manual_recovery' => 0,
        ]);

        $this->assertEqualsWithDelta(23500.0, (float) $form->total_payable, 0.01);
        $this->assertEqualsWithDelta(2300.0, (float) $form->total_recoverable, 0.01);
        $this->assertEqualsWithDelta(21200.0, (float) $form->net_amount, 0.01);

        $this->assertSame('auto', $form->lineItems->firstWhere('code', 'eosb')->source_type);
        $this->assertNull($form->lineItems->firstWhere('code', 'manual_payable')->source_type);
        $this->assertNull($form->lineItems->firstWhere('code', 'notice_payment'), 'zero rows are not persisted');
    }

    public function test_finalize_locks_and_refuses_regeneration(): void
    {
        $svc = new HrSettlementService();
        $t = $this->termination();
        $form = $svc->worksheet($t, 5, ['policy_version' => 'v1', 'eosb_amount' => 10000]);
        $final = $svc->finalize($form, 7);

        $this->assertSame('final', $final->status);
        $this->assertSame(7, $final->finalized_by);
        $this->assertNotNull($final->snapshot);

        $this->expectException(ValidationException::class);
        $svc->worksheet($t, 5, ['policy_version' => 'v2', 'eosb_amount' => 999]);
    }

    private function finalisedForm(float $recommended, ?int $lossId = null): AssetReturnForm
    {
        return AssetReturnForm::create([
            'reference' => 'RT-000001', 'company_id' => 1, 'employee_id' => 10,
            'outcome' => 'lost', 'recommended_recovery_amount' => $recommended,
            'status' => 'final', 'recovery_status' => 'not_required',
            'snapshot' => $lossId ? ['employee_assess_loss_id' => $lossId] : [],
        ]);
    }

    public function test_recovery_approval_is_capped_and_never_touches_deducted_amount(): void
    {
        $loss = EmployeeAssessLoss::create(['employee_id' => 10, 'loss_amount' => 1000, 'status' => 'pending']);
        $form = $this->finalisedForm(1000, $loss->id);
        $svc = new AssetRecoveryApprovalService();

        $svc->approve($form, 3, 400);
        $form->refresh();
        $loss->refresh();

        $this->assertSame('partially_approved', $form->recovery_status);
        $this->assertEqualsWithDelta(400.0, (float) $form->allocations()->sum('amount'), 0.01);
        $this->assertSame(0.0, (float) $loss->deducted_amount, 'deducted_amount must not move on approval');
        $this->assertSame('pending', $loss->status);

        // Over-cap is rejected.
        try {
            $svc->approve($form->fresh(), 3, 700);
            $this->fail('expected the remaining-amount cap to reject 700');
        } catch (ValidationException $e) {
            $this->assertStringContainsString('remaining', strtolower($e->getMessage()));
        }

        // Approving the remaining 600 fully covers it -> loss resolved.
        $svc->approve($form->fresh(), 3, 600);
        $loss->refresh();
        $this->assertSame('approved', $form->fresh()->recovery_status);
        $this->assertSame('settled', $loss->status);
        $this->assertSame(0.0, (float) $loss->deducted_amount);
    }

    public function test_waiver_records_a_ledger_row_and_settles_without_deducting(): void
    {
        $loss = EmployeeAssessLoss::create(['employee_id' => 10, 'loss_amount' => 900, 'status' => 'pending']);
        $form = $this->finalisedForm(900, $loss->id);

        (new AssetRecoveryWaiverService())->waive($form, 4, 'Goodwill write-off approved by GM');
        $form->refresh();
        $loss->refresh();

        $this->assertSame('waived', $form->recovery_status);
        $this->assertSame(AssetRecoveryAllocation::METHOD_WAIVER, $form->allocations()->first()->method);
        $this->assertEqualsWithDelta(900.0, (float) $form->allocations()->sum('amount'), 0.01);
        $this->assertSame('settled', $loss->status);
        $this->assertSame(0.0, (float) $loss->deducted_amount);
    }
}
