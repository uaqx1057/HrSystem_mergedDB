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
        Schema::create('company_asset_serials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_asset_id');
            $table->string('serial_no');
            $table->enum('status', ['available', 'assigned'])->default('available');
            $table->timestamps();

            $table->foreign('company_asset_id')
                ->references('id')->on('company_assets')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_asset_serials');
    }
};
