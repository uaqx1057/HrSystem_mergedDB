<?php

namespace App\Services;

use App\Models\AssetRecoveryAllocation;
use App\Models\AssetReturnForm;
use App\Models\EmployeeAssessLoss;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AssetRecoveryWaiverService
{
    public function waive(AssetReturnForm $form, int $actorId, string $reason): AssetReturnForm
    {
        return DB::transaction(function () use ($form, $actorId, $reason) {
            $form = AssetReturnForm::query()->whereKey($form->id)->lockForUpdate()->firstOrFail();
            $remaining = max(0, (float) $form->recommended_recovery_amount - (float) $form->allocations()->sum('amount'));

            if ($form->status !== AssetReturnForm::STATUS_FINAL || $remaining <= 0) {
                throw ValidationException::withMessages(['recovery' => 'Only a finalized RT with a remaining recovery amount can be waived.']);
            }

            if ($form->recovery_status === 'waived') {
                throw ValidationException::withMessages(['recovery' => 'This recovery has already been waived.']);
            }

            $lossId = data_get($form->snapshot, 'employee_assess_loss_id');

            AssetRecoveryAllocation::create([
                'asset_return_form_id' => $form->id,
                'employee_assess_loss_id' => $lossId,
                'method' => AssetRecoveryAllocation::METHOD_WAIVER,
                'amount' => $remaining,
                'approved_by' => $actorId,
                'approved_at' => now(),
                'notes' => $reason,
            ]);

            // Waiving forgives the obligation - it resolves the loss but no money
            // is ever taken, so deducted_amount is left untouched. The waiver
            // allocation row above is the ledger record of how it was cleared.
            if ($lossId) {
                EmployeeAssessLoss::query()
                    ->whereKey($lossId)
                    ->where('status', EmployeeAssessLoss::STATUS_PENDING)
                    ->update(['status' => EmployeeAssessLoss::STATUS_SETTLED]);
            }

            $form->update([
                'recovery_status' => 'waived',
                'approved_recovery_amount' => 0,
                'recovery_approved_by' => $actorId,
                'recovery_approved_at' => now(),
                'recovery_reason' => $reason,
            ]);

            return $form->fresh(['allocations']);
        });
    }
}
