<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_candidates', function (Blueprint $table) {
            $table->foreignId('job_opening_id')->nullable()->after('branch_id')->constrained('hr_job_openings')->nullOnDelete();
            $table->string('source')->default('manual')->after('job_opening_id');
            $table->text('cover_note')->nullable()->after('notes');
            $table->string('rejection_reason')->nullable()->after('cover_note');
            // Confirmed by HR during the pre-hire onboarding checklist, used to construct the real employee record on conversion.
            // teams.id is INT UNSIGNED (increments()), not the BIGINT UNSIGNED foreignId() creates.
            $table->unsignedInteger('department_id')->nullable()->after('rejection_reason');
            $table->foreign('department_id')->references('id')->on('teams')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->after('department_id')->constrained('designations')->nullOnDelete();
            $table->decimal('basic_salary', 15, 2)->nullable()->after('designation_id');
        });
    }

    public function down(): void
    {
        Schema::table('hr_candidates', function (Blueprint $table) {
            $table->dropForeign(['job_opening_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropColumn(['job_opening_id', 'source', 'cover_note', 'rejection_reason', 'department_id', 'designation_id', 'basic_salary']);
        });
    }
};
