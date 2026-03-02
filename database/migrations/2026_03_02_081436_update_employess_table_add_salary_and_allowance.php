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
            $table->text('basic_salary')->nullable()->after('sponsorship_transfer_date');

            $table->enum('vehicle_allocation', ['yes', 'no'])
                  ->default('yes')
                  ->after('sponsorship_transfer_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn('basic_salary');
            $table->dropColumn('vehicle_allocation');
        });
    }
};
