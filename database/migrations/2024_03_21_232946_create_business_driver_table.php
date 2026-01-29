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
        Schema::create('business_driver', function (Blueprint $table) {
            $table->id();
            $table->string('platform_id');
            $table->unsignedInteger('driver_id');
            $table->unsignedInteger('business_id');

            $table->foreign('driver_id')->references('id')->on('drivers')->cascadeOnDelete()->restrictOnUpdate();
            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete()->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_driver');
    }
};
