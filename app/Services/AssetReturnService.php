<?php

namespace App\Services;

use App\Models\AssetAssignment;
use App\Models\AssetAssignmentHistory;
use App\Models\AssetReturnForm;
use App\Models\AssetReturnFormLine;
use App\Models\CompanyAsset;
use App\Models\CompanyAssetSerial;
use App\Models\EmployeeAssessLoss;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AssetReturnService
{
    /** RT-0005-0004 inspection checklist - the single source of truth. */
    public const CHECKLIST = [
        'accessories' => [
            'Main unit', 'Power adapter / charging cable', 'Carry case / bag', 'Mouse',
            'External keyboard / docking station', 'Headset / webcam', 'Original box / packaging',
            'Company asset tag / sticker intact', 'SIM / data card',
        ],
        'technical' => [
            'Chassis / body - dents, cracks, scratches', 'Screen / display - dead pixels, marks',
            'Hinges, lid & casing alignment', 'Keyboard & touchpad function', 'Ports, Wi-Fi & connectivity',
            'Audio, camera & microphone', 'Battery health', 'Charging / adapter function',
            'Power-on / boot test & performance', 'Storage & memory - unauthorised changes',
            'Operating system & licensed software', 'General cleanliness & presentation',
        ],
        'data' => [
            'Company data backed up / transferred', 'Device wiped, formatted or re-imaged',
            'Accounts signed out / removed from domain', 'Encryption / BitLocker key recovered',
            'Email, VPN & system access revoked', 'Software licences reclaimed / reassigned',
            'No personal data left on device', 'Asset register / inventory record updated',
        ],
    ];

    public const RESULT_OPTIONS = [
        'accessories' => ['returned', 'not returned', 'n/a'],
        'technical' => ['excellent', 'good', 'fair', 'poor', 'n/a'],
        'data' => ['done', 'n/a'],
    ];
    /**
     * Formally certify a unit's return: close the assignment, release the
     * serial, and create the finalized RT return record in one transaction.
     */
    public function certifyReturn(AssetAssignment $assignment, int $actorId, array $data): AssetReturnForm
    {
        return DB::transaction(function () use ($assignment, $actorId, $data) {
            $assignment = AssetAssignment::query()->whereKey($assignment->id)->lockForUpdate()->firstOrFail();
            $asset = CompanyAsset::query()->whereKey($assignment->company_asset_id)->lockForUpdate()->firstOrFail();
            $serial = $assignment->company_asset_serial_id
                ? CompanyAssetSerial::query()->whereKey($assignment->company_asset_serial_id)->lockForUpdate()->first()
                : null;

            $history = AssetAssignmentHistory::create([
                'company_asset_id' => $asset->id,
                'company_asset_serial_id' => $assignment->company_asset_serial_id,
                'employee_id' => $assignment->employee_id,
                'serial_no' => $assignment->serialLabel(),
                'action_type' => AssetAssignmentHistory::ACTION_RETURNED,
                'qty' => 1,
                'signed_document' => $data['return_document'] ?? null,
                'asset_assignment_id' => $assignment->id,
                'added_by' => $actorId,
                'action_at' => now(),
            ]);

            if ($serial && in_array($serial->status, [CompanyAssetSerial::STATUS_ASSIGNED, CompanyAssetSerial::STATUS_PENDING])) {
                $serial->update(['status' => CompanyAssetSerial::STATUS_AVAILABLE]);
            }

            $form = $this->createForm($asset, $serial, $assignment, $history, AssetReturnForm::OUTCOME_RETURNED, $actorId, $data);

            $assignment->delete();
            $asset->syncAvailability();

            return $form;
        });
    }

    /**
     * Write off a unit as lost, damaged, or retired: create the recovery
     * recommendation, close any live assignment, and set the serial's final
     * disposition. Never returns the unit to available circulation.
     */
    public function writeOff(CompanyAssetSerial $serial, int $actorId, string $outcome, array $data): AssetReturnForm
    {
        if (!in_array($outcome, [AssetReturnForm::OUTCOME_LOST, AssetReturnForm::OUTCOME_DAMAGED, AssetReturnForm::OUTCOME_RETIRED], true)) {
            throw ValidationException::withMessages(['outcome' => 'Invalid write-off outcome.']);
        }

        return DB::transaction(function () use ($serial, $actorId, $outcome, $data) {
            $serial = CompanyAssetSerial::query()->whereKey($serial->id)->lockForUpdate()->firstOrFail();
            $asset = CompanyAsset::query()->whereKey($serial->company_asset_id)->lockForUpdate()->firstOrFail();
            $assignment = AssetAssignment::query()->where('company_asset_serial_id', $serial->id)->lockForUpdate()->first();

            $history = AssetAssignmentHistory::create([
                'company_asset_id' => $asset->id,
                'company_asset_serial_id' => $serial->id,
                'employee_id' => $assignment?->employee_id ?? ($data['employee_id'] ?? null),
                'serial_no' => $serial->serial_no,
                'action_type' => AssetAssignmentHistory::ACTION_WRITTEN_OFF,
                'qty' => 1,
                'added_by' => $actorId,
                'action_at' => now(),
            ]);

            $serial->update(['status' => $outcome]);

            $form = $this->createForm($asset, $serial, $assignment, $history, $outcome, $actorId, $data);

            $assignment?->delete();
            $asset->syncAvailability();

            if (!empty($data['recommended_recovery_amount']) && $history->employee_id) {
                $assessLoss = EmployeeAssessLoss::create([
                    'company_asset_id' => $asset->id,
                    'employee_id' => $history->employee_id,
                    'asset_assignment_history_id' => $history->id,
                    'loss_amount' => $data['recommended_recovery_amount'],
                    'status' => EmployeeAssessLoss::STATUS_PENDING,
                ]);

                $form->update(['snapshot' => array_merge($form->snapshot ?? [], ['employee_assess_loss_id' => $assessLoss->id])]);
            }

            return $form;
        });
    }

    private function createForm(CompanyAsset $asset, ?CompanyAssetSerial $serial, ?AssetAssignment $assignment, AssetAssignmentHistory $history, string $outcome, int $actorId, array $data): AssetReturnForm
    {
        $form = AssetReturnForm::create([
            'company_id' => company()->id,
            'company_asset_id' => $asset->id,
            'company_asset_serial_id' => $serial?->id,
            'asset_assignment_id' => $assignment?->id,
            'asset_assignment_history_id' => $history->id,
            'employee_id' => $history->employee_id,
            'outcome' => $outcome,
            'accessories_checklist' => $data['accessories_checklist'] ?? null,
            'technical_inspection' => $data['technical_inspection'] ?? null,
            'data_clearance_notes' => $data['data_clearance_notes'] ?? null,
            'disposition_notes' => $data['disposition_notes'] ?? null,
            'recommended_recovery_amount' => $data['recommended_recovery_amount'] ?? null,
            'recovery_reason' => $data['recovery_reason'] ?? null,
            'status' => AssetReturnForm::STATUS_FINAL,
            'certified_by' => $actorId,
            'certified_at' => now(),
            'evidence_path' => $data['evidence_path'] ?? null,
            'signed_document' => $data['return_document'] ?? null,
        ]);

        $form->update(['reference' => 'RT-' . str_pad((string) $form->id, 6, '0', STR_PAD_LEFT)]);

        $lineSummary = $this->persistLines($form, $data['lines'] ?? []);
        // Keep the free-text columns as a rendered summary of the structured rows
        // so existing readers and the snapshot still carry the detail.
        $form->update([
            'accessories_checklist' => $data['accessories_checklist'] ?? ($lineSummary['accessories'] ?: null),
            'technical_inspection' => $data['technical_inspection'] ?? ($lineSummary['technical'] ?: null),
            'data_clearance_notes' => $data['data_clearance_notes'] ?? ($lineSummary['data'] ?: null),
        ]);

        $form->update(['snapshot' => [
            'reference' => $form->reference,
            'company_id' => $form->company_id,
            'asset_id' => $form->company_asset_id,
            'asset_name' => $asset->name,
            'serial_id' => $serial?->id,
            'serial_no' => $serial?->serial_no,
            'employee_id' => $form->employee_id,
            'employee_name' => $history->employee?->name,
            'outcome' => $outcome,
            'accessories_checklist' => $form->accessories_checklist,
            'technical_inspection' => $form->technical_inspection,
            'data_clearance_notes' => $form->data_clearance_notes,
            'disposition_notes' => $form->disposition_notes,
            'recommended_recovery_amount' => $form->recommended_recovery_amount,
            'recovery_reason' => $form->recovery_reason,
            'lines' => $form->lines()->get(['section', 'label', 'result', 'remarks'])->toArray(),
            'certified_by' => $actorId,
            'certified_at' => optional($form->certified_at)->toIso8601String(),
        ]]);

        return $form->fresh(['lines']);
    }

    /**
     * Persist the structured inspection rows and return a "Label: result (remarks)"
     * one-line summary per section for the free-text fallback.
     *
     * @param  array<string, array<int, array{result?: string, remarks?: string}>>  $input
     * @return array{accessories: string, technical: string, data: string}
     */
    private function persistLines(AssetReturnForm $form, array $input): array
    {
        $summary = ['accessories' => [], 'technical' => [], 'data' => []];

        foreach (self::CHECKLIST as $section => $labels) {
            $rows = $input[$section] ?? [];
            foreach ($labels as $i => $label) {
                $result = trim((string) ($rows[$i]['result'] ?? ''));
                $remarks = trim((string) ($rows[$i]['remarks'] ?? ''));
                if ($result === '' && $remarks === '') {
                    continue;
                }

                AssetReturnFormLine::create([
                    'asset_return_form_id' => $form->id,
                    'section' => $section,
                    'label' => $label,
                    'result' => $result ?: null,
                    'remarks' => $remarks ?: null,
                    'sort_order' => $i,
                ]);

                $summary[$section][] = $label . ': ' . ($result ?: '—') . ($remarks !== '' ? ' (' . $remarks . ')' : '');
            }
        }

        return [
            'accessories' => implode('; ', $summary['accessories']),
            'technical' => implode('; ', $summary['technical']),
            'data' => implode('; ', $summary['data']),
        ];
    }
    public function generatePdf(AssetReturnForm $form): AssetReturnForm
    {
        return DB::transaction(function () use ($form) {
            $form = AssetReturnForm::query()
                ->with([
                    'asset.department', 'asset.branch',
                    'serial',
                    'employee.employeeDetail.designation',
                    'employee.employeeDetail.department',
                    'employee.branch',
                    'certifiedBy', 'recoveryApprovedBy',
                    'allocations', 'lines',
                ])
                ->whereKey($form->id)->lockForUpdate()->firstOrFail();
            if ($form->status !== AssetReturnForm::STATUS_FINAL) {
                throw ValidationException::withMessages(['asset_return' => 'Only finalized RT records can generate a PDF.']);
            }
            if ($form->pdf_path && $form->document_hash) {
                return $form;
            }
            $bytes = Pdf::loadView('company-assets.rt-final-pdf', [
                'form' => $form,
                'company' => function_exists('company') ? company() : null,
            ])->setPaper('letter')->output();
            $path = 'asset-returns/' . $form->company_id . '/' . $form->reference . '.pdf';
            Storage::put($path, $bytes);
            $form->update(['pdf_path' => $path, 'document_hash' => hash('sha256', $bytes)]);
            return $form->fresh();
        });
    }
}
