<?php

namespace App\Http\Requests\Admin\Employee;

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
        \Illuminate\Support\Facades\Validator::extend('check_superadmin', function ($attribute, $value, $parameters, $validator) {
            return !\App\Models\User::withoutGlobalScopes([\App\Scopes\ActiveScope::class, \App\Scopes\CompanyScope::class])
                ->where('email', $value)
                ->where('is_superadmin', 1)
                ->exists();
        });

        $setting = company();
        $rules = [
            'employee_id' => 'required|unique:employee_details,employee_id,null,id,company_id,' . company()->id.'|max:100',
            'employee_type' => 'required|in:saudi,expat',
            'name' => 'required|max:50',
            'email' => 'required|email:rfc|unique:users,email,null,id,company_id,' . company()->id.'|max:100|check_superadmin',
            'slack_username' => 'nullable|unique:employee_details,slack_username,null,id,company_id,' . company()->id.'|max:30',
            'hourly_rate' => 'nullable|numeric',
            'joining_date' => 'required',
            // 'last_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:joining_date',
            'date_of_birth' => 'nullable|date_format:"' . $setting->date_format . '"|before_or_equal:'.now($setting->timezone)->toDateString(),
            'department' => 'required',
            'designation' => 'required',
            'branch_id' => 'nullable|exists:branches,id',
            'probation_end_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:joining_date',
            // 'notice_period_start_date' => 'nullable|required_with:notice_period_end_date|date_format:"' . $setting->date_format . '"',
            // 'notice_period_end_date' => 'nullable|required_with:notice_period_start_date|date_format:"' . $setting->date_format . '"|after_or_equal:notice_period_start_date',
            'internship_end_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:joining_date',
            'contract_end_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:joining_date',
            'bank_accounts.*.bank_name' => 'required_with:bank_accounts.*.iban_number,bank_accounts.*.account_number,bank_accounts.*.swift_code|string|max:255',
            'bank_accounts.*.iban_number' => 'required_with:bank_accounts.*.bank_name,bank_accounts.*.account_number,bank_accounts.*.swift_code|string|max:255',
            'bank_accounts.*.account_number' => 'nullable|string|max:255',
            'bank_accounts.*.swift_code' => 'nullable|string|max:255',
            'bank_accounts.*.is_main_account' => 'nullable|boolean',
            'password' => 'required|string|min:8',
        ];

        if (request()->telegram_user_id) {
            $rules['telegram_user_id'] = 'nullable|unique:users,telegram_user_id,null,id,company_id,' . company()->id;
        }

        if (request()->employee_type === 'saudi') {
            $rules['national_id'] = 'required|string|max:50';
            $rules['national_id_expiry_date'] = 'required|date_format:"' . $setting->date_format . '"';
        }

        if (request()->employee_type === 'expat') {
            $rules['iqama_no'] = 'required|string|max:50';
            $rules['iqama_profession'] = 'required|string|max:100';
            $rules['iqama_expiry_date'] = 'required|date_format:"' . $setting->date_format . '"';
            $rules['iqama_image'] = 'required|image';
            $rules['passport_no'] = 'required|string|max:50';
        }

        $rules = $this->customFieldRules($rules);

        return $rules;
    }

    public function attributes()
    {
        $attributes = [];

        $attributes = $this->customFieldsAttributes($attributes);

        return $attributes;
    }

    public function messages()
    {
        return [
            'email.check_superadmin' => __('superadmin.emailAlreadyExist'),
        ];
    }

}
