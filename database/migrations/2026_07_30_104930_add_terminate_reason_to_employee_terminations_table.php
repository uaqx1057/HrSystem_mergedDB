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
        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->text('terminate_reason')->nullable()->after('revert_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employee_terminations', function (Blueprint $table) {
            $table->dropColumn(['terminate_reason']);
        });
    }
};
