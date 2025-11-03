<?php

namespace App\Http\Requests\Tenant\Whatsapp;

use App\Http\Requests\BaseFormRequest;

class SendWhatsappMessageRequest extends BaseFormRequest
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
            'body' => ['required_without:media_ids', 'nullable', 'string'],
            'media_ids' => ['required_without:body', 'nullable', 'array'],
            'reply_to_message_id' => 'nullable|string|exists:tenant.messages,external_message_id',
        ];
    }
}
