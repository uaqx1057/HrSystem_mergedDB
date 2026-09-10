<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_offboarding_cases', function (Blueprint $table) {
            $table->string('reference')->nullable()->after('id');
            $table->string('approval_status')->default('awaiting_approval')->after('status');
            $table->unsignedBigInteger('manager_id')->nullable()->after('initiated_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('manager_id');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by');
            $table->text('rejected_reason')->nullable()->after('rejected_at');
            $table->string('settlement_status')->default('not_started')->after('approval_status');
            $table->decimal('settlement_amount', 14, 2)->nullable()->after('settlement_status');
            $table->timestamp('access_revoked_at')->nullable()->after('settlement_amount');
            $table->unsignedBigInteger('completed_by')->nullable()->after('completed_at');
            $table->unsignedBigInteger('reverted_by')->nullable()->after('completed_by');
            $table->timestamp('reverted_at')->nullable()->after('reverted_by');
            $table->text('revert_reason')->nullable()->after('reverted_at');
            $table->unique(['company_id', 'reference'], 'hr_offboard_case_company_ref_unique');
            $table->index(['employee_id', 'approval_status', 'status'], 'hr_offboard_case_employee_state_idx');
        });

        // Existing open cases were already initiated under the legacy workflow and
        // must remain actionable when approval gating is introduced.
        DB::table('hr_offboarding_cases')
            ->where('status', 'open')
            ->update(['approval_status' => 'approved', 'approved_at' => now()]);

        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->unsignedBigInteger('offboarding_case_id')->nullable()->after('id');
            $table->unique('offboarding_case_id', 'employee_term_case_unique');
        });

        Schema::table('hr_offboarding_tasks', function (Blueprint $table) {
            $table->string('category')->default('custom')->after('title');
            $table->boolean('is_required')->default(true)->after('category');
            $table->text('blocked_reason')->nullable()->after('notes');
            $table->text('waiver_reason')->nullable()->after('blocked_reason');
            $table->unsignedBigInteger('completed_by')->nullable()->after('completed_at');
            $table->string('evidence_path')->nullable()->after('completed_by');
            $table->index(['case_id', 'category', 'status'], 'hr_offboard_task_case_category_idx');
        });

        Schema::create('hr_offboarding_task_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->string('exit_type')->nullable();
            $table->string('employee_type')->nullable();
            $table->string('category');
            $table->string('title');
            $table->string('owner_type')->default('hr');
            $table->boolean('is_required')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['company_id', 'exit_type', 'employee_type'], 'hr_offboard_template_scope_idx');
        });

        Schema::create('hr_lifecycle_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_user_id');
            $table->unsignedInteger('company_id');
            $table->string('event');
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['subject_user_id', 'created_at'], 'hr_lifecycle_event_subject_idx');
            $table->index(['company_id', 'event'], 'hr_lifecycle_event_company_idx');
        });

        Schema::create('hr_system_sync_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('offboarding_case_id')->nullable();
            $table->string('operation');
            $table->string('status')->default('pending');
            $table->json('systems')->nullable();
            $table->unsignedInteger('attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'operation'], 'hr_system_sync_status_idx');
            $table->index(['employee_id', 'operation'], 'hr_system_sync_employee_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_system_sync_jobs');
        Schema::dropIfExists('hr_lifecycle_events');
        Schema::dropIfExists('hr_offboarding_task_templates');

        Schema::table('hr_offboarding_tasks', function (Blueprint $table) {
            $table->dropIndex('hr_offboard_task_case_category_idx');
            $table->dropColumn(['category', 'is_required', 'blocked_reason', 'waiver_reason', 'completed_by', 'evidence_path']);
        });

        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->dropUnique('employee_term_case_unique');
            $table->dropColumn('offboarding_case_id');
        });

        Schema::table('hr_offboarding_cases', function (Blueprint $table) {
            $table->dropUnique('hr_offboard_case_company_ref_unique');
            $table->dropIndex('hr_offboard_case_employee_state_idx');
            $table->dropColumn(['reference', 'approval_status', 'manager_id', 'approved_by', 'approved_at', 'rejected_by', 'rejected_at', 'rejected_reason', 'settlement_status', 'settlement_amount', 'access_revoked_at', 'completed_by', 'reverted_by', 'reverted_at', 'revert_reason']);
        });
    }
};
