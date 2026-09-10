<?php

namespace App\Support;

use App\Models\Insurance;
use App\Models\Passport;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Single source of truth for employee statutory-document expiry (passport,
 * iqama/visa, medical insurance, employment contract). Returns every item that
 * is already expired or falls due within the next 90 days, banded the same way
 * the HR compliance dashboard renders it.
 *
 * Employee "contract" expiry comes from employee_details.contract_end_date only.
 * The Worksuite `contracts` table is client/sales contracts (client_id, not an
 * employee) and must not be used here.
 */
class HrDocumentExpiry
{
    /**
     * @param  \Illuminate\Support\Collection  $employees  eager-loaded with employeeDetail + visa
     * @return \Illuminate\Support\Collection<int, array{employee: \App\Models\User, type: string, date: \Carbon\Carbon, label: string, days: int, band: string}>
     */
    public static function forEmployees(Collection $employees, int $companyId): Collection
    {
        $userIds = $employees->pluck('id');
        $today = Carbon::today();
        $items = collect();

        $passports = Passport::where('company_id', $companyId)->whereIn('user_id', $userIds)->get()->keyBy('user_id');
        $insurances = Insurance::whereIn('employee_id', $userIds)->get()->keyBy('employee_id');

        $add = function ($employee, string $type, $date, string $label) use ($items, $today) {
            if (!$date) {
                return;
            }

            $date = Carbon::parse($date);
            $days = $today->diffInDays($date, false);

            if ($days > 90) {
                return;
            }

            $band = $days < 0 ? 'expired' : ($days <= 30 ? '0-30' : ($days <= 60 ? '31-60' : '61-90'));
            $items->push(compact('employee', 'type', 'date', 'label', 'days', 'band'));
        };

        foreach ($employees as $employee) {
            $add($employee, 'passport', $passports->get($employee->id)?->expiry_date, 'Passport');
            $add($employee, 'iqama', $employee->visa->sortByDesc('expiry_date')->first()?->expiry_date, 'Iqama / Visa');
            $add($employee, 'insurance', $insurances->get($employee->id)?->expiry_date, 'Insurance');
            $add($employee, 'contract', $employee->employeeDetail?->contract_end_date, 'Contract');
        }

        return $items;
    }
}
