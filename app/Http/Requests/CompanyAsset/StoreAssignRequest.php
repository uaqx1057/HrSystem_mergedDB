<?php

namespace App\Http\Requests\CompanyAsset;

use App\Http\Requests\CoreRequest;

class StoreAssignRequest extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            // create-assign sends company_asset_id; edit-assign sends id (the assignment)
            'company_asset_id'        => 'sometimes|required|exists:company_assets,id',
            'id'                      => 'sometimes|required|exists:asset_assignments,id',
            'employee'                => 'required|exists:users,id',
            'company_asset_serial_id' => 'required_without:serial_no|nullable|integer',
            'serial_no'               => 'required_without:company_asset_serial_id|nullable|string',
        ];
    }
}
