<?php

namespace App\Http\Controllers;

use App\Models\AssetReturnForm;
use App\Services\AssetRecoveryApprovalService;
use Illuminate\Http\Request;

class AssetRecoveryController extends AccountBaseController
{
    public function index()
    {
        abort_403(user()->permission('manage_finance_clearance') === 'none');

        $this->forms = AssetReturnForm::with(['asset', 'employee'])
            ->where('company_id', user()->company_id)
            ->whereIn('recovery_status', ['not_required', 'partially_approved'])
            ->whereNotNull('recommended_recovery_amount')
            ->where('recommended_recovery_amount', '>', 0)
            ->latest()
            ->paginate(25);

        return view('asset-recovery.index', $this->data);
    }

    public function approve(Request $request, $id)
    {
        abort_403(user()->permission('manage_finance_clearance') === 'none');
        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'notes' => 'nullable|string|max:1000',
        ]);

        $form = AssetReturnForm::query()
            ->where('company_id', user()->company_id)
            ->findOrFail($id);

        app(AssetRecoveryApprovalService::class)->approve($form, user()->id, (float) $data['amount'], $data['notes'] ?? '');

        return back()->with('success', 'Asset recovery approved and allocated.');
    }
}
