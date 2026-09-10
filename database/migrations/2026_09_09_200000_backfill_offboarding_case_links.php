<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Link legacy pending employee_terminations to canonical hr_offboarding_cases.
 *
 * Rules (from the implementation plan):
 *  - Only unambiguous links: a pending termination gets the single unlinked open
 *    case for that employee, or a fresh case is created 1:1 when none exists.
 *  - Never guess when there is more than one candidate case; those are written to
 *    hr_lifecycle_events as `offboarding_backfill_exception` and left for HR.
 *
 * Idempotent: only touches terminations whose offboarding_case_id is still NULL.
 * down() only unlinks the rows this migration created a case for.
 */
return new class extends Migration
{
    public function up(): void
    {
        $pending = DB::table('employee_terminations')
            ->where('status', 'pending')
            ->whereNull('offboarding_case_id')
            ->orderBy('id')
            ->get();

        $linked = 0;
        $created = 0;
        $exceptions = 0;

        foreach ($pending as $termination) {
            $linkedCaseIds = DB::table('employee_terminations')
                ->whereNotNull('offboarding_case_id')
                ->pluck('offboarding_case_id')
                ->all();

            $openCases = DB::table('hr_offboarding_cases')
                ->where('employee_id', $termination->user_id)
                ->whereIn('status', ['open', 'completion_pending'])
                ->when(!empty($linkedCaseIds), fn ($q) => $q->whereNotIn('id', $linkedCaseIds))
                ->orderBy('id')
                ->get();

            if ($openCases->count() > 1) {
                $exceptions++;
                DB::table('hr_lifecycle_events')->insert([
                    'subject_user_id' => $termination->user_id,
                    'company_id' => $termination->company_id,
                    'event' => 'offboarding_backfill_exception',
                    'actor_id' => null,
                    'meta' => json_encode([
                        'termination_id' => $termination->id,
                        'candidate_case_ids' => $openCases->pluck('id')->all(),
                        'note' => 'Multiple open cases; linked manually by HR.',
                    ]),
                    'created_at' => now(),
                ]);
                continue;
            }

            if ($openCases->count() === 1) {
                $case = $openCases->first();
                DB::table('employee_terminations')->where('id', $termination->id)
                    ->update(['offboarding_case_id' => $case->id]);
                DB::table('hr_offboarding_cases')->where('id', $case->id)->update([
                    'approval_status' => 'approved',
                    'approved_at' => $case->approved_at ?? now(),
                    'reason' => $case->reason ?: $termination->reason,
                    'last_working_date' => $case->last_working_date ?: $termination->last_working_date,
                    'resignation_date' => $case->resignation_date ?: $termination->resignation_date,
                    'reference' => $case->reference ?: ('HR-' . str_pad((string) $case->id, 6, '0', STR_PAD_LEFT)),
                    'updated_at' => now(),
                ]);
                $linked++;
                continue;
            }

            $caseId = DB::table('hr_offboarding_cases')->insertGetId([
                'company_id' => $termination->company_id,
                'employee_id' => $termination->user_id,
                'exit_type' => $termination->exit_type ?: 'termination',
                'reason' => $termination->reason ?: ($termination->terminate_reason ?: 'Legacy termination (backfilled).'),
                'resignation_date' => $termination->resignation_date,
                // last_working_date is NOT NULL; legacy rows may not have set it.
                'last_working_date' => $termination->last_working_date
                    ?: ($termination->created_at ?: now()),
                'status' => 'open',
                'approval_status' => 'approved',
                'approved_at' => now(),
                'initiated_by' => $termination->initiated_by,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('hr_offboarding_cases')->where('id', $caseId)->update([
                'reference' => 'HR-' . str_pad((string) $caseId, 6, '0', STR_PAD_LEFT),
            ]);

            DB::table('employee_terminations')->where('id', $termination->id)
                ->update(['offboarding_case_id' => $caseId]);
            $created++;
        }

        Log::info("[offboarding backfill] pending={$pending->count()} linked={$linked} created={$created} exceptions={$exceptions}");
    }

    public function down(): void
    {
        // Unlink terminations from cases that carry no tasks and no settlement -
        // i.e. cases this migration is likely to have created. Leave anything a
        // human has since worked on untouched.
        $caseIds = DB::table('hr_offboarding_cases as c')
            ->leftJoin('hr_offboarding_tasks as t', 't.case_id', '=', 'c.id')
            ->whereNull('t.id')
            ->pluck('c.id')
            ->all();

        if (!empty($caseIds)) {
            DB::table('employee_terminations')->whereIn('offboarding_case_id', $caseIds)
                ->update(['offboarding_case_id' => null]);
            DB::table('hr_offboarding_cases')->whereIn('id', $caseIds)->delete();
        }
    }
};
