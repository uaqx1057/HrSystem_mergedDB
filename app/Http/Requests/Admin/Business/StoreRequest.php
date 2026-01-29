<?php

namespace App\Http\Requests\Admin\Business;

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
        $rules = [
            'name' => 'required|unique:businesses,name,',
            'fields' => 'array',
            'fields.*.name' => 'required|string',
            'fields.*.type' => 'required|in:TEXT,INTEGER,DOCUMENT',
            'fields.*.required' => 'required|boolean',
            'fields.*.admin_only' => 'required|boolean'
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
