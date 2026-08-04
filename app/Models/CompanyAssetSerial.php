<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyAssetSerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_asset_id',
        'serial_no',
        'status',
    ];
}
