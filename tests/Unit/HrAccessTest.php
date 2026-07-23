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
        $actor = new User(['branch_id' => 6]); $actor->id = 10;
        $sameBranchEmployee = new User(['branch_id' => 6]); $sameBranchEmployee->id = 20;
        $unassignedActor = new User(['branch_id' => null]); $unassignedActor->id = 11;

        $this->assertTrue(HrAccess::canAccessEmployeeBranch($actor, $sameBranchEmployee, 'leave'));
        $this->assertFalse(HrAccess::canAccessEmployeeBranch($unassignedActor, $sameBranchEmployee, 'leave'));
    }

    public function test_direct_manager_can_approve_only_their_direct_report(): void
    {
        $manager = new User(['branch_id' => 1]); $manager->id = 10;
        $employee = new User(['branch_id' => 2]); $employee->id = 20;
        $detail = new EmployeeDetails(); $detail->reporting_to = 10;
        $employee->setRelation('employeeDetail', $detail);
        $leave = new Leave(['user_id' => 20]);
        $leave->setRelation('user', $employee);

        $this->assertTrue(HrAccess::canApproveLeave($manager, $leave, 'owned'));
    }

    public function test_added_and_owned_permissions_do_not_grant_unrelated_leave_access(): void
    {
        $actor = new User(['branch_id' => null]); $actor->id = 10;
        $employee = new User(['branch_id' => 2]); $employee->id = 20;
        $detail = new EmployeeDetails(); $detail->reporting_to = 99;
        $employee->setRelation('employeeDetail', $detail);

        $addedLeave = new Leave(['user_id' => 20, 'added_by' => 10]);
        $addedLeave->setRelation('user', $employee);
        $unrelatedLeave = new Leave(['user_id' => 20, 'added_by' => 99]);
        $unrelatedLeave->setRelation('user', $employee);

        $this->assertTrue(HrAccess::canAccessLeave($actor, $addedLeave, 'added', false));
        $this->assertFalse(HrAccess::canAccessLeave($actor, $unrelatedLeave, 'added', false));
        $this->assertFalse(HrAccess::canAccessLeave($actor, $unrelatedLeave, 'owned', false));
    }
}
