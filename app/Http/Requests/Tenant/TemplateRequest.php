<?php

namespace App\Http\Requests\Tenant;

use App\Enum\ButtonTypeEnum;
use App\Enum\CampaignTypeEnum;
use App\Enum\TemplateStatusEnum;
use App\Enum\TemplateTypeEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TemplateRequest extends BaseFormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'category' => ['required', Rule::in(CampaignTypeEnum::values())],
            'template_type' => ['required', Rule::in(TemplateTypeEnum::values())],
            'body' => 'required|string',
            'sender_number' => 'nullable|string|max:20',
            'header_content' => 'nullable|string|max:255',
            'footer_content' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
            'media_id' => 'nullable|integer',
            // validate buttons
            'template_buttons' => 'nullable|array|min:1|max:3',
            'template_buttons.*.button_text' => 'required|string|max:255',
            'template_buttons.*.button_type' => ['required', Rule::in(ButtonTypeEnum::values())],
            'template_buttons.*.action_value' => 'required|string',
            // validate parms
            'template_parms' => 'nullable|array|min:1',
            'template_parms.*.parm_name' => 'required|string|max:255',

        ];
    }

    public function messages(): array
    {
        return [
            // Basic template validation
            'name.required' => __('template.validation.name_required'),
            'name.string' => __('template.validation.name_string'),
            'name.max' => __('template.validation.name_max'),

            'description.string' => __('template.validation.description_string'),
            'description.max' => __('template.validation.description_max'),

            'category.required' => __('template.validation.category_required'),
            'category.in' => __('template.validation.category_in', [
                'values' => implode(', ', CampaignTypeEnum::values()),
            ]),

            'template_type.required' => __('template.validation.template_type_required'),
            'template_type.in' => __('template.validation.template_type_in', [
                'values' => implode(', ', TemplateTypeEnum::values()),
            ]),

            'content.required' => __('template.validation.content_required'),
            'content.string' => __('template.validation.content_string'),

            'header_content.string' => __('template.validation.header_content_string'),
            'header_content.max' => __('template.validation.header_content_max'),

            'footer_content.string' => __('template.validation.footer_content_string'),
            'footer_content.max' => __('template.validation.footer_content_max'),

            'is_active.required' => __('template.validation.is_active_required'),
            'is_active.boolean' => __('template.validation.is_active_boolean'),

            // Template buttons validation - Laravel will automatically replace :position
            'template_buttons.array' => __('template.validation.template_buttons_array'),
            'template_buttons.min' => __('template.validation.template_buttons_min'),

            'template_buttons.*.button_text.required' => __('validation.custom.template.button_text_required'),
            'template_buttons.*.button_text.string' => __('template.validation.button_text_string'),
            'template_buttons.*.button_text.max' => __('template.validation.button_text_max'),

            'template_buttons.*.button_type.required' => __('template.validation.button_type_required'),
            'template_buttons.*.button_type.in' => __('template.validation.button_type_in', [
                'values' => implode(', ', ButtonTypeEnum::values()),
            ]),

            'template_buttons.*.action_value.required' => __('template.validation.action_value_required'),
            'template_buttons.*.action_value.string' => __('template.validation.action_value_string'),

            // Template parameters validation - Laravel will automatically replace :position
            'template_parms.array' => __('template.validation.template_parms_array'),
            'template_parms.min' => __('template.validation.template_parms_min'),

            'template_parms.*.parm_name.required' => __('template.validation.template_parms_parm_name_required'),
            'template_parms.*.parm_name.string' => __('template.validation.template_parms_parm_name_string'),
            'template_parms.*.parm_name.max' => __('template.validation.template_parms_parm_name_max'),
        ];
    }

    protected function prepareForValidation()
    {
        $this->merge([
            'status' => $this->template_type == TemplateTypeEnum::WHATSAPP->value ? TemplateStatusEnum::PENDING->value : TemplateStatusEnum::APPROVED->value,
        ]);
    }
}
