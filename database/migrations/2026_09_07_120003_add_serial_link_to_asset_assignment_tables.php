<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['asset_assignments', 'asset_assignment_history'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'company_asset_serial_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->unsignedBigInteger('company_asset_serial_id')->nullable();
                    $t->foreign('company_asset_serial_id', $table . '_cas_id_foreign')
                        ->references('id')->on('company_asset_serials')
                        ->nullOnDelete();
                });
            }
        }

        // Backfill the new link from the legacy serial_no string.
        foreach (['asset_assignments', 'asset_assignment_history'] as $table) {
            if (!Schema::hasColumn($table, 'company_asset_serial_id')) {
                continue;
            }
            DB::table($table . ' as a')
                ->join('company_asset_serials as s', function ($join) {
                    $join->on('s.company_asset_id', '=', 'a.company_asset_id')
                        ->on('s.serial_no', '=', 'a.serial_no');
                })
                ->whereNull('a.company_asset_serial_id')
                ->whereNotNull('a.serial_no')
                ->update(['a.company_asset_serial_id' => DB::raw('s.id')]);
        }

        // Normalise company_assets.status casing + recompute to the 3-state model.
        if (Schema::hasTable('company_assets')) {
            DB::statement("UPDATE company_assets SET status = LOWER(status)");

            $hasSoftDelete = Schema::hasColumn('company_asset_serials', 'deleted_at');

            $assets = DB::table('company_assets')->select('id', 'qty')->get();
            foreach ($assets as $asset) {
                $total = DB::table('company_asset_serials')
                    ->where('company_asset_id', $asset->id)
                    ->when($hasSoftDelete, fn ($q) => $q->whereNull('deleted_at'))
                    ->count();
                $available = DB::table('company_asset_serials')
                    ->where('company_asset_id', $asset->id)
                    ->where('status', 'available')
                    ->when($hasSoftDelete, fn ($q) => $q->whereNull('deleted_at'))
                    ->count();

                $status = $available === 0
                    ? 'assigned'
                    : ($available < $total ? 'partially_assigned' : 'available');

                DB::table('company_assets')->where('id', $asset->id)->update([
                    'available_qty' => $available,
                    'qty'           => max($total, (int) $asset->qty),
                    'status'        => $status,
                ]);
            }
        }
    }

    public function down(): void
    {
        foreach (['asset_assignments', 'asset_assignment_history'] as $table) {
            if (Schema::hasColumn($table, 'company_asset_serial_id')) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    $t->dropForeign($table . '_cas_id_foreign');
                    $t->dropColumn('company_asset_serial_id');
                });
            }
        }
    }
};
