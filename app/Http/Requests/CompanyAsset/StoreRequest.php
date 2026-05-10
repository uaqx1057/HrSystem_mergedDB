<?php

namespace App\Http\Requests\CompanyAsset;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
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
            'name' => 'required|string|max:255',
            'catalog' => 'required|string|max:255',
            'sku_no' => 'required|string|max:255|unique:company_assets,sku_no',
            'type' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
        ];
    }
}
