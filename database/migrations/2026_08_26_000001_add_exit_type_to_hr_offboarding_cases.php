<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_offboarding_cases', function (Blueprint $table) {
            $table->string('exit_type')->default('termination')->after('employee_id');
            $table->date('resignation_date')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('hr_offboarding_cases', function (Blueprint $table) {
            $table->dropColumn(['exit_type', 'resignation_date']);
        });
    }
};