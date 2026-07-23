<?php

namespace App\Models;

use App\Scopes\ActiveScope;
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
        'branch_id',
        'qty',
        'added_by',
    ];

    public function employee()
    {
        return $this->belongsTo(User::class, 'employee_id')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function asset()
    {
        return $this->belongsTo(CompanyAsset::class, 'company_asset_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }
}
