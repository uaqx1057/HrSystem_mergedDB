<?php

namespace App\Http\Requests\CompanyAsset;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
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
            'sku_no' => 'required|string|max:255|unique:company_assets,sku_no,' . $this->route('company_asset'),
            'type' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'branch_id' => 'required|exists:branches,id',
            'qty' => 'required|integer|min:1',
        ];
    }
}
