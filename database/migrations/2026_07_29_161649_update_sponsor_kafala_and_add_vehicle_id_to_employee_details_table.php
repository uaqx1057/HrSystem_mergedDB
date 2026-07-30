<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropColumn('sponsor_kafala');
        });

        Schema::table('employee_details', function (Blueprint $table) {
            // companies.id is int(10) unsigned, NOT bigint — must match exactly
            $table->unsignedInteger('sponsor_kafala')->nullable();
            $table->foreign('sponsor_kafala')
                ->references('id')->on('companies')
                ->nullOnDelete();

            // vehicles.id IS bigint(20) unsigned — foreignId() is correct here
            $table->foreignId('vehicle_id')
                ->nullable()
                ->constrained('vehicles')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_details', function (Blueprint $table) {
            $table->dropForeign(['sponsor_kafala']);
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn(['sponsor_kafala', 'vehicle_id']);
            $table->text('sponsor_kafala')->nullable();
        });
    }
};
