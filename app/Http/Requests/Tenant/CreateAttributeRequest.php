<?php

namespace App\Http\Requests\Tenant;

use App\Enum\ActivationStatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateAttributeRequest extends FormRequest
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
        $attributeId = $this->route('attribute');

        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                Rule::unique('tenant.attributes', 'slug')->ignore($attributeId),
            ],
            'status' => ['sometimes', 'required', Rule::in(ActivationStatusEnum::values())],
            'values' => 'required|array|min:1',
            'values.*' => 'required|string',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'slug' => generateSlug($this->name),
        ]);
    }
}
