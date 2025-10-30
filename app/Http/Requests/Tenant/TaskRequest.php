<?php

namespace App\Http\Requests\Tenant;

use App\Enum\PriorityEnum;
use App\Enum\TaskStatusEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class TaskRequest extends BaseFormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            //            'status' => ['required', Rule::in(TaskStatusEnum::values())],
            'customer_id' => 'required|integer|exists:tenant.customers,id',
            'user_id' => 'required|integer|exists:tenant.users,id',
            'priority' => ['required', Rule::in(PriorityEnum::values())],
            'due_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:today',
            'media_ids' => 'nullable|array',
            'media_ids.*' => 'nullable|string|exists:tenant.temp_files,file_id',
        ];
    }
}
