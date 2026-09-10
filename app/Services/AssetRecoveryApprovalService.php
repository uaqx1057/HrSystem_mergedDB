<?php

namespace App\Services;

use App\Models\AssetRecoveryAllocation;
use App\Models\AssetReturnForm;
use App\Models\EmployeeAssessLoss;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetRecoveryApprovalService
{
    public function approve(AssetReturnForm $form, int $actorId, float $amount, string $notes = ''): AssetReturnForm
    {
        return DB::transaction(function () use ($form, $actorId, $amount, $notes) {
            $form = AssetReturnForm::query()->whereKey($form->id)->lockForUpdate()->firstOrFail();
            $recommended = (float) ($form->recommended_recovery_amount ?? 0);
            $allocated = (float) $form->allocations()->sum('amount');

            if ($form->status !== AssetReturnForm::STATUS_FINAL || $recommended <= 0) {
                throw ValidationException::withMessages(['recovery' => 'Only finalized RT records with a recovery recommendation can be approved.']);
            }

            if ($amount <= 0 || $amount > $recommended - $allocated) {
                throw ValidationException::withMessages(['amount' => 'Approved recovery exceeds the remaining recommended amount.']);
            }

            $remainingAfter = $recommended - $allocated - $amount;
            $form->update([
                'recovery_status' => $remainingAfter > 0 ? 'partially_approved' : 'approved',
                'approved_recovery_amount' => $amount,
                'recovery_approved_by' => $actorId,
                'recovery_approved_at' => now(),
            ]);

            $lossId = data_get($form->snapshot, 'employee_assess_loss_id');

            AssetRecoveryAllocation::create([
                'asset_return_form_id' => $form->id,
                'employee_assess_loss_id' => $lossId,
                'method' => AssetRecoveryAllocation::METHOD_SETTLEMENT,
                'amount' => $amount,
                'approved_by' => $actorId,
                'approved_at' => now(),
                'notes' => $notes ?: null,
            ]);

            // The allocation ledger is the single source of truth for what has
            // been scheduled for recovery. Resolve the underlying loss only once
            // the whole recommended amount is covered, and never move
            // deducted_amount here - that tracks money actually taken by payroll
            // or the final settlement, so a payroll run cannot recover it twice.
            if ($lossId && $remainingAfter <= 0) {
                EmployeeAssessLoss::query()
                    ->whereKey($lossId)
                    ->where('status', EmployeeAssessLoss::STATUS_PENDING)
                    ->update(['status' => EmployeeAssessLoss::STATUS_SETTLED]);
            }

            return $form->fresh(['allocations']);
        });
    }
}
