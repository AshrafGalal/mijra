<?php

namespace App\Http\Requests\Tenant;

use App\Enum\MessageTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SendMessageRequest extends FormRequest
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
            'content' => 'required|string',
            'type' => ['nullable', 'string', Rule::in(MessageTypeEnum::values())],
            'metadata' => 'nullable|array',
            'attachments' => 'nullable|array',
            'attachments.*.type' => 'required_with:attachments|string',
            'attachments.*.url' => 'required_with:attachments|string',
            'attachments.*.filename' => 'nullable|string',
            'attachments.*.mime_type' => 'nullable|string',
            'attachments.*.file_size' => 'nullable|integer',
        ];
    }
}



