<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignmentHistory extends Model
{
    use HasFactory;

    protected $table = 'asset_assignment_history';

    protected $fillable = [
        'company_asset_id',
        'employee_id',
        'action_type',
        'qty',
        'action_at',
        'signed_document',
        'asset_assignment_id',
    ];

    protected $casts = [
        'action_at' => 'datetime',
    ];

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }
}
