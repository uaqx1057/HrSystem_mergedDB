<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'catalog',
        'sku_no',
        'name',
        'type',
        'brand',
        'department_id',
        'branch_id',
        'qty',
        'available_qty',
        'status',
    ];

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function history()
    {
        return $this->hasMany(AssetAssignmentHistory::class, 'company_asset_id');
    }
}
