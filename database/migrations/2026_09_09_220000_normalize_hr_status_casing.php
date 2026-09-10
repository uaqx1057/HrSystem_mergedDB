<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Normalise the two HR-owned status columns to lowercase now that every reader
 * goes through a model constant (AssetAssignment::STATUS_*, EmployeeAssessLoss::
 * STATUS_*). Scope is HR-app only - asset_assignments and employee_assess_losses
 * are the Worksuite company-assets / asset-loss tables, not shared with the
 * driver/vehicle data used by DMS/DOBS.
 *
 *   asset_assignments.status       'Pending'  -> 'pending'   'Assigned' -> 'assigned'
 *   employee_assess_losses.status  'Pending'  -> 'pending'   'Deducted' -> 'settled'
 */
return new class extends Migration
{
    public function up(): void
    {
        $isMysql = DB::connection()->getDriverName() === 'mysql';

        if (Schema::hasTable('asset_assignments')) {
            if ($isMysql) {
                DB::statement("ALTER TABLE asset_assignments MODIFY status VARCHAR(255) NOT NULL DEFAULT 'pending'");
            }
            DB::table('asset_assignments')->where('status', 'Pending')->update(['status' => 'pending']);
            DB::table('asset_assignments')->where('status', 'Assigned')->update(['status' => 'assigned']);
        }

        if (Schema::hasTable('employee_assess_losses')) {
            if ($isMysql) {
                DB::statement("ALTER TABLE employee_assess_losses MODIFY status VARCHAR(20) NOT NULL DEFAULT 'pending'");
            }
            DB::table('employee_assess_losses')->where('status', 'Pending')->update(['status' => 'pending']);
            DB::table('employee_assess_losses')->whereIn('status', ['Deducted', 'deducted'])->update(['status' => 'settled']);
        }
    }

    public function down(): void
    {
        $isMysql = DB::connection()->getDriverName() === 'mysql';

        if (Schema::hasTable('asset_assignments')) {
            DB::table('asset_assignments')->where('status', 'pending')->update(['status' => 'Pending']);
            DB::table('asset_assignments')->where('status', 'assigned')->update(['status' => 'Assigned']);
            if ($isMysql) {
                DB::statement("ALTER TABLE asset_assignments MODIFY status VARCHAR(255) NOT NULL DEFAULT 'Pending'");
            }
        }

        if (Schema::hasTable('employee_assess_losses')) {
            DB::table('employee_assess_losses')->where('status', 'settled')->update(['status' => 'Deducted']);
            DB::table('employee_assess_losses')->where('status', 'pending')->update(['status' => 'Pending']);
            if ($isMysql) {
                DB::statement("ALTER TABLE employee_assess_losses MODIFY status ENUM('Pending','Deducted') NOT NULL DEFAULT 'Pending'");
            }
        }
    }
};
