<?php

namespace App\Http\Requests\Tenant;

use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TenantUserRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],

            'email' => [
                'required',
                'email',
                Rule::unique('tenant.users', 'email')
                    ->whereNull('deleted_at')
                    ->ignore($this->user),
            ],

            'role_id' => ['required', 'integer', 'exists:tenant.roles,id'],
            'phone' => ['nullable', 'string', 'max:20'],
            'department_id' => ['nullable', 'integer', 'exists:tenant.departments,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('app.tenant_user.name_required'),
            'name.string' => __('app.tenant_user.name_string'),
            'email.required' => __('app.tenant_user.email_required'),
            'email.email' => __('app.tenant_user.email_email'),
            'email.unique' => __('app.tenant_user.email_unique'),
            'role_id.required' => __('app.tenant_user.role_id_required'),
            'role_id.integer' => __('app.tenant_user.role_id_integer'),
            'role_id.exists' => __('app.tenant_user.role_id_exists'),
            'phone.string' => __('app.tenant_user.phone_string'),
            'department_id.integer' => __('app.tenant_user.department_id_integer'),
            'department_id.exists' => __('app.tenant_user.department_id_exists'),
            'tenant_id.required' => __('app.tenant_user.tenant_id_required'),
            'tenant_id.integer' => __('app.tenant_user.tenant_id_integer'),
            'tenant_id.exists' => __('app.tenant_user.tenant_id_exists'),
        ];
    }
}
