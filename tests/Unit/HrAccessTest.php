<?php

namespace Tests\Unit;

use App\Models\EmployeeDetails;
use App\Models\Leave;
use App\Models\User;
use App\Services\HrAccess;
use Tests\TestCase;

class HrAccessTest extends TestCase
{
    public function test_branch_scope_allows_only_the_same_branch_without_global_scope(): void
    {
        $actor = new User(['id' => 10, 'branch_id' => 6]);
        $sameBranchEmployee = new User(['id' => 20, 'branch_id' => 6]);
        $unassignedActor = new User(['id' => 11, 'branch_id' => null]);

        $this->assertTrue(HrAccess::canAccessEmployeeBranch($actor, $sameBranchEmployee, 'leave'));
        $this->assertFalse(HrAccess::canAccessEmployeeBranch($unassignedActor, $sameBranchEmployee, 'leave'));
    }

    public function test_direct_manager_can_approve_only_their_direct_report(): void
    {
        $manager = new User(['id' => 10, 'branch_id' => 1]);
        $employee = new User(['id' => 20, 'branch_id' => 2]);
        $employee->setRelation('employeeDetail', new EmployeeDetails(['reporting_to' => 10]));
        $leave = new Leave(['user_id' => 20]);
        $leave->setRelation('user', $employee);

        $this->assertTrue(HrAccess::canApproveLeave($manager, $leave, 'none'));
    }

    public function test_added_and_owned_permissions_do_not_grant_unrelated_leave_access(): void
    {
        $actor = new User(['id' => 10, 'branch_id' => null]);
        $employee = new User(['id' => 20, 'branch_id' => 2]);
        $employee->setRelation('employeeDetail', new EmployeeDetails(['reporting_to' => 99]));

        $addedLeave = new Leave(['user_id' => 20, 'added_by' => 10]);
        $addedLeave->setRelation('user', $employee);
        $unrelatedLeave = new Leave(['user_id' => 20, 'added_by' => 99]);
        $unrelatedLeave->setRelation('user', $employee);

        $this->assertTrue(HrAccess::canAccessLeave($actor, $addedLeave, 'added', false));
        $this->assertFalse(HrAccess::canAccessLeave($actor, $unrelatedLeave, 'added', false));
        $this->assertFalse(HrAccess::canAccessLeave($actor, $unrelatedLeave, 'owned', false));
    }
}
