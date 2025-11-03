<?php

namespace App\Http\Requests\Tenant;

use App\Enum\OpportunityStatusEnum;
use App\Enum\PriorityEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class OpportunityRequest extends BaseFormRequest
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
            'customer_id' => 'required|integer|exists:tenant.customers,id',
            'workflow_id' => 'required|integer|exists:tenant.workflows,id',
            'stage_id' => 'required|integer|exists:tenant.stages,id',
            'user_id' => 'nullable|integer|exists:tenant.users,id',
            'priority' => ['required', Rule::in(PriorityEnum::values())],
            'status' => ['required', Rule::in(OpportunityStatusEnum::values())],
            'source' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'expected_close_date' => 'nullable|date_format:Y-m-d|after_or_equal:today',
            'items' => 'nullable|array|min:1',
            'items.*.item_id' => 'required|numeric|exists:tenant.items,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
