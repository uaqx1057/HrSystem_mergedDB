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
        Schema::create('driver_calculations', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('business_id');
            $table->foreign('business_id')->references('id')->on('businesses')->cascadeOnDelete()->restrictOnUpdate();
            $table->enum('type', ['RANGE', 'EQUAL & ABOVE', 'FIXED']);
            $table->decimal('amount', 10,2)->default(0);
            $table->integer('from_value')->nullable();
            $table->integer('to_value')->nullable();
            $table->integer('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_calculations');
    }
};
