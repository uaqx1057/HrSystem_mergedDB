<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Notice-period terms for an exit, captured when the termination / resignation
 * is started (not at completion). `notice_type` = immediate | notice;
 * `notice_months` = 1 | 2 | 3 when served; `notice_start_date` is null for an
 * immediate exit. `last_working_date` (already on the table) is derived from
 * these.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_offboarding_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_offboarding_cases', 'notice_type')) {
                $table->string('notice_type')->default('notice')->after('last_working_date');
            }
            if (!Schema::hasColumn('hr_offboarding_cases', 'notice_months')) {
                $table->unsignedTinyInteger('notice_months')->nullable()->after('notice_type');
            }
            if (!Schema::hasColumn('hr_offboarding_cases', 'notice_start_date')) {
                $table->date('notice_start_date')->nullable()->after('notice_months');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_offboarding_cases', function (Blueprint $table) {
            $table->dropColumn(['notice_type', 'notice_months', 'notice_start_date']);
        });
    }
};
