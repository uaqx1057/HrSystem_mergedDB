<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'company_asset_id',
        'document_path',
        'signed_document',
        'status',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id');
    }

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }
}
