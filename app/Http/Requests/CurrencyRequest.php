<?php

namespace App\Http\Requests;

class CurrencyRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => 'sometimes|required|string|max:10',
            'name' => 'sometimes|required|string|max:255',
            'symbol' => 'sometimes|required|string|max:5',
            'decimal_places' => 'sometimes|required|integer|min:0|max:8',
            'is_active' => 'required|boolean',
            'is_default' => 'required|boolean',
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'is_active' => $this->is_active ?? false,
            'is_default' => $this->is_default ?? false,
        ]);
    }
}
