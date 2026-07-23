<?php

namespace Tests\Unit;

use App\Models\EmployeeDetails;
use App\Models\HrCertification;
use App\Models\HrCertificationRule;
use App\Models\User;
use App\Services\EmployeeLifecycle;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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

    public function test_required_certification_gaps_respect_designation_and_valid_expiry(): void
    {
        Carbon::setTestNow('2026-07-23 12:00:00');
        $driver = new User(['name' => 'Driver']); $driver->id = 10;
        $driver->setRelation('employeeDetail', new EmployeeDetails(['designation_id' => 3]));
        $coordinator = new User(['name' => 'Coordinator']); $coordinator->id = 11;
        $coordinator->setRelation('employeeDetail', new EmployeeDetails(['designation_id' => 4]));

        $rules = new Collection([
            new HrCertificationRule(['designation_id' => null, 'certification_name' => 'Safety']),
            new HrCertificationRule(['designation_id' => 3, 'certification_name' => 'Driving permit']),
        ]);
        $certifications = new Collection([
            new HrCertification(['employee_id' => 10, 'name' => 'Safety', 'expires_at' => '2026-08-01', 'status' => 'valid']),
            new HrCertification(['employee_id' => 11, 'name' => 'Safety', 'expires_at' => '2026-07-01', 'status' => 'valid']),
        ]);

        $gaps = HrCertificationRule::missingForEmployees(new Collection([$driver, $coordinator]), $rules, $certifications);

        $this->assertCount(2, $gaps);
        $this->assertSame(['Driving permit'], $gaps->first()['requirements']->all());
        $this->assertSame(['Safety'], $gaps->last()['requirements']->all());
    }
}
