<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The HR Clearance & Offboarding form (HR-0111) captured on its own gated
 * data-entry screen: handover checklist, statutory/government actions with
 * references, leave & entitlement confirmations, the employee declaration and
 * the HR clearance decision. The branded PDF renders from `hr_clearance_data`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_offboarding_cases', function (Blueprint $table) {
            if (!Schema::hasColumn('hr_offboarding_cases', 'hr_clearance_data')) {
                $table->json('hr_clearance_data')->nullable()->after('access_revoked_at');
            }
            if (!Schema::hasColumn('hr_offboarding_cases', 'hr_clearance_status')) {
                $table->string('hr_clearance_status')->default('pending')->after('hr_clearance_data');
            }
            if (!Schema::hasColumn('hr_offboarding_cases', 'hr_clearance_decision')) {
                $table->string('hr_clearance_decision')->nullable()->after('hr_clearance_status');
            }
            if (!Schema::hasColumn('hr_offboarding_cases', 'hr_cleared_by')) {
                $table->unsignedBigInteger('hr_cleared_by')->nullable()->after('hr_clearance_decision');
            }
            if (!Schema::hasColumn('hr_offboarding_cases', 'hr_cleared_at')) {
                $table->timestamp('hr_cleared_at')->nullable()->after('hr_cleared_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_offboarding_cases', function (Blueprint $table) {
            $table->dropColumn([
                'hr_clearance_data', 'hr_clearance_status', 'hr_clearance_decision',
                'hr_cleared_by', 'hr_cleared_at',
            ]);
        });
    }
};
