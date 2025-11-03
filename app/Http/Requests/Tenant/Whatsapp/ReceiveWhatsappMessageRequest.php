<?php

namespace App\Http\Requests\Tenant\Whatsapp;

use App\Enum\ConversationTypeEnum;
use App\Enum\MessageDirectionEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class ReceiveWhatsappMessageRequest extends BaseFormRequest
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
            'account_id' => ['required', 'string'],
            'tenant_id' => ['required', 'string'],
            'external_identifier_id' => ['required', 'string', 'max:255'],
            'contact_identifier_id' => ['required', 'string', 'max:255'],
            'contact_name' => ['required', 'string', 'max:255'],
            'sent_at' => ['required', 'date'],
            'title' => ['required', 'string', 'max:255'],
            'is_muted' => ['boolean'],
            'is_story' => ['boolean'],
            'unread_count' => ['nullable','integer'],
            'type' => ['required', Rule::in(ConversationTypeEnum::values())],
            'platform' => ['required', 'string', 'in:whatsapp'],
            'messages' => ['required', 'array'],
            'messages.*.external_message_id' => ['nullable', 'string', 'max:255'],
            'messages.*.sender' => ['required', 'string', 'max:255'],
            'messages.*.receiver' => ['required', 'string', 'max:255'],
            'messages.*.body' => ['nullable', 'string'],
            'messages.*.sent_at' => ['required', 'date'],
            'messages.*.has_media' => ['boolean'],
            'messages.*.is_forward' => ['boolean'],
            'messages.*.direction' => ['required', 'integer', Rule::in(MessageDirectionEnum::values())],
            'messages.*.platform_account_id' => ['required', 'string'],
            'messages.*.reply_to_message_id' => ['nullable', 'string', 'max:255'],
            'messages.*.status' => ['nullable', 'integer'],
            'messages.*.type' => ['required', 'string'],
            'messages.*.metadata' => ['nullable', 'array'],
        ];
    }
}
