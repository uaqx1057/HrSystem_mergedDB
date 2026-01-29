<?php

namespace App\Http\Requests\Admin\Driver;

use App\Models\Driver;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

class UpdateRequest extends CoreRequest
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
            'branch_id' => 'sometimes|exists:branches,id',
            'driver_type_id' => 'sometimes|exists:driver_types,id',
            'nationality_id' => 'nullable|exists:countries,id',
            'address' => 'nullable',
            'insurance_expiry_date' => 'nullable',
            'iqaama_expiry_date' => 'nullable',
            'license_expiry_date' => 'nullable',
            'stc_pay' => 'nullable',
            'bank_name' => 'nullable',
            'iban' => 'nullable',
            'contract_period_in_months' => 'nullable|numeric',
            'job_position' => 'nullable',
            'department' => 'nullable',
            'vehicle_monthly_cost' => 'sometimes',
            'mobile_data' => 'sometimes',
            'accommodation' => 'sometimes',
            'government_cost' => 'sometimes',
            'fuel' => 'sometimes',
            'gprs' => 'sometimes',
            'joining_date' => 'nullable|date_format:"' . $setting->date_format . '"',
            'basic_salary' => 'nullable|numeric',
            'housing_allowance' => 'nullable|numeric',
            'transportation_allowance' => 'nullable|numeric',
            'performance_allowance' => 'nullable|numeric',
            'other_allowance' => 'nullable|numeric',
            'total_salary' => 'nullable|numeric',
            'driver_id' => 'nullable',
            'image' => 'nullable|image',
            'name' => 'nullable',
            'iqaama_number' => [
                'sometimes',
                Rule::unique('drivers', 'iqaama_number')->ignore($this->driver->id),
            ],
            'absher_number' => 'nullable',
            'sponsorship' => 'nullable',
            'sponsorship_id' => 'nullable',
            'insurance_policy_number' => 'nullable',
            'remarks' => 'nullable',
            'email' => 'nullable|email',
            'work_mobile_no' => 'nullable',
            'work_mobile_country_code' => 'nullable',
            'iqama' => 'nullable',
            'license' => 'nullable',
            'mobile_form' => 'nullable',
            'sim_form' => 'nullable',
            'medical' => 'nullable',
            'other_document' => 'nullable',
            'insurance_expiry_date' => 'nullable|date_format:"' . $setting->date_format . '"',
            'license_expiry_date' => 'nullable|date_format:"' . $setting->date_format . '"',
            'iqaama_expiry_date' => 'nullable|date_format:"' . $setting->date_format . '"',
            'date_of_birth' => 'nullable|date_format:"' . $setting->date_format . '"|before_or_equal:'.now($setting->timezone)->toDateString(),
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
