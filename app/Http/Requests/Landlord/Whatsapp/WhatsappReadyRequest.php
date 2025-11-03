<?php

namespace App\Http\Requests\Landlord\Whatsapp;

use Illuminate\Foundation\Http\FormRequest;

class WhatsappReadyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'tenant_id' => 'required|string',
            'account_id' => 'required|integer',
            'phone_number' => 'required|string',
            'label_name' => 'nullable|string',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
