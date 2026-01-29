<?php

namespace App\Http\Requests\Admin\BusinessDriver;

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
            'platform_id' => [ 
                'required',
                Rule::unique('business_driver')
                    ->where('platform_id', $this->platform_id)
                    ->where('business_id', $this->business_id)
                    ->ignore($this->driver->id, 'driver_id')
            ]
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
