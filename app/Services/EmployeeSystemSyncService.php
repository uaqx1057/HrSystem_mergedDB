<?php

namespace App\Services;

use App\Models\EmployeeSystemAccess;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pushes the HR-side profile (users + employee_details) into any linked DMS/DOBS
 * account so the two systems don't drift apart after the initial grant. Safe to
 * call repeatedly - it only touches rows for currently-active EmployeeSystemAccess
 * links, matched via system_user_id. designations/branches/teams are shared tables
 * in this merged DB, so copying the raw IDs across is valid (not a foreign ID space).
 */
class EmployeeSystemSyncService
{
    public function syncEmployeeProfileToLinkedSystems(User $hrUser): void
    {
        $employeeDetail = $hrUser->employeeDetail;

        $accesses = EmployeeSystemAccess::where('employee_id', $hrUser->id)
            ->where('is_active', true)
            ->get();

        foreach ($accesses as $access) {
            try {
                if ($access->system === 'dms') {
                    DB::table('users')->where('id', $access->system_user_id)->update([
                        'salutation'               => $hrUser->salutation,
                        'name'                     => $hrUser->name,
                        'email'                    => $hrUser->email,
                        'mobile'                   => $hrUser->mobile,
                        'gender'                   => $hrUser->gender,
                        'dob'                      => optional($employeeDetail)->date_of_birth,
                        'marital_status'           => $hrUser->marital_status,
                        'designation_id'           => optional($employeeDetail)->designation_id,
                        'department_id'            => optional($employeeDetail)->department_id,
                        'branch_id'                => $hrUser->branch_id,
                        'country_id'               => $hrUser->country_id,
                        'joining_date'             => optional($employeeDetail)->joining_date,
                        'address'                  => optional($employeeDetail)->address,
                        'about'                    => optional($employeeDetail)->about_me,
                        'image'                    => $hrUser->image,
                        'probation_end_date'       => optional($employeeDetail)->probation_end_date,
                        'notice_period_start_date' => optional($employeeDetail)->notice_period_start_date,
                        'notice_period_end_date'   => optional($employeeDetail)->notice_period_end_date,
                        'is_login_allowed'         => strtolower((string) $hrUser->status) === 'active' ? 1 : 0,
                        'updated_at'               => now(),
                    ]);
                } elseif ($access->system === 'dobs') {
                    $designationName = optional($employeeDetail)->designation_id
                        ? DB::table('designations')->where('id', $employeeDetail->designation_id)->value('name')
                        : null;
                    $branchName = $hrUser->branch_id
                        ? DB::table('branches')->where('id', $hrUser->branch_id)->value('name')
                        : null;

                    $dobsUpdate = ['name' => $hrUser->name, 'email' => $hrUser->email];
                    if ($designationName) $dobsUpdate['designation'] = $designationName;
                    if ($branchName) $dobsUpdate['branch_city'] = $branchName;

                    DB::table('dobs_user')->where('id', $access->system_user_id)->update($dobsUpdate);
                }
            } catch (\Throwable $e) {
                Log::error("Failed to sync employee #{$hrUser->id} profile to {$access->system}: " . $e->getMessage());
            }
        }
    }
}
