<?php

namespace App\Http\Requests\Admin\Employee;

use App\Models\EmployeeDetails;
use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;

class UpdateRequest extends CoreRequest
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
        $detailID = EmployeeDetails::where('user_id', $this->route('employee'))->first();
        $setting = company();
        $rules = [
            'employee_id' => 'required|max:50|unique:employee_details,employee_id,'.$detailID->id.',id,company_id,' . company()->id,
            'employee_type' => 'required|in:saudi,expat',
            'name'  => 'required|max:50',
            'hourly_rate' => 'nullable|numeric',
            'department' => 'required',
            'designation' => 'required',
            'branch_id' => 'nullable|exists:branches,id',
            'joining_date' => 'required',
            // 'last_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:joining_date',
            'date_of_birth' => 'nullable|date_format:"' . $setting->date_format . '"|before_or_equal:'.now($setting->timezone)->toDateString(),
            'probation_end_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:joining_date',
            // 'notice_period_start_date' => 'nullable|required_with:notice_period_end_date|date_format:"' . $setting->date_format . '"',
            // 'notice_period_end_date' => 'nullable|required_with:notice_period_start_date|date_format:"' . $setting->date_format . '"|after_or_equal:notice_period_start_date',
            'internship_end_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:joining_date',
            'contract_end_date' => 'nullable|date_format:"' . $setting->date_format . '"|after_or_equal:joining_date',
            'transfer_number' => 'nullable|numeric',
        ];

        if (isWorksuite()) {
            $rules['email'] = 'required|max:100|unique:users,email,'.$this->route('employee').',id,company_id,' . company()->id;
        }

        if ($detailID) {
            $rules['slack_username'] = 'nullable|unique:employee_details,slack_username,'.$detailID->id.',id,company_id,' . company()->id;
        }
        else {
            $rules['slack_username'] = 'nullable|unique:employee_details,slack_username,null,id,company_id,' . company()->id;
        }

        if (request()->password != '') {
            $rules['password'] = 'required|min:8|max:50';
        }

        if (request()->telegram_user_id) {
            $rules['telegram_user_id'] = 'nullable|unique:users,telegram_user_id,' . $detailID->user_id.',id,company_id,' . company()->id;
        }

        if (request()->employee_type === 'saudi') {
            $rules['national_id'] = 'required|string|max:50';
            $rules['national_id_expiry_date'] = 'required|date_format:"' . $setting->date_format . '"';
        }

        if (request()->employee_type === 'expat') {
            $rules['iqama_no'] = 'required|string|max:50';
            $rules['iqama_profession'] = 'required|string|max:100';
            $rules['iqama_expiry_date'] = 'required|date_format:"' . $setting->date_format . '"';
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

}
