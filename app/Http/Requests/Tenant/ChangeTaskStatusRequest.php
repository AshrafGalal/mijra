<?php

namespace App\Http\Requests\Tenant;

use App\Enum\TaskStatusEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class ChangeTaskStatusRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'integer', Rule::in(TaskStatusEnum::values())],
        ];
    }
}
