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
        Schema::create('asset_assignment_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_asset_id');
            $table->unsignedBigInteger('employee_id');
            $table->string('action_type'); // assigned / returned
            $table->unsignedInteger('qty')->default(1);
            $table->timestamp('action_at')->nullable();
            $table->text('signed_document')->nullable();

            $table->timestamps();

            $table->foreign('company_asset_id')
                ->references('id')
                ->on('company_assets')
                ->cascadeOnDelete();

            $table->foreign('employee_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assignment_history');
    }
};
