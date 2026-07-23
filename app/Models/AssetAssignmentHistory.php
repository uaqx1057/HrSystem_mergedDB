<?php

namespace App\Models;

use App\Scopes\ActiveScope;
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
        'added_by',
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

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'added_by')->withoutGlobalScope(ActiveScope::class)->withOut('clientDetails');
    }
}
