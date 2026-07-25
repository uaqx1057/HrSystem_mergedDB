<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_edit_states', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedInteger('last_saved_step')->nullable();
            $table->unsignedBigInteger('version')->default(0);
            $table->foreignId('last_saved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('last_saved_at')->nullable();
            $table->timestamps();
            $table->unique(['company_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_edit_states');
    }
};
