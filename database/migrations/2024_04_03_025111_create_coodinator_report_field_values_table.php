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
        Schema::create('coordinator_report_field_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('coordinator_report_id');
            $table->unsignedInteger('field_id');
            $table->string('value');

            $table->foreign('field_id')->references('id')->on('business_fields')->cascadeOnDelete()->restrictOnUpdate();
            $table->foreign('coordinator_report_id')->references('id')->on('coordinator_reports')->cascadeOnDelete()->restrictOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coordinator_report_field_values');
    }
};
