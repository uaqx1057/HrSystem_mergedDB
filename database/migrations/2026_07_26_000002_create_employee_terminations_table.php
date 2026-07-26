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
        Schema::create('employee_terminations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->text('reason')->nullable();

            // Overall status: pending (Start Termination) | completed (Done)
            $table->string('status')->default('pending');

            // IT clearance
            $table->string('it_clearance_status')->default('pending'); // pending | issued
            $table->unsignedBigInteger('it_clearance_issued_by')->nullable();
            $table->timestamp('it_clearance_issued_at')->nullable();
            $table->timestamp('it_reminder_sent_at')->nullable();

            // Finance clearance
            $table->string('finance_clearance_status')->default('pending'); // pending | issued
            $table->unsignedBigInteger('finance_clearance_issued_by')->nullable();
            $table->timestamp('finance_clearance_issued_at')->nullable();
            $table->timestamp('finance_reminder_sent_at')->nullable();

            $table->unsignedBigInteger('completed_by')->nullable();
            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('company_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_terminations');
    }
};
