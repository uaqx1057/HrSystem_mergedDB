<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The employee's personal (non-work) email, used to send clearance / offboarding
 * documents that must survive the work account being deactivated.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_details', 'personal_email')) {
                $table->string('personal_email')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn('personal_email');
        });
    }
};
