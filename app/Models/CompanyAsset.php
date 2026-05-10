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
    ];

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }
}
