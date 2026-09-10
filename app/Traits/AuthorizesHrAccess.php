<?php

namespace App\Traits;

use App\Services\HrAccess;
use App\Models\User;

trait AuthorizesHrAccess
{
    protected function authorizeHrPermission(string $permission, array $allowed): string
    {
        $value = user()->permission($permission);
        abort_403(!in_array($value, $allowed, true));

        return $value;
    }

    protected function authorizeEmployeeBranch(User $employee, string $module, string $permission = 'edit_employees'): void
    {
        $value = user()->permission($permission);
        abort_403($value !== 'all' && !($value === 'branch' && HrAccess::canAccessEmployeeBranch(user(), $employee, $module)));
    }
}
