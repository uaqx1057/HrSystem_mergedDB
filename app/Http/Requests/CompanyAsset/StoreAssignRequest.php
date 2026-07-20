<?php

namespace App\Http\Requests\CompanyAsset;

use App\Http\Requests\CoreRequest;

class StoreAssignRequest extends CoreRequest
{

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
        return [
            'employee' => 'required',
            'qty' => 'required|integer|min:1',
        ];
    }
}
