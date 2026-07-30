<?php

namespace App\Http\Requests\CompanyAsset;

use App\Http\Requests\CoreRequest;

class UpdateRequest extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

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
            'serial_no'     => 'required|array',
            'serial_no.*'   => 'required|string|max:255|distinct',
            'serial_id'     => 'required|array',
            'serial_id.*'   => 'nullable|integer|exists:company_asset_serials,id',
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

            foreach ($serials as $i => $serialNo) {
                $ignoreId = $this->serial_id[$i] ?? null;

                $exists = \App\Models\CompanyAssetSerial::where('serial_no', trim($serialNo))
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();

                // if ($exists) {
                //     $validator->errors()->add("serial_no.$i", __('messages.serialAlreadyExists', ['serial' => $serialNo]));
                // }
            }
        });
    }
}
