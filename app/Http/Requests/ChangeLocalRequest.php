<?php

namespace App\Http\Requests;

use App\Enum\SupportedLocalesEnum;
use Illuminate\Validation\Rule;

class ChangeLocalRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', Rule::in(SupportedLocalesEnum::values())],
        ];
    }

    public function messages()
    {
        return [
            'locale.in' => __('validation.custom.locale.in'),
            'locale.required' => __('validation.custom.locale.required'),
        ];
    }
}
