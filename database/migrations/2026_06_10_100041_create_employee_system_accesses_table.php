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
        Schema::create('employee_system_access', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id'); // HR users.id
            $table->enum('system', ['dms', 'dobs']);
            $table->unsignedBigInteger('system_user_id')->nullable();
            $table->string('role')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('provisioned_at')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'system']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_system_access');
    }
};