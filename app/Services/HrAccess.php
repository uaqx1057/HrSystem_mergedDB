<?php

namespace App\Services;

use App\Models\EmployeeDetails;
use App\Models\HrAccessScope;
use App\Models\HrLeaveApproverDelegation;
use App\Models\Leave;
use App\Models\User;

/**
 * HR-only authorization helper.
 *
 * Cross-branch access is granted explicitly through hr_access_scopes. A
 * branch ID, including Head Office, never grants global access by itself.
 */
final class HrAccess
{
    public static function hasAllBranchAccess(User $actor, string $module): bool
    {
        if (empty($actor->company_id)) {
            return false;
        }

        return HrAccessScope::query()
            ->where('company_id', $actor->company_id)
            ->where('user_id', $actor->id)
            ->where('module', $module)
            ->where('scope', 'all')
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->exists();
    }

    public static function canAccessEmployeeBranch(User $actor, User $employee, string $module): bool
    {
        return !is_null($actor->branch_id)
            && ($actor->branch_id === $employee->branch_id || self::hasAllBranchAccess($actor, $module));
    }

    public static function isDirectManager(User $actor, User $employee): bool
    {
        $detail = $employee->relationLoaded('employeeDetail')
            ? $employee->employeeDetail
            : EmployeeDetails::where('user_id', $employee->id)->first();

        return $detail && (int) $detail->reporting_to === (int) $actor->id;
    }

    public static function isDelegatedLeaveApprover(User $actor, User $employee): bool
    {
        $detail = $employee->relationLoaded('employeeDetail') ? $employee->employeeDetail : EmployeeDetails::where('user_id', $employee->id)->first();

        return $detail && $detail->reporting_to && HrLeaveApproverDelegation::query()
            ->where('company_id', $employee->company_id)->where('manager_id', $detail->reporting_to)
            ->where('delegate_id', $actor->id)->where('is_active', true)
            ->whereDate('starts_at', '<=', today())->whereDate('ends_at', '>=', today())->exists();
    }

    public static function canActAsLeaveManager(User $actor, User $employee): bool
    {
        return self::isDirectManager($actor, $employee) || self::isDelegatedLeaveApprover($actor, $employee);
    }

    public static function canAccessLeave(User $actor, Leave $leave, string|bool $permission, bool $allowDirectManager = true): bool
    {
        $employee = $leave->relationLoaded('user') ? $leave->user : $leave->user()->first();

        if (!$employee) {
            return false;
        }

        if ($permission === 'all') {
            return true;
        }

        if ($permission === 'branch' && self::canAccessEmployeeBranch($actor, $employee, 'leave')) {
            return true;
        }

        if ($permission === 'added' && (int) $leave->added_by === (int) $actor->id) {
            return true;
        }

        if ($permission === 'owned' && (int) $leave->user_id === (int) $actor->id) {
            return true;
        }

        if ($permission === 'both' && ((int) $leave->user_id === (int) $actor->id || (int) $leave->added_by === (int) $actor->id)) {
            return true;
        }

        return $allowDirectManager && self::canActAsLeaveManager($actor, $employee);
    }

    public static function canApproveLeave(User $actor, Leave $leave, string|bool $permission): bool
    {
        if ($permission === 'none' || !$permission) {
            return false;
        }

        $employee = $leave->relationLoaded('user') ? $leave->user : $leave->user()->first();

        if (!$employee) {
            return false;
        }

        if (self::canActAsLeaveManager($actor, $employee)) {
            return true;
        }

        return $permission === 'all'
            || ($permission === 'branch' && self::canAccessEmployeeBranch($actor, $employee, 'leave'));
    }
}
