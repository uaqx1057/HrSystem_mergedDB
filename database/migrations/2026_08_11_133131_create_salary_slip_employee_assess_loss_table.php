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
        Schema::create('salary_slip_employee_assess_loss', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_slip_id');
            $table->unsignedBigInteger('employee_assess_loss_id');
            $table->decimal('deducted_amount', 15, 2)->default(0);
            $table->timestamps();

            $table->foreign('salary_slip_id')->references('id')->on('salary_slips')->onDelete('cascade');
            $table->foreign('employee_assess_loss_id')->references('id')->on('employee_assess_losses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salary_slip_employee_assess_loss');
    }
};
