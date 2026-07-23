<?php

namespace App\Console\Commands;

use App\Models\HrAccessScope;
use App\Models\User;
use Illuminate\Console\Command;

class ManageHrAccessScope extends Command
{
    protected $signature = 'hr:access-scope
        {action : list, grant, or revoke}
        {userId? : HR user ID for grant or revoke}
        {module? : HR module name for grant or revoke}
        {--company= : Company ID; defaults to the HR user company}
        {--until= : Optional ISO-8601 expiry for a grant}
        {--granted-by= : HR administrator user ID for audit purposes}';

    protected $description = 'Manage explicit HR-only cross-branch access scopes';

    private const MODULES = [
        'leave', 'attendance', 'shift_schedules', 'payroll', 'insurance',
        'air_tickets', 'advance_salaries', 'company_assets', 'employee_bank_accounts',
    ];

    public function handle(): int
    {
        $action = $this->argument('action');

        if ($action === 'list') {
            $query = HrAccessScope::query()->with('user:id,name,email', 'grantedBy:id,name');

            if ($this->option('company')) {
                $query->where('company_id', $this->option('company'));
            }

            $this->table(
                ['ID', 'Company', 'User', 'Module', 'Active', 'Ends at', 'Granted by'],
                $query->orderBy('company_id')->orderBy('user_id')->get()->map(fn (HrAccessScope $scope) => [
                    $scope->id,
                    $scope->company_id,
                    $scope->user?->name,
                    $scope->module,
                    $scope->is_active ? 'yes' : 'no',
                    optional($scope->ends_at)->toDateTimeString(),
                    $scope->grantedBy?->name,
                ])->all()
            );

            return self::SUCCESS;
        }

        if (!in_array($action, ['grant', 'revoke'], true) || !$this->argument('userId') || !$this->argument('module')) {
            $this->error('Use list, or grant/revoke with a user ID and an allowed module.');

            return self::INVALID;
        }

        $module = $this->argument('module');
        if (!in_array($module, self::MODULES, true)) {
            $this->error('Allowed modules: ' . implode(', ', self::MODULES));

            return self::INVALID;
        }

        $user = User::withoutGlobalScopes()->findOrFail($this->argument('userId'));
        $companyId = $this->option('company') ?: $user->company_id;

        if (!$companyId) {
            $this->error('A company ID is required for this user.');

            return self::INVALID;
        }

        $scope = HrAccessScope::firstOrNew([
            'company_id' => $companyId,
            'user_id' => $user->id,
            'module' => $module,
        ]);

        if ($action === 'revoke') {
            $scope->is_active = false;
            $scope->ends_at = now();
            $scope->save();
            $this->info("Revoked {$module} cross-branch access for {$user->name}.");

            return self::SUCCESS;
        }

        $scope->fill([
            'scope' => 'all',
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => $this->option('until'),
            'granted_by' => $this->option('granted-by') ?: null,
        ]);
        $scope->save();
        $this->info("Granted {$module} cross-branch access to {$user->name}.");

        return self::SUCCESS;
    }
}
