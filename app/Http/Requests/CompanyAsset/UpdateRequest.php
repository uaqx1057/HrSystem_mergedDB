<?php

namespace App\Http\Requests\CompanyAsset;

use App\Http\Requests\CoreRequest;
use App\Models\CompanyAssetSerial;

class UpdateRequest extends CoreRequest
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
            'sku_no'        => 'required|string|max:255|unique:company_assets,sku_no,' . $this->route('company_asset'),
            'type'          => 'nullable|string|max:255',
            'brand'         => 'nullable|string|max:255',
            'department_id' => 'required|exists:departments,id',
            'branch_id'     => 'required|exists:branches,id',
            'qty'           => 'required|integer|min:1|max:500',
            'serial_no'     => 'required|array|min:1',
            'serial_no.*'   => 'required|string|max:255|distinct',
            'serial_id'     => 'required|array',
            'serial_id.*'   => 'nullable|integer',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $qty = (int) $this->qty;
            $serials = array_map('trim', is_array($this->serial_no) ? $this->serial_no : []);
            $serialIds = is_array($this->serial_id) ? $this->serial_id : [];

            if (count($serials) !== $qty) {
                $validator->errors()->add('serial_no', __('messages.serialCountMismatch'));
            }

            foreach ($serials as $i => $serialNo) {
                if ($serialNo === '') {
                    continue;
                }

                $ignoreId = $serialIds[$i] ?? null;

                $clash = CompanyAssetSerial::withTrashed()
                    ->where('serial_no', $serialNo)
                    ->whereNull('deleted_at')
                    ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                    ->exists();

                if ($clash) {
                    $validator->errors()->add("serial_no.$i", __('messages.serialAlreadyExists', ['serial' => $serialNo]));
                }
            }
        });
    }
}
