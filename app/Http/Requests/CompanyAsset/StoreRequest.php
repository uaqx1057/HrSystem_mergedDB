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
            'department_id' => 'required|exists:departments,id',
            'branch_id' => 'required|exists:branches,id',
            'qty' => 'required|integer|min:1',
            'serial_no'     => 'required|array',
            'serial_no.*'   => 'required|string|max:255|distinct',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $qty = (int) $this->qty;
            $serials = is_array($this->serial_no) ? $this->serial_no : [];

            if (count($serials) !== $qty) {
                $validator->errors()->add('serial_no', __('messages.serialCountMismatch'));
            }
        });
    }
}
