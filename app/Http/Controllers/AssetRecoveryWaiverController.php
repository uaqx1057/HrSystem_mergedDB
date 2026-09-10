<?php

namespace App\Http\Controllers;

use App\Models\AssetReturnForm;
use App\Services\AssetRecoveryWaiverService;
use Illuminate\Http\Request;

class AssetRecoveryWaiverController extends AccountBaseController
{
    public function waive(Request $request, $id)
    {
        abort_403(user()->permission('manage_finance_clearance') === 'none');
        $data = $request->validate([
            'reason' => 'required|string|max:2000',
        ]);

        $form = AssetReturnForm::query()
            ->where('company_id', user()->company_id)
            ->findOrFail($id);

        app(AssetRecoveryWaiverService::class)->waive($form, user()->id, $data['reason']);

        return back()->with('success', 'Asset recovery waived and recorded.');
    }
}
