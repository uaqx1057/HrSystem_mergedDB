<?php

namespace App\Http\Requests\Admin\Driver;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class StoreRequest extends CoreRequest
{
    use CustomFieldsRequestTrait;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $setting = company();
        $rules = [
            'branch_id' => 'required|exists:branches,id',
            'driver_id' => 'required',
            'driver_type_id' => 'required',
            'image' => 'required|image',
            'name' => 'required',
            'iqaama_number' => 'required|unique:drivers,iqaama_number',
            'absher_number' => 'required',
            'sponsorship' => 'required',
            'sponsorship_id' => 'required',
            'insurance_policy_number' => 'required',
            'remarks' => 'nullable',
            'email' => 'required|email',
            'work_mobile_no' => 'required',
            'vehicle_monthly_cost' => 'sometimes',
            'mobile_data' => 'sometimes',
            'accommodation' => 'sometimes',
            'government_cost' => 'sometimes',
            'fuel' => 'sometimes',
            'gprs' => 'sometimes',
            'work_mobile_country_code' => 'required',
            'insurance_expiry_date' => 'required|date_format:"' . $setting->date_format . '"',
            'license_expiry_date' => 'required|date_format:"' . $setting->date_format . '"',
            'iqaama_expiry_date' => 'required|date_format:"' . $setting->date_format . '"',
            'date_of_birth' => 'required|date_format:"' . $setting->date_format . '"|before_or_equal:'.now($setting->timezone)->toDateString(),
        ];

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

}
