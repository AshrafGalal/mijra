<?php

namespace App\Http\Requests\Tenant;

use App\Enum\ExternalPlatformEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class StoreSelectedPagesRequest extends BaseFormRequest
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
            'platform_slug' => ['required','string',Rule::in(ExternalPlatformEnum::values())],
            'pages' => 'required|array|min:1',
            'pages.*' => 'array',
            'pages.*.id' => 'required|string',
            'pages.*.name' => 'required|string',
            'pages.*.access_token' => 'nullable|string',
            'pages.*.category' => 'nullable|string',
            'pages.*.category_list' => 'nullable|array',
            'pages.*.capabilities' => 'nullable|array',
        ];
    }
}
