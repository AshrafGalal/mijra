<?php

namespace App\Http\Requests\Tenant;

use App\Http\Requests\BaseFormRequest;

class StageRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ];

        // Only require workflow_id on store (POST)
        if ($this->isMethod('post')) {
            $rules['workflow_id'] = ['required', 'integer', 'exists:tenant.workflows,id'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => __('app.stage.name_required'),
            'name.string' => __('app.stage.name_string'),
            'name.max' => __('app.stage.name_max'),
            'workflow_id.required' => __('app.stage.workflow_id_required'),
            'workflow_id.integer' => __('app.stage.workflow_id_integer'),
            'workflow_id.exists' => __('app.stage.workflow_id_exists'),
            'is_active.required' => __('app.stage.is_active_required'),
            'is_active.boolean' => __('app.stage.is_active_boolean'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }
}
