<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_onboarding_cases', function (Blueprint $table) {
            $table->id(); $table->unsignedInteger('company_id'); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('template_name')->default('standard'); $table->string('status')->default('open');
            $table->date('due_date')->nullable(); $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable(); $table->timestamps();
            $table->index(['company_id', 'employee_id', 'status']);
        });
        Schema::create('hr_onboarding_tasks', function (Blueprint $table) {
            $table->id(); $table->foreignId('case_id')->constrained('hr_onboarding_cases')->cascadeOnDelete();
            $table->string('title'); $table->string('owner_type')->default('hr'); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); $table->date('due_date')->nullable(); $table->timestamp('completed_at')->nullable(); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('hr_offboarding_cases', function (Blueprint $table) {
            $table->id(); $table->unsignedInteger('company_id'); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason'); $table->string('status')->default('open'); $table->date('last_working_date');
            $table->foreignId('initiated_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('completed_at')->nullable(); $table->timestamps();
        });
        Schema::create('hr_offboarding_tasks', function (Blueprint $table) {
            $table->id(); $table->foreignId('case_id')->constrained('hr_offboarding_cases')->cascadeOnDelete();
            $table->string('title'); $table->string('owner_type')->default('hr'); $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending'); $table->date('due_date')->nullable(); $table->timestamp('completed_at')->nullable(); $table->text('notes')->nullable(); $table->timestamps();
        });
        Schema::create('hr_employee_transfers', function (Blueprint $table) {
            $table->id(); $table->unsignedInteger('company_id'); $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('from_branch_id')->nullable()->constrained('branches')->nullOnDelete(); $table->foreignId('to_branch_id')->constrained('branches')->cascadeOnDelete();
            $table->unsignedInteger('from_department_id')->nullable(); $table->foreign('from_department_id')->references('id')->on('teams')->nullOnDelete(); $table->unsignedInteger('to_department_id')->nullable(); $table->foreign('to_department_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreignId('from_manager_id')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('to_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('effective_date'); $table->string('status')->default('pending'); $table->text('reason')->nullable();
            $table->foreignId('requested_by')->nullable()->constrained('users')->nullOnDelete(); $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete(); $table->timestamp('applied_at')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_transfers'); Schema::dropIfExists('hr_offboarding_tasks'); Schema::dropIfExists('hr_offboarding_cases'); Schema::dropIfExists('hr_onboarding_tasks'); Schema::dropIfExists('hr_onboarding_cases');
    }
};
