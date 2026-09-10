<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\HrAssetCustodyRecord;
use App\Services\AssetReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HrAssetCustodyCertificationController extends AccountBaseController
{
    public function index()
    {
        $this->authorizeIt();
        $query = HrAssetCustodyRecord::with(['assignment.asset', 'assignment.employee'])
            ->whereNotNull('returned_at')
            ->whereNull('certified_at');
        if (user()->permission('manage_it_clearance') === 'branch') {
            $query->whereHas('assignment.asset', fn ($assetQuery) => $assetQuery->where('branch_id', user()->branch_id));
        }
        $this->records = $query->latest('returned_at')->paginate(25);

        return view('hr-asset-custody.certifications', $this->data);
    }

    public function certify(Request $request, $assignmentId)
    {
        $this->authorizeIt();
        $data = $request->validate([
            'lines' => 'nullable|array',
            'lines.*.*.result' => 'nullable|string|max:40',
            'lines.*.*.remarks' => 'nullable|string|max:500',
            'technical_inspection' => 'nullable|string|max:2000',
            'data_clearance_notes' => 'nullable|string|max:2000',
            'certification_notes' => 'nullable|string|max:2000',
        ]);

        $result = DB::transaction(function () use ($assignmentId, $data) {
            $assignment = AssetAssignment::with(['asset', 'serial', 'employee'])
                ->whereKey($assignmentId)
                ->lockForUpdate()
                ->firstOrFail();
            $record = HrAssetCustodyRecord::query()
                ->where('asset_assignment_id', $assignment->id)
                ->whereNotNull('returned_at')
                ->whereNull('certified_at')
                ->lockForUpdate()
                ->firstOrFail();

            $form = app(AssetReturnService::class)->certifyReturn($assignment, user()->id, [
                'return_document' => $record->evidence_path,
                'lines' => $data['lines'] ?? [],
                // Fallbacks - only used when no structured rows are submitted for
                // that section (see AssetReturnService::createForm).
                'technical_inspection' => $data['technical_inspection'] ?? null,
                'data_clearance_notes' => $data['data_clearance_notes'] ?? null,
                'disposition_notes' => $data['certification_notes'] ?? null,
            ]);

            $record->update([
                'asset_return_form_id' => $form->id,
                'certified_by' => user()->id,
                'certified_at' => now(),
                'certification_notes' => $data['certification_notes'] ?? null,
            ]);

            return $form;
        });

        return back()->with('success', 'Asset return certified. RT ' . $result->reference . ' was finalized.');
    }

    private function authorizeIt(): void
    {
        $permission = user()->permission('manage_it_clearance');
        abort_403(!in_array($permission, ['all', 'branch']));
    }
}
