<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hr_employee_transfers', function (Blueprint $table) {
            $table->string('asset_decision')->nullable()->after('reason');
        });
    }

    public function down(): void
    {
        Schema::table('hr_employee_transfers', function (Blueprint $table) {
            $table->dropColumn('asset_decision');
        });
    }
};
