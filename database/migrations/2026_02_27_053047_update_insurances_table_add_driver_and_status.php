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
        Schema::table('insurances', function (Blueprint $table) {
            // Add driver_id column
            $table->unsignedBigInteger('driver_id')->nullable()->after('employee_id');

            // Add status enum column
            $table->enum('status', ['active', 'cancelled'])
                  ->default('active')
                  ->after('driver_id');

            // Make employee column nullable
            $table->unsignedBigInteger('employee_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insurances', function (Blueprint $table) {
            $table->dropColumn('driver_id');
            $table->dropColumn('status');

            // Revert employee back to not nullable (if needed)
            $table->unsignedBigInteger('employee_id')->nullable(false)->change();
        });
    }
};
