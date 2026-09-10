<?php

namespace App\Http\Requests\CompanyAsset;

use App\Http\Requests\CoreRequest;

class StoreRequest extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name'          => 'required|string|max:255',
            'catalog'       => 'nullable|string|max:255',
            'sku_no'        => 'required|string|max:255|unique:company_assets,sku_no',
            'type'          => 'nullable|string|max:255',
            'brand'         => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'branch_id'     => 'required|exists:branches,id',
            'qty'           => 'required|integer|min:1|max:500',
            'serial_no'     => 'required|array|min:1',
            'serial_no.*'   => 'required|string|max:255|distinct',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $qty = (int) $this->qty;
            $serials = array_map('trim', is_array($this->serial_no) ? $this->serial_no : []);

            if (count($serials) !== $qty) {
                $validator->errors()->add('serial_no', __('messages.serialCountMismatch'));
            }

            // Serial numbers must be globally unique (one physical unit == one serial).
            foreach ($serials as $i => $serialNo) {
                if ($serialNo === '') {
                    continue;
                }
                if (\App\Models\CompanyAssetSerial::where('serial_no', $serialNo)->exists()) {
                    $validator->errors()->add("serial_no.$i", __('messages.serialAlreadyExists', ['serial' => $serialNo]));
                }
            }
        });
    }
}
