<?php

namespace App\Http\Requests;

class MailSettingsRequest extends BaseFormRequest
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
            'smtp_host' => 'required|string|regex:/^([a-zA-Z0-9][a-zA-Z0-9\-]*\.)+[a-zA-Z]{2,}$/',
            'smtp_port' => 'required|numeric',
            //            'encryption' => 'required|string',
            'mail_username' => 'required|string',
            'mail_password' => 'required|string',
            'from_email_address' => 'required|string',
            'from_name' => 'required|string',

        ];

    }

    public function messages(): array
    {
        return [
            'smtp_host.required' => __('validation.mail_settings.smtp_host_required'),
            'smtp_host.string' => __('validation.mail_settings.smtp_host_string'),

            'smtp_port.required' => __('validation.mail_settings.smtp_port_required'),
            'smtp_port.numeric' => __('validation.mail_settings.smtp_port_numeric'),

            'mail_username.required' => __('validation.mail_settings.mail_username_required'),
            'mail_username.string' => __('validation.mail_settings.mail_username_string'),

            'mail_password.required' => __('validation.mail_settings.mail_password_required'),
            'mail_password.string' => __('validation.mail_settings.mail_password_string'),

            'from_email_address.required' => __('validation.mail_settings.from_email_address_required'),
            'from_email_address.string' => __('validation.mail_settings.from_email_address_string'),

            'from_name.required' => __('validation.mail_settings.from_name_required'),
            'from_name.string' => __('validation.mail_settings.from_name_string'),
        ];
    }
}
