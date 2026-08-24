<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * These are the columns the new multistep apply form needs that do NOT
     * already exist on hr_candidates (based on every field referenced in
     * CareersController, HrCandidateController@saveOnboardingChecklist and
     * CandidateOnboardingService).
     *
     * Already-existing columns we simply reuse (no migration needed):
     * name, email, mobile, employee_type, iqama_no, iqama_profession,
     * iqama_expiry_date, national_id, national_id_expiry_date, passport_no,
     * passport_expiry_date, basic_salary, branch_id, designation_id,
     * department_id, status, source, company_id, job_opening_id.
     */
    public function up(): void
    {
        Schema::table('hr_candidates', function (Blueprint $table) {
            $table->string('salutation', 20)->nullable()->after('name');
            $table->date('date_of_birth')->nullable()->after('email');
            $table->unsignedBigInteger('country_id')->nullable()->after('mobile');
            $table->string('gender', 20)->nullable()->after('country_id');
            $table->text('address')->nullable()->after('gender');
            $table->string('marital_status', 30)->nullable()->after('address');
            $table->string('linkedin_username')->nullable()->after('marital_status');

            $table->index('country_id');
        });
    }

    public function down(): void
    {
        Schema::table('hr_candidates', function (Blueprint $table) {
            $table->dropIndex(['country_id']);
            $table->dropColumn([
                'salutation',
                'date_of_birth',
                'country_id',
                'gender',
                'address',
                'marital_status',
                'linkedin_username',
            ]);
        });
    }
};
