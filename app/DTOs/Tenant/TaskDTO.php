<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\PriorityEnum;
use App\Enum\TaskStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class TaskDTO extends BaseDTO
{
    public function __construct(
        public string $title,
        public int $user_id,
        public int $customer_id,
        public int $status = TaskStatusEnum::PENDING->value,
        public ?string $description = null,
        public string $priority = PriorityEnum::MEDIUM->value,
        public ?string $due_date = null,
        public ?string $completed_at = null,
        public ?array $media_ids = null,
    ) {}

    /**
     * Create DTO from HTTP request
     */
    public static function fromRequest(Request $request): static
    {
        return new static(
            title: $request->title,
            user_id: $request->user_id,
            customer_id: $request->customer_id,
            status: $request->get('status', TaskStatusEnum::PENDING->value),
            description: $request->description,
            priority: $request->priority,
            due_date: $request->due_date,
            completed_at: $request->completed_at,
            media_ids: $request->media_ids,
        );
    }

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): static
    {
        return new static(
            title: Arr::get($data, 'title'),
            user_id: Arr::get($data, 'user_id'),
            customer_id: Arr::get($data, 'customer_id'),
            status: Arr::get($data, 'status'),
            description: Arr::get($data, 'description'),
            priority: Arr::get($data, 'priority'),
            due_date: Arr::get($data, 'due_date'),
            completed_at: Arr::get($data, 'completed_at'),
            media_ids: Arr::get($data, 'media_ids', []),
        );
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'customer_id' => $this->customer_id,
            'user_id' => $this->user_id,
            'priority' => $this->priority,
            'due_date' => $this->due_date,
            'completed_at' => $this->completed_at,
        ];
    }
}
