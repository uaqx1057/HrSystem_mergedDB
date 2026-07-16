<?php

namespace App\Http\Requests\EmployeeBankAccount;

use App\Http\Requests\CoreRequest;

class StoreEmployeeBankAccount extends CoreRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'employee_id' => 'required|exists:users,id',
            'bank_name' => 'required|string|max:255',
            'iban_number' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'swift_code' => 'nullable|string|max:255',
            'is_main_account' => 'nullable|boolean',
        ];
    }
}
