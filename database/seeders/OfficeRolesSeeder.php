<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Sets up the Speed Logi office roles for the HR app (Worksuite RBAC, company 27).
 *
 * - CEO gets full access (cloned from `admin`).
 * - HR Manager already exists and is configured (id 70) - only ensures the
 *   offboarding/clearance permissions are 'all'.
 * - Finance / Accountant / IT / GRO / GM get targeted grants over the vetted
 *   `employee` baseline (id 68).
 * - Line-manager roles get branch-scoped view + leave approval; the offboarding
 *   handover clearance is completed via the task's assigned_to (no employee-edit).
 * - Everyone else is a straight clone of `employee` (self-service only).
 * - The DMS-import junk roles (no company, no users) are soft-deleted.
 *
 * Idempotent: re-running rebuilds each role's permission_role from its template.
 * Run: php artisan db:seed --class=OfficeRolesSeeder --force
 */
class OfficeRolesSeeder extends Seeder
{
    private const COMPANY_ID = 27;
    private const TPL_EMPLOYEE = 68;
    private const TPL_ADMIN = 67;

    private const TYPE_ALL = 4;
    private const TYPE_BRANCH = 6;

    /** permission_id => name (for reference only). */
    private const P = [
        'view_employees' => 37, 'add_employees' => 36, 'edit_employees' => 38, 'delete_employees' => 39,
        'manage_termination_employees' => 463, 'view_pending_termination_employees' => 464, 'view_terminated_employees' => 465,
        'manage_it_clearance' => 466, 'manage_finance_clearance' => 467,
        'view_payroll' => 378, 'add_payroll' => 377, 'edit_payroll' => 379, 'delete_payroll' => 380,
        'view_advance_salary' => 445, 'add_advance_salary' => 444, 'edit_advance_salary' => 446,
        'view_company_assets' => 437, 'add_company_assets' => 436, 'edit_company_assets' => 438,
        'delete_company_assets' => 439, 'assign_company_asset_to_employee' => 440,
        'asset_loss_deduction' => 469,
        'view_leave' => 187, 'approve_or_reject_leaves' => 190,
        'view_insurance' => 450, 'add_insurance' => 449, 'edit_insurance' => 451,
        'view_attendance' => 114, 'view_candidates' => 471,
    ];

    private const JUNK_ROLE_IDS = [8, 9, 10, 11, 12, 13, 14, 48, 49, 53, 54, 55, 56, 57, 73, 74, 75, 76];

    public function run(): void
    {
        $p = self::P;
        $ALL = self::TYPE_ALL;
        $BR = self::TYPE_BRANCH;

        // --- CEO: full access ---
        $this->buildRole('CEO', self::TPL_ADMIN, []);

        // --- GM: read-only oversight ---
        $this->buildRole('GM', self::TPL_EMPLOYEE, [
            $p['view_employees'] => $ALL, $p['view_pending_termination_employees'] => $ALL,
            $p['view_terminated_employees'] => $ALL, $p['view_payroll'] => $ALL,
            $p['view_advance_salary'] => $ALL, $p['view_company_assets'] => $ALL,
            $p['view_insurance'] => $ALL, $p['view_attendance'] => $ALL,
            $p['view_leave'] => $ALL, $p['view_candidates'] => $ALL,
        ]);

        // --- Finance Manager ---
        $this->buildRole('Finance Manager', self::TPL_EMPLOYEE, [
            $p['view_payroll'] => $ALL, $p['add_payroll'] => $ALL, $p['edit_payroll'] => $ALL, $p['delete_payroll'] => $ALL,
            $p['manage_finance_clearance'] => $ALL,
            $p['view_advance_salary'] => $ALL, $p['add_advance_salary'] => $ALL, $p['edit_advance_salary'] => $ALL,
            $p['asset_loss_deduction'] => $ALL,
            $p['view_employees'] => $ALL, $p['view_pending_termination_employees'] => $ALL, $p['view_terminated_employees'] => $ALL,
        ]);

        // --- Accountant ---
        $this->buildRole('Accountant', self::TPL_EMPLOYEE, [
            $p['view_payroll'] => $ALL, $p['add_payroll'] => $ALL,
            $p['view_advance_salary'] => $ALL, $p['asset_loss_deduction'] => $ALL,
        ]);

        // --- IT Manager ---
        $this->buildRole('IT Manager', self::TPL_EMPLOYEE, [
            $p['manage_it_clearance'] => $ALL,
            $p['view_company_assets'] => $ALL, $p['add_company_assets'] => $ALL, $p['edit_company_assets'] => $ALL,
            $p['delete_company_assets'] => $ALL, $p['assign_company_asset_to_employee'] => $ALL,
            $p['view_employees'] => $ALL, $p['view_pending_termination_employees'] => $ALL,
        ]);

        // --- GRO (Government Relations Officer) ---
        $this->buildRole('GRO', self::TPL_EMPLOYEE, [
            $p['view_employees'] => $ALL,
            $p['view_insurance'] => $ALL, $p['add_insurance'] => $ALL, $p['edit_insurance'] => $ALL,
            $p['view_pending_termination_employees'] => $ALL, $p['view_terminated_employees'] => $ALL,
        ]);

        // --- Line managers: branch view + leave approval; handover via assigned_to ---
        $lineManagerGrants = [
            $p['view_employees'] => $BR,
            $p['view_leave'] => $BR,
            $p['approve_or_reject_leaves'] => $BR,
        ];
        foreach ([
            'Business Development Manager', 'Fleet Manager', 'Project Manager',
            'Operational Manager', 'Operational Lead Supervisor', 'Operational Supervisor',
            'Facility Manager', 'Controlroom Supervisor',
        ] as $title) {
            $this->buildRole($title, self::TPL_EMPLOYEE, $lineManagerGrants);
        }

        // --- Self-service only: straight clone of employee ---
        foreach ([
            'Fleet Coordinator', 'Operational Coordinator', 'Platform Coordinator',
            'Developer', 'Tea Boy',
        ] as $title) {
            $this->buildRole($title, self::TPL_EMPLOYEE, []);
        }

        // --- Ensure the existing hr-manager carries the offboarding/clearance perms ---
        $hrManager = Role::withoutGlobalScopes()->where('name', 'hr-manager')->where('company_id', self::COMPANY_ID)->first();
        if ($hrManager) {
            foreach ([
                $p['edit_employees'], $p['view_employees'], $p['add_employees'],
                $p['manage_termination_employees'], $p['view_pending_termination_employees'], $p['view_terminated_employees'],
                $p['manage_it_clearance'], $p['manage_finance_clearance'],
            ] as $permId) {
                DB::table('permission_role')
                    ->where('role_id', $hrManager->id)->where('permission_id', $permId)
                    ->update(['permission_type_id' => $ALL]);
            }
        }

        // --- Soft-delete the DMS-import junk roles (no company, no users) ---
        DB::table('roles')
            ->whereIn('id', self::JUNK_ROLE_IDS)
            ->whereNull('deleted_at')
            ->whereNull('company_id')
            ->update(['deleted_at' => now(), 'updated_at' => now()]);

        $this->command?->info('Office roles seeded for company ' . self::COMPANY_ID . '.');
    }

    private function buildRole(string $displayName, int $templateRoleId, array $grants): void
    {
        $slug = str_slug($displayName);

        $role = Role::withoutGlobalScopes()
            ->where('name', $slug)->where('company_id', self::COMPANY_ID)->first();

        if (!$role) {
            $role = new Role();
            $role->name = $displayName;   // model setter slugs it
            $role->display_name = $displayName;
            $role->company_id = self::COMPANY_ID;
            $role->save();
        } else {
            $role->forceFill(['display_name' => $displayName, 'deleted_at' => null])->save();
        }

        // Rebuild permission_role from the template, applying the grants.
        DB::table('permission_role')->where('role_id', $role->id)->delete();

        $rows = DB::table('permission_role')->where('role_id', $templateRoleId)->get();
        $insert = $rows->map(fn ($r) => [
            'permission_id' => $r->permission_id,
            'role_id' => $role->id,
            'permission_type_id' => $grants[$r->permission_id] ?? $r->permission_type_id,
            'company_id' => self::COMPANY_ID,
        ])->all();

        foreach (array_chunk($insert, 200) as $chunk) {
            DB::table('permission_role')->insert($chunk);
        }

        $this->command?->line("  {$displayName} ({$slug}) -> " . count($insert) . ' permission rows, ' . count($grants) . ' grants');
    }
}
