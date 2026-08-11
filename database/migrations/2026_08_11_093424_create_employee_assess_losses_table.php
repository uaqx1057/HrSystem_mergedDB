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
        Schema::create('employee_assess_losses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_asset_id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('asset_assignment_history_id');
            $table->decimal('loss_amount', 15, 2);
            $table->decimal('deducted_amount', 15, 2)->default(0);
            $table->enum('status', ['Pending', 'Deducted'])->default('Pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_assess_losses');
    }
};
