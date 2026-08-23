<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_candidate_onboarding_cases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('hr_candidates')->cascadeOnDelete();
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->string('status')->default('open');
            $table->date('due_date')->nullable();
            // No FK constraint on users — same errno 150 as branches (hr_job_openings migration).
            $table->unsignedInteger('initiated_by')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->boolean('documents_verified')->default(false);
            $table->boolean('compensation_confirmed')->default(false);
            $table->boolean('bank_details_collected')->default(false);
            $table->boolean('contract_signed')->default(false);
            $table->boolean('manager_signoff')->default(false);
            // Master switch: only when this AND all five items above are true does
            // saving the checklist create/update the real employee record.
            $table->boolean('convert_to_employee')->default(false);
       
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_candidate_onboarding_cases');
    }
};
