<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class HrCertificationRule extends BaseModel
{
    protected $table = 'hr_certification_rules';
    protected $guarded = ['id'];

    public function designation()
    {
        return $this->belongsTo(Designation::class);
    }

    /** Return employees whose applicable active rules lack a valid record. */
    public static function missingForEmployees(Collection $employees, Collection $rules, Collection $certifications): Collection
    {
        return $employees->map(function ($employee) use ($rules, $certifications) {
            $designationId = optional($employee->employeeDetail)->designation_id;
            $required = $rules->filter(fn ($rule) => is_null($rule->designation_id) || (int) $rule->designation_id === (int) $designationId)
                ->pluck('certification_name')->unique(fn ($name) => Str::lower(trim($name)));
            $validNames = $certifications->where('employee_id', $employee->id)
                ->filter(fn ($certification) => $certification->status !== 'expired' && (is_null($certification->expires_at) || $certification->expires_at->gte(today())))
                ->map(fn ($certification) => Str::lower(trim($certification->name)));
            $missing = $required->filter(fn ($name) => ! $validNames->contains(Str::lower(trim($name))))->values();

            return ['employee' => $employee, 'requirements' => $missing];
        })->filter(fn ($gap) => $gap['requirements']->isNotEmpty())->values();
    }
}
