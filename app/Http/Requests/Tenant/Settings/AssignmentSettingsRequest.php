<?php

namespace App\Http\Requests\Tenant\Settings;

use App\Enum\RotationTypeEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class AssignmentSettingsRequest extends BaseFormRequest
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
            'rotation_type' => [
                'required',
                'integer',
                Rule::in(RotationTypeEnum::values()),
            ],
            'consider_workload' => 'required|boolean',
            'enable_department_based_assignment' => 'required|boolean',
            'enable_ai_suggestions' => 'required|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'rotation_type.required' => __('validation_settings.attributes.rotation_type').' '.__('validation.required'),
            'rotation_type.integer' => __('validation_settings.attributes.rotation_type').' '.__('validation.integer'),
            'rotation_type.in' => __('validation_settings.attributes.rotation_type').' '.__('validation.in'),
            'consider_workload.required' => __('validation_settings.attributes.consider_workload').' '.__('validation.required'),
            'consider_workload.boolean' => __('validation_settings.attributes.consider_workload').' '.__('validation.boolean'),
            'enable_department_based_assignment.required' => __('validation_settings.attributes.enable_department_based_assignment').' '.__('validation.required'),
            'enable_department_based_assignment.boolean' => __('validation_settings.attributes.enable_department_based_assignment').' '.__('validation.boolean'),
            'enable_ai_suggestions.required' => __('validation_settings.attributes.enable_ai_suggestions').' '.__('validation.required'),
            'enable_ai_suggestions.boolean' => __('validation_settings.attributes.enable_ai_suggestions').' '.__('validation.boolean'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'consider_workload' => $this->boolean('consider_workload'),
            'enable_department_based_assignment' => $this->boolean('enable_department_based_assignment'),
            'enable_ai_suggestions' => $this->boolean('enable_ai_suggestions'),
        ]);
    }
}
