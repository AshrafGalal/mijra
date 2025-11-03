<?php

namespace App\Http\Requests\Tenant\Whatsapp;

use App\Http\Requests\BaseFormRequest;

class StartConversationRequest extends BaseFormRequest
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
            'contact_id' => ['required', 'string', 'exists:tenant.customers,id'],
            'platform_account_id' => ['required', 'string'],
            'template_id' => ['required', 'string'],
        ];
    }
}
