<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->boolean('verify_employee_profile')->default(false);
            $table->boolean('setup_bank_and_payroll')->default(false);
            $table->boolean('assign_insurance')->default(false);
            $table->boolean('assign_required_assets')->default(false);
            $table->boolean('manager_confirmation')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn(['verify_employee_profile', 'setup_bank_and_payroll', 'assign_insurance', 'assign_required_assets', 'manager_confirmation']);
        });
    }
};
