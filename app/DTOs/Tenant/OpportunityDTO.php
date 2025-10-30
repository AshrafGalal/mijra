<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\OpportunityStatusEnum;
use App\Enum\PriorityEnum;
use App\Enum\TaskStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OpportunityDTO extends BaseDTO
{
    public function __construct(
        public int $customer_id,
        public int $workflow_id,
        public int $stage_id,
        public string $priority = PriorityEnum::MEDIUM->value,
        public int $status = OpportunityStatusEnum::ACTIVE->value,
        public ?int $user_id = null,
        public ?string $source = null,
        public ?string $expected_close_date = null,
        public ?string $completed_at = null,
        public ?string $notes = null,
        public ?array $items = null,
    ) {}

    /**
     * Create DTO from HTTP request
     */
    public static function fromRequest(Request $request): static
    {
        return new static(
            customer_id: $request->customer_id,
            workflow_id: $request->workflow_id,
            stage_id: $request->stage_id,
            priority: $request->get('priority', PriorityEnum::MEDIUM->value),
            status: $request->get('status', TaskStatusEnum::PENDING->value),
            user_id: $request->user_id,
            source: $request->source,
            expected_close_date: $request->expected_close_date,
            completed_at: $request->completed_at,
            notes: $request->notes,
            items: $request->items,
        );
    }

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): static
    {
        return new static(
            customer_id: Arr::get($data, 'customer_id'),
            workflow_id: Arr::get($data, 'workflow_id'),
            stage_id: Arr::get($data, 'stage_id'),
            priority: Arr::get($data, 'priority', PriorityEnum::MEDIUM->value),
            status: Arr::get($data, 'status', OpportunityStatusEnum::ACTIVE->value),
            user_id: Arr::get($data, 'user_id'),
            source: Arr::get($data, 'source'),
            expected_close_date: Arr::get($data, 'expected_close_date'),
            completed_at: Arr::get($data, 'completed_at'),
            notes: Arr::get($data, 'notes'),
            items: Arr::get($data, 'items'),
        );
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'customer_id' => $this->customer_id,
            'user_id' => $this->user_id,
            'workflow_id' => $this->workflow_id,
            'stage_id' => $this->stage_id,
            'priority' => $this->priority,
            'status' => $this->status,
            'source' => $this->source,
            'expected_close_date' => $this->expected_close_date,
            'completed_at' => $this->completed_at,
            'notes' => $this->notes,
        ];
    }
}
