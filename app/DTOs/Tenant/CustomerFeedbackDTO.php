<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\CustomerFeedbackStatusEnum;
use App\Enum\FeedbackSourceEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CustomerFeedbackDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public string $email,
        public int $feedback_category_id,
        public int $rating,
        public string $detailed_review,
        public ?string $source = FeedbackSourceEnum::WEBSITE->value,
        public ?string $status = CustomerFeedbackStatusEnum::NEW->value,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: Arr::get($data, 'name'),
            email: Arr::get($data, 'email'),
            feedback_category_id: Arr::get($data, 'feedback_category_id'),
            rating: Arr::get($data, 'rating'),
            detailed_review: Arr::get($data, 'detailed_review'),
            source: Arr::get($data, 'source', FeedbackSourceEnum::WEBSITE->value),
            status: Arr::get($data, 'status', CustomerFeedbackStatusEnum::NEW->value),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            name: $request->name,
            email: $request->email,
            feedback_category_id: $request->feedback_category_id,
            rating: $request->rating,
            detailed_review: $request->detailed_review,
            source: $request->input('source', FeedbackSourceEnum::WEBSITE->value),
            status: $request->input('status', CustomerFeedbackStatusEnum::NEW->value),
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'feedback_category_id' => $this->feedback_category_id,
            'rating' => $this->rating,
            'detailed_review' => $this->detailed_review,
            'source' => $this->source ?? FeedbackSourceEnum::WEBSITE->value,
            'status' => $this->status ?? CustomerFeedbackStatusEnum::NEW->value,
        ];
    }
}
