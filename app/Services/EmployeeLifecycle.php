<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

final class EmployeeLifecycle
{
    public static function summary(User $employee): array
    {
        $detail = $employee->employeeDetail;
        $required = [
            'Employee ID' => $detail?->employee_id,
            'Email' => $employee->email,
            'Branch' => $employee->branch_id,
            'Department' => $detail?->department_id,
            'Designation' => $detail?->designation_id,
            'Joining date' => $detail?->joining_date,
        ];

        if (($detail?->employee_type ?? 'expat') === 'saudi') {
            $required += ['National ID' => $detail?->national_id, 'National ID expiry' => $detail?->national_id_expiry_date];
        } else {
            $required += ['Iqama number' => $detail?->iqama_no, 'Iqama profession' => $detail?->iqama_profession, 'Iqama expiry' => $detail?->iqama_expiry_date];
        }

        $missing = collect($required)->filter(fn ($value) => blank($value))->keys()->values();
        $total = count($required);
        $completed = $total - $missing->count();

        $documents = collect([
            ['label' => 'Iqama', 'date' => $detail?->iqama_expiry_date],
            ['label' => 'National ID', 'date' => $detail?->national_id_expiry_date],
            ['label' => 'Passport', 'date' => $detail?->passport_expiry_date],
        ])->filter(fn ($document) => filled($document['date']))->map(function ($document) {
            $date = Carbon::parse($document['date'])->startOfDay();
            return $document + ['days_remaining' => now()->startOfDay()->diffInDays($date, false)];
        })->sortBy('days_remaining')->values();

        return [
            'percentage' => $total ? (int) round(($completed / $total) * 100) : 0,
            'missing' => $missing,
            'documents' => $documents,
        ];
    }
}
