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
        Schema::table('advance_salaries', function (Blueprint $table) {
            $table->decimal('deducted_amount', 15, 2)->default(0)->after('advance_salary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('advance_salaries', function (Blueprint $table) {
            $table->dropColumn('deducted_amount');
        });
    }
};
