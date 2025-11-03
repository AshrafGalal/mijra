<?php

namespace App\Http\Requests\Landlord\Whatsapp;

use App\Http\Requests\BaseFormRequest;

class WhatsappQrcodeRecivedRequest extends BaseFormRequest
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
            'tenant_id' => 'required|string',
            'account_id' => 'required|string',
            'qr' => 'required|string',
        ];
    }
}
