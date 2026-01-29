<?php

namespace App\Http\Requests\Admin\ReceiptVoucher;

use App\Http\Requests\CoreRequest;
use App\Traits\CustomFieldsRequestTrait;
use Illuminate\Validation\Rule;

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

    // protected function prepareForValidation()
    // {
    //     if ($this->invoice_number) {
    //         $this->merge([
    //             'invoice_number' => \App\Helper\NumberFormat::invoice($this->invoice_number),
    //         ]);
    //     }
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'voucher_number' => [
                'required',
                Rule::unique('receipt_vouchers')
            ],
            'voucher_date' => 'required',
            'start_date' => 'required',
            'end_date' => 'required',
            'total_amount' => 'required',
            'driver_id' => 'required'
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

    public function messages()
    {
        return [];
    }

}
