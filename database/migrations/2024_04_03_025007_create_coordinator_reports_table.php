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
        Schema::create('coordinator_reports', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('business_id');
            $table->boolean('admin_submitted')->default(false);
            $table->foreign('driver_id')->references('id')->on('drivers')->cascadeOnDelete()->restrictOnUpdate();
            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete()->restrictOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('coordinator_reports');
    }
};
