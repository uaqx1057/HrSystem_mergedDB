<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Turns the notice_type / notice_months inputs captured when an exit is started
 * into concrete dates. Shared by the HR "Start Termination", the employee
 * "Submit Resignation" flow, the hr-lifecycle start actions and the console's
 * "edit exit terms".
 *
 *  - immediate : last working day = the base date, no notice start.
 *  - notice    : notice starts on the base date, last working day = +N months
 *                (N in {1,2,3}).
 *
 * base date = the resignation date for a resignation, today for a termination.
 */
class NoticeTerms
{
    /**
     * @return array{notice_type:string, notice_months:?int, notice_start_date:?string, last_working_date:string}
     */
    public static function resolve(?string $type, $months, ?string $baseDate): array
    {
        $base = $baseDate ? Carbon::parse($baseDate) : Carbon::today();

        if ($type === 'immediate') {
            return [
                'notice_type' => 'immediate',
                'notice_months' => null,
                'notice_start_date' => null,
                'last_working_date' => $base->toDateString(),
            ];
        }

        $months = (int) $months;
        $months = in_array($months, [1, 2, 3], true) ? $months : 1;

        return [
            'notice_type' => 'notice',
            'notice_months' => $months,
            'notice_start_date' => $base->toDateString(),
            'last_working_date' => $base->copy()->addMonths($months)->toDateString(),
        ];
    }
}
