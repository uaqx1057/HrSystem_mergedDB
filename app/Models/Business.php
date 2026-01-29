<?php

namespace App\Models;

use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends BaseModel
{
    use CustomFieldsTrait, HasCompany;

    public static $presetFields = [
        'Total Orders',
        'Bonus',
        'Tip',
        'Other Tip',
        'Total Earnings',
    ];

    protected $table = 'businesses';

    protected $appends = [  ];

    protected $casts = [
    ];

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(Driver::class);
    }


    public function fields(): HasMany
    {
        return $this->hasMany(BusinessField::class);
    }

    public function driver_calculations(): HasMany
    {
        return $this->hasMany(DriverCalculation::class);
    }

    public function coordinator_reports(): HasMany
    {
        return $this->hasMany(CoordinatorReport::class);
    }
}
