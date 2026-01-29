<?php

namespace App\Http\Requests\Admin\CoordinatorReport;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;
use App\Models\CoordinatorReport;
use Carbon\Carbon;
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
        $setting = company();

        $rules = [
            'business_id' => 'required|exists:businesses,id',
            'driver_id' => 'required|exists:drivers,id',
            'fields.*.field_id' => 'required|exists:business_fields,id',
            'fields.*.value' => 'nullable',
            'report_date' => 'required|date_format:"' . $setting->date_format . '"',
            // 'report_unique' => [
            //     'required',
            //     function ($attribute, $value, $fail) {
            //         $exists = CoordinatorReport::where('business_id', $this->business_id)
            //                                    ->where('driver_id', $this->driver_id)
            //                                    ->whereDate('report_date', Carbon::createFromFormat('d-m-Y', $this->report_date)->format('Y-m-d'))
            //                                    ->exists();
            //         if ($exists) {
            //             $fail('The combination of business_id, driver_id, and report_date must be unique.');
            //         }
            //     },
            // ]
        ];

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
