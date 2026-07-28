<?php

namespace App\Console\Commands;

use App\Models\EmployeeSystemAccess;
use App\Models\User;
use App\Scopes\ActiveScope;
use App\Services\EmployeeSystemSyncService;
use Illuminate\Console\Command;

class SyncLinkedEmployees extends Command
{
    protected $signature = 'hr:sync-linked-employees';

    protected $description = 'One-time/on-demand backfill: push HR profile data to every already-linked DMS/DOBS account';

    public function handle(EmployeeSystemSyncService $sync): int
    {
        $employeeIds = EmployeeSystemAccess::where('is_active', true)->distinct()->pluck('employee_id');

        $this->info("Backfilling {$employeeIds->count()} linked employees...");

        foreach ($employeeIds as $employeeId) {
            $hrUser = User::withoutGlobalScope(ActiveScope::class)->find($employeeId);

            if (!$hrUser) {
                $this->warn("skip {$employeeId}: HR user not found");
                continue;
            }

            try {
                $sync->syncEmployeeProfileToLinkedSystems($hrUser);
                $this->line("synced employee_id={$employeeId} ({$hrUser->name})");
            } catch (\Throwable $e) {
                $this->error("FAILED employee_id={$employeeId}: " . $e->getMessage());
            }
        }

        $this->info('Done.');

        return self::SUCCESS;
    }
}
