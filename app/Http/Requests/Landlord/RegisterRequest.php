<?php

namespace App\Http\Requests\Landlord;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends BaseFormRequest
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
            'organization_name' => ['required', 'string', 'max:255', Rule::unique('tenants', 'slug')->whereNull('deleted_at')],
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'string', 'confirmed', Password::min(8)->mixedCase()],
            'free_trial' => 'boolean', // ✅ add validation rule
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'free_trial' => $this->routeIs('landlord.auth.free-trial') ? 1 : 0,
        ]);
    }
}
