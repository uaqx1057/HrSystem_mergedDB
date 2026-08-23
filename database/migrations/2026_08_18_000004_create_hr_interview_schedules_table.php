<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_interview_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('hr_candidates')->cascadeOnDelete();
            $table->unsignedInteger('event_id');
            $table->foreign('event_id')->references('id')->on('events')->cascadeOnDelete();
            $table->string('round')->default('HR Screening');
            // No FK constraint on users — same errno 150 as branches (hr_job_openings migration).
            $table->unsignedInteger('scheduled_by')->nullable();
            $table->string('status')->default('scheduled');
            $table->string('outcome')->nullable();
            $table->text('feedback')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_interview_schedules');
    }
};
