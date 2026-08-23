<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_candidate_onboarding_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('hr_candidate_onboarding_cases')->cascadeOnDelete();
            $table->string('title');
            $table->string('owner_type')->default('hr');
            // No FK constraint on users — same errno 150 as branches (hr_job_openings migration).
            $table->unsignedInteger('assigned_to')->nullable();
            $table->string('status')->default('pending');
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_candidate_onboarding_tasks');
    }
};
