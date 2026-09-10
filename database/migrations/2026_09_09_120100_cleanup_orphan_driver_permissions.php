<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The previous migration deleted the 'drivers' module row, but on this DB the
 * permissions.module_id / permission_role.permission_id foreign keys did not
 * cascade, leaving 4 orphan permission rows + their permission_role pivots.
 * Remove them explicitly.
 */
return new class extends Migration
{
    private array $names = ['add_drivers', 'view_drivers', 'edit_drivers', 'delete_drivers'];

    public function up(): void
    {
        $permIds = DB::table('permissions')->whereIn('name', $this->names)->pluck('id')->all();

        if (!empty($permIds)) {
            DB::table('permission_role')->whereIn('permission_id', $permIds)->delete();
            DB::table('permissions')->whereIn('id', $permIds)->delete();
        }
    }

    public function down(): void
    {
        // Re-seeding is handled by 2026_09_09_120000_remove_drivers_module::down()
        // (which also re-creates the module). Nothing to do here.
    }
};
