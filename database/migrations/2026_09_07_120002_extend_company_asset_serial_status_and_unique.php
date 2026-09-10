<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('company_asset_serials')) {
            return;
        }

        // enum(available,assigned) -> varchar so we can add pending / lost / damaged /
        // retired without another ALTER every time. Raw ALTER on purpose: doctrine/dbal
        // throws "Unknown database type enum" when introspecting the current column.
        DB::statement("ALTER TABLE company_asset_serials MODIFY status VARCHAR(30) NOT NULL DEFAULT 'available'");

        $hasSoftDelete = Schema::hasColumn('company_asset_serials', 'deleted_at');

        // De-dupe first, or the unique index can't be created. For each
        // (asset, serial) group keep one row (prefer an assigned/pending one,
        // else the lowest id) and drop the rest.
        $dupes = DB::table('company_asset_serials')
            ->select('company_asset_id', 'serial_no', DB::raw('COUNT(*) as c'))
            ->when($hasSoftDelete, fn ($q) => $q->whereNull('deleted_at'))
            ->groupBy('company_asset_id', 'serial_no')
            ->having('c', '>', 1)
            ->get();

        foreach ($dupes as $dupe) {
            $rows = DB::table('company_asset_serials')
                ->where('company_asset_id', $dupe->company_asset_id)
                ->where('serial_no', $dupe->serial_no)
                ->when($hasSoftDelete, fn ($q) => $q->whereNull('deleted_at'))
                ->orderByRaw("FIELD(status,'assigned','pending') DESC")
                ->orderBy('id')
                ->get();

            foreach ($rows->slice(1) as $extra) {
                if ($hasSoftDelete) {
                    DB::table('company_asset_serials')->where('id', $extra->id)->update(['deleted_at' => now()]);
                } else {
                    DB::table('company_asset_serials')->where('id', $extra->id)->delete();
                }
            }
        }

        // one active serial number per asset (deleted_at in the key lets a
        // soft-deleted serial number be re-used, MySQL treats NULLs as distinct)
        $indexName = 'cas_asset_serial_unique';
        $exists = collect(DB::select("SHOW INDEX FROM company_asset_serials"))
            ->pluck('Key_name')->contains($indexName);

        if (!$exists) {
            Schema::table('company_asset_serials', function (Blueprint $table) use ($indexName, $hasSoftDelete) {
                if ($hasSoftDelete) {
                    $table->unique(['company_asset_id', 'serial_no', 'deleted_at'], $indexName);
                } else {
                    $table->unique(['company_asset_id', 'serial_no'], $indexName);
                }
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('company_asset_serials')) {
            return;
        }

        $indexes = collect(DB::select("SHOW INDEX FROM company_asset_serials"))->pluck('Key_name');

        // MySQL may drop the auto FK index once cas_asset_serial_unique exists.
        // Re-create a plain index on company_asset_id first, or the unique index
        // cannot be dropped (it is "needed in a foreign key constraint").
        if (!$indexes->contains('company_asset_serials_company_asset_id_foreign')) {
            Schema::table('company_asset_serials', function (Blueprint $t) {
                $t->index('company_asset_id', 'company_asset_serials_company_asset_id_foreign');
            });
        }

        if ($indexes->contains('cas_asset_serial_unique')) {
            Schema::table('company_asset_serials', fn (Blueprint $t) => $t->dropUnique('cas_asset_serial_unique'));
        }
    }
};
