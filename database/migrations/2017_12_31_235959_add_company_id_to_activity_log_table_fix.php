<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('activity_log', 'company_id')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->unsignedBigInteger('company_id')->nullable()->index();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('activity_log', 'company_id')) {
            Schema::table('activity_log', function (Blueprint $table) {
                $table->dropColumn('company_id');
            });
        }
    }
};
