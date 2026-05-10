<?php

namespace App\Http\Requests\AdvanceSalary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdvanceSalary extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employee' => 'required',
            'date' => 'required',
            'advance_salary' => 'required|numeric|min:0.01',
        ];
    }
}
