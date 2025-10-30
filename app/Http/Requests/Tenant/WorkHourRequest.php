<?php

namespace App\Http\Requests\Tenant;

use App\Enum\DayOfWeekEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class WorkHourRequest extends BaseFormRequest
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
            'work_hours' => [
                'required',
                'array',
                'min:1',
            ],
            'work_hours.*.day' => ['required', Rule::in(DayOfWeekEnum::values())],
            'work_hours.*.from' => 'required|date_format:H:i',
            'work_hours.*.to' => 'required|date_format:H:i',
            'work_hours.*.is_closed' => 'required|boolean',
        ];
    }
}
