<?php

namespace App\Models;

use App\Traits\CustomFieldsTrait;
use App\Traits\HasCompany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Driver extends BaseModel
{
    use CustomFieldsTrait, HasCompany;

    protected $table = 'drivers';

    protected $guarded = ['id', '_token', '_method'];

    protected $appends = [ 'image_url', 'work_mobile_with_phone_code' ];

    protected $casts = [
        'joining_date' => 'datetime',
        'insurance_expiry_date' => 'datetime',
        'license_expiry_date' => 'datetime',
        'iqaama_expiry_date' => 'datetime',
        'date_of_birth' => 'datetime'
    ];

    public function getImageUrlAttribute()
    {
        $gravatarHash = !is_null($this->email) ? md5(strtolower(trim($this->email))) : md5($this->id);

        return ($this->image) ? asset_url_local_s3('avatar/' . $this->image) : 'https://www.gravatar.com/avatar/' . $gravatarHash . '.png?s=200&d=mp';
    }

    public function getIqamaUrlAttribute()
    {
        return ($this->iqama) ? asset_url_local_s3('iqama/' . $this->iqama) : null;
    }

    public function getLicenseUrlAttribute()
    {
        return ($this->license) ? asset_url_local_s3('license/' . $this->license) : null;
    }

    public function getMedicalUrlAttribute()
    {
        return ($this->medical) ? asset_url_local_s3('medical/' . $this->medical) : null;
    }

    public function getSimFormUrlAttribute()
    {
        return ($this->sim_form) ? asset_url_local_s3('sim_form/' . $this->sim_form) : null;
    }

    public function getMobileFormUrlAttribute()
    {
        return ($this->mobile_form) ? asset_url_local_s3('mobile_form/' . $this->mobile_form) : null;
    }

    public function getOtherDocuumentUrlAttribute()
    {
        return ($this->other_document) ? asset_url_local_s3('other_document/' . $this->other_document) : null;
    }

    public function getWorkMobileWithPhoneCodeAttribute()
    {
        return $this->work_mobile_country_code . $this->work_mobile_no;
    }

    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'nationality_id', 'id');
    }

    public function businesses(): BelongsToMany {
        return $this->belongsToMany(Business::class)->withPivot('platform_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function driver_type(): BelongsTo
    {
        return $this->belongsTo(DriverType::class);
    }

    /**
     * Get all of the coordinator_reports for the Driver
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function coordinator_reports(): HasMany
    {
        return $this->hasMany(CoordinatorReport::class);
    }
}
