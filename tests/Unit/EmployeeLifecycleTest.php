<?php

namespace Tests\Unit;

use App\Models\EmployeeDetails;
use App\Models\User;
use App\Services\EmployeeLifecycle;
use Carbon\Carbon;
use Tests\TestCase;

class EmployeeLifecycleTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_expat_profile_reports_required_iqama_fields_and_expiry(): void
    {
        Carbon::setTestNow('2026-07-23 12:00:00');
        $employee = new User(['email' => 'employee@example.test', 'branch_id' => 1]);
        $employee->setRelation('employeeDetail', new EmployeeDetails([
            'employee_id' => 'E-100', 'department_id' => 2, 'designation_id' => 3,
            'joining_date' => '2026-01-01', 'employee_type' => 'expat',
            'iqama_no' => '123', 'iqama_profession' => 'Driver',
            'iqama_expiry_date' => '2026-08-02', 'passport_expiry_date' => '2027-01-01',
        ]));

        $summary = EmployeeLifecycle::summary($employee);

        $this->assertSame(100, $summary['percentage']);
        $this->assertCount(0, $summary['missing']);
        $this->assertSame('Iqama', $summary['documents']->first()['label']);
        $this->assertSame(10, $summary['documents']->first()['days_remaining']);
    }

    public function test_saudi_profile_requires_national_id_not_iqama(): void
    {
        $employee = new User(['email' => 'saudi@example.test', 'branch_id' => 1]);
        $employee->setRelation('employeeDetail', new EmployeeDetails([
            'employee_id' => 'S-100', 'department_id' => 2, 'designation_id' => 3,
            'joining_date' => '2026-01-01', 'employee_type' => 'saudi',
        ]));

        $summary = EmployeeLifecycle::summary($employee);

        $this->assertContains('National ID', $summary['missing']->all());
        $this->assertContains('National ID expiry', $summary['missing']->all());
        $this->assertNotContains('Iqama number', $summary['missing']->all());
    }
}
