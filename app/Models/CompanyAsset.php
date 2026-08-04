<?php

namespace App\Models;

use App\Scopes\ActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'added_by',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }

    public function serials()
    {
        return $this->hasMany(CompanyAssetSerial::class);
    }
}
