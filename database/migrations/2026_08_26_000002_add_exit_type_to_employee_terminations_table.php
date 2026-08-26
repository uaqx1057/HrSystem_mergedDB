<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->string('exit_type')->default('termination')->after('user_id');
            $table->date('resignation_date')->nullable()->after('terminate_reason');
            $table->date('last_working_date')->nullable()->after('resignation_date');
            $table->index(['company_id', 'exit_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->dropIndex(['company_id', 'exit_type', 'status']);
            $table->dropColumn(['exit_type', 'resignation_date', 'last_working_date']);
        });
    }
};