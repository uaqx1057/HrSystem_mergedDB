<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * The HR app no longer has any Drivers pages. Remove the 'drivers' module and its
 * add/view/edit/delete_drivers permissions so it stops showing in the Role
 * Permissions grid. permissions.module_id and permission_role.permission_id are
 * ON DELETE CASCADE, so deleting the module row cleans up everything.
 *
 * This is HR-app permission config only (Worksuite modules/permissions tables) —
 * it is not operational data shared with DMS/DOBS.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('modules')->where('module_name', 'drivers')->delete();
    }

    public function down(): void
    {
        if (DB::table('modules')->where('module_name', 'drivers')->exists()) {
            return;
        }

        $moduleId = DB::table('modules')->insertGetId([
            'module_name'   => 'drivers',
            'is_superadmin' => 0,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        foreach (['add_drivers', 'view_drivers', 'edit_drivers', 'delete_drivers'] as $name) {
            DB::table('permissions')->updateOrInsert(
                ['name' => $name],
                [
                    'display_name'        => ucwords(str_replace('_', ' ', $name)),
                    'module_id'           => $moduleId,
                    'is_custom'           => 0,
                    'allowed_permissions' => '{"all":4, "none":5, "branch":6}',
                    'created_at'          => now(),
                    'updated_at'          => now(),
                ]
            );
        }
    }
};
