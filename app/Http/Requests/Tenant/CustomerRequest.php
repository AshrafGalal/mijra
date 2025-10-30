<?php

namespace App\Http\Requests\Tenant;

use App\Enum\CustomerSourceEnum;
use App\Enum\CustomerStatusEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends BaseFormRequest
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
    public function rules()
    {
        $countriesData = countriesData();

        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'country_code' => ['required', 'string', Rule::in($countriesData->pluck('dial_code')->toArray())],
            'phone' => ['nullable', 'string', Rule::unique('tenant.customers', 'phone')->ignore($this->customer)],
            'email' => 'nullable|email',
            'tags' => 'nullable|array|min:1',
            'city' => 'nullable|string',
            'address' => 'required|string',
            'zipcode' => 'nullable|string',
            'notes' => 'nullable|string|max:190',

        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->get('status', CustomerStatusEnum::ACTIVE->value),
            'source' => $this->get('source', CustomerSourceEnum::MANUAL->value),
        ]);
    }
}
