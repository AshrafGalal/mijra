<?php

namespace App\Http\Requests\Tenant\Settings;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class CurrencySettingsRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'default_currency' => ['required', Rule::in(currencyCodes())],
            'show_decimal_places' => 'required|boolean',

        ];

    }

    protected function prepareForValidation()
    {
        $this->merge([
            'show_decimal_places' => $this->boolean('show_decimal_places'),
        ]);
    }
}
