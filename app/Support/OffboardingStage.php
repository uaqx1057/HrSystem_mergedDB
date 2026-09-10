<?php

namespace App\Support;

use App\Models\EmployeeTermination;
use App\Models\HrOffboardingCase;

/**
 * Derives the current stage of an offboarding / termination / resignation from
 * the case + termination + settlement state, for the employee-list "Stage"
 * column, the pending-termination screen strip and the offboarding console.
 */
class OffboardingStage
{
    /**
     * @return array{
     *   index:int, key:string, label:string, badge:string,
     *   steps: array<int, array{key:string,label:string,state:string}>
     * }
     */
    public static function for(EmployeeTermination $termination): array
    {
        // A completed exit is terminal - skip the per-row gate queries.
        if ($termination->status === EmployeeTermination::STATUS_COMPLETED) {
            $steps = [];
            foreach (['Requested', 'Approved', 'Clearances 3/3', 'Settlement', 'Access revoked', 'Ready to complete', 'Completed'] as $i => $label) {
                $steps[] = ['key' => 'k' . $i, 'label' => $label, 'state' => 'done'];
            }
            return ['index' => 7, 'key' => 'completed', 'label' => 'Completed', 'badge' => 'success', 'steps' => $steps];
        }

        $case = $termination->relationLoaded('offboardingCase')
            ? $termination->offboardingCase
            : ($termination->offboarding_case_id
                ? HrOffboardingCase::find($termination->offboarding_case_id)
                : null);

        $completed     = false;
        $approved      = $case ? $case->approval_status === 'approved' : true; // legacy cases had no gate
        $itIssued      = $termination->it_clearance_status === 'issued';
        $finIssued     = $termination->finance_clearance_status === 'issued';
        $hrIssued      = $case ? ($case->hr_clearance_status ?? 'pending') === 'issued' : false;
        $clearedCount  = (int) $itIssued + (int) $finIssued + (int) $hrIssued;
        $clearancesOk  = $clearedCount === 3;
        $settlementOk  = Clearance::finalSettlement($termination) !== null;
        $accessRevoked = $case ? (bool) $case->access_revoked_at : false;
        $tasksOk       = $case
            ? !$case->tasks()->where('is_required', true)->whereNotIn('status', ['completed', 'waived'])->exists()
            : true;
        $readyToComplete = $approved && $clearancesOk && $settlementOk && $accessRevoked && $tasksOk;

        $steps = [
            ['key' => 'requested',  'label' => 'Requested',         'done' => true],
            ['key' => 'approved',   'label' => 'Approved',          'done' => $approved],
            ['key' => 'clearances', 'label' => "Clearances {$clearedCount}/3", 'done' => $clearancesOk],
            ['key' => 'settlement', 'label' => 'Settlement',        'done' => $settlementOk],
            ['key' => 'access',     'label' => 'Access revoked',    'done' => $accessRevoked],
            ['key' => 'ready',      'label' => 'Ready to complete', 'done' => $readyToComplete],
            ['key' => 'completed',  'label' => 'Completed',         'done' => $completed],
        ];

        // Current stage = first not-done step (or "completed" when finished).
        $index = 7;
        foreach ($steps as $i => $step) {
            if (!$step['done']) { $index = $i + 1; break; }
        }
        if ($completed) { $index = 7; }

        $out = [];
        foreach ($steps as $i => $step) {
            $state = $step['done'] ? 'done' : 'todo';
            if (($i + 1) === $index && !$completed) { $state = 'current'; }
            $out[] = ['key' => $step['key'], 'label' => $step['label'], 'state' => $state];
        }

        $current = $steps[$index - 1];

        return [
            'index' => $index,
            'key'   => $current['key'],
            'label' => $completed ? 'Completed' : $current['label'],
            'badge' => $completed ? 'success' : ($readyToComplete ? 'primary' : 'warning'),
            'steps' => $out,
        ];
    }
}
