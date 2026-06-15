<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class BioTimeService
{
    protected $db;

    public function __construct()
    {
        $this->db = DB::connection('biotime');
    }

    public function createEmployee(\App\Models\User $user, \App\Models\EmployeeDetails $details): void
    {
        // Split name into first/last
        $nameParts = explode(' ', trim($user->name), 2);
        $firstName = $nameParts[0] ?? '';
        $lastName  = $nameParts[1] ?? '';

        // Get the default company_id from BioTime
       $companyId = $this->db->table('personnel_company')
                    ->value('id');
        if (!$companyId) {
            \Log::error('BioTime: No default company found');
            return;
        }

        // Optionally map department
        $departmentId = null;
        if ($details->department_id) {
            $deptName = optional($details->department)->team_name;
            $departmentId = $this->db->table('personnel_department')
                ->where('dept_name', $deptName)
                ->where('company_id', $companyId)
                ->value('id');
        }

        // Check if already exists (avoid duplicates on re-runs)
        $exists = $this->db->table('personnel_employee')
            ->where('emp_code', $details->employee_id)
            ->exists();

        if ($exists) {
            return;
        }

        $this->db->table('personnel_employee')->insert([
            'emp_code'       => $details->employee_id,
            'first_name'     => $firstName,
            'last_name'      => $lastName,
            'email'          => $user->email,
            'mobile'         => $user->mobile,
            'hire_date'      => $details->joining_date,
            'gender'         => $user->gender ? strtoupper(substr($user->gender, 0, 1)) : null,
            'status'         => 1,
            'is_active'      => DB::raw('true'),   // boolean cast
            'enable_payroll' => DB::raw('false'),  // boolean cast
            'company_id'     => $companyId,
            'department_id'  => $departmentId,
            'create_time'    => now(),
            'create_user'    => 'hr_system',
            'update_time'    => now(),
        ]);
    }
}