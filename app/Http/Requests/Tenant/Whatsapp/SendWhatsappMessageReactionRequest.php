<?php

namespace App\Http\Requests\Tenant\Whatsapp;

use App\Http\Requests\BaseFormRequest;

class SendWhatsappMessageReactionRequest extends BaseFormRequest
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
            'emoji' => ['required', 'string'],
        ];
    }
}
