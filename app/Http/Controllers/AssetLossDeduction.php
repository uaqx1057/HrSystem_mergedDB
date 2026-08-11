<?php

namespace App\Http\Controllers;

use App\Models\CompanyAsset;
use App\Models\DriverDocument;
use App\Models\EmployeeAssessLoss;
use App\Models\User;
use Illuminate\Http\Request;

class AssetLossDeduction extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'Asset Loss Deductions';
    }

    public function index(Request $request)
    {
        $viewPermission = user()->permission('asset_loss_deduction');

        abort_403(!in_array($viewPermission, ['all', 'added', 'owned', 'both','branch']));

        $query = EmployeeAssessLoss::with(['companyAsset','employee','assetLoss']);

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('company_asset_id')) {
            $query->where('company_asset_id', $request->company_asset_id);
        }

        if($viewPermission == 'branch'){
            $query->whereHas('employee', function($e){
                $e->where('branch_id', user()->branch_id);
            });
        }

        if($viewPermission == 'owned'){
            $query->where('employee_id', user()->id);
        }

        $this->assetDeductions = $query->latest()
            ->paginate(20)
            ->appends($request->query()); // keeps filters in pagination links

        // needed to populate the driver filter dropdown
        $this->employees = User::allEmployees(null, true, $viewPermission);

        $this->companyAssets = CompanyAsset::orderBy('name','asc')->get();

        return view('asset-loss-deductions.index', $this->data);
    }
}
