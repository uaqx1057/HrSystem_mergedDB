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
        Schema::table('drivers', function (Blueprint $table) {
                // Define the 'driver_type_id' column first
                $table->unsignedBigInteger('driver_type_id')->nullable();

                // Add foreign key constraint
                $table->foreign('driver_type_id')
                    ->references('id')
                    ->on('driver_types')
                    ->onDelete('cascade')
                    ->onUpdate('cascade');

                // Add other columns
                $table->decimal('vehicle_monthly_cost', 10,2)->default(0);
                $table->decimal('mobile_data', 10,2)->default(0);
                $table->decimal('accommodation', 10,2)->default(0);
                $table->decimal('government_cost', 10,2)->default(0);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['driver_type_id']);

            // Drop the columns
            $table->dropColumn('driver_type_id');
            $table->dropColumn('vehicle_monthly_cost');
            $table->dropColumn('mobile_data');
            $table->dropColumn('accommodation');
            $table->dropColumn('government_cost');
        });
    }
};
