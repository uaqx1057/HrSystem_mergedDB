<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('company_assets') && !Schema::hasColumn('company_assets', 'deleted_at')) {
            Schema::table('company_assets', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (Schema::hasTable('company_asset_serials') && !Schema::hasColumn('company_asset_serials', 'deleted_at')) {
            Schema::table('company_asset_serials', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('company_assets', 'deleted_at')) {
            Schema::table('company_assets', fn (Blueprint $t) => $t->dropSoftDeletes());
        }
        if (Schema::hasColumn('company_asset_serials', 'deleted_at')) {
            Schema::table('company_asset_serials', fn (Blueprint $t) => $t->dropSoftDeletes());
        }
    }
};
