<?php

namespace App\Http\Requests\Tenant;

use App\Enum\CustomerFeedbackStatusEnum;
use App\Enum\FeedbackSourceEnum;
use App\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;

class CustomerFeedbackRequest extends BaseFormRequest
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
            'email' => 'required|email|max:255',
            'feedback_category_id' => [
                'required',
                'integer',
                'exists:tenant.feedback_categories,id',
            ],
            'rating' => 'required|integer|min:1|max:5',
            'detailed_review' => 'required|string',
            'source' => ['sometimes', 'string', Rule::in(FeedbackSourceEnum::values())],
            'status' => ['sometimes', 'string', Rule::in(CustomerFeedbackStatusEnum::values())],
        ];

    }
}
