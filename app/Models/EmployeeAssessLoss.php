<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeAssessLoss extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_asset_id',
        'employee_id',
        'loss_amount',
        'asset_assignment_history_id',
        'deducted_amount',
        'status',
    ];

    public function companyAsset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function assetLoss()
    {
        return $this->belongsTo(AssetAssignmentHistory::class, 'asset_assignment_history_id');
    }

    public function salarySlips()
    {
        return $this->belongsToMany(SalarySlip::class, 'salary_slip_employee_assess_loss')
            ->withPivot('deducted_amount')
            ->withTimestamps();
    }
}
