<?php

namespace App\Http\Requests\Tenant;

use App\Http\Requests\BaseFormRequest;

class CategoryRequest extends BaseFormRequest
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
            'name' => [
                'required',
                'array',
                'min:1',
            ],
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'parent_id' => 'nullable|integer',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['is_active' => $this->boolean('is_active')]);
    }
}
