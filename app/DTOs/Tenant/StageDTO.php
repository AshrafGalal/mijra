<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StageDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public bool $is_active,
        public ?int $workflow_id = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: Arr::get($data, 'name'),
            is_active: Arr::get($data, 'is_active', true),
            workflow_id: Arr::get($data, 'workflow_id'),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            name: $request->name,
            is_active: $request->is_active ?? true,
            workflow_id: $request->workflow_id,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'is_active' => $this->is_active,
            'workflow_id' => $this->workflow_id,
        ];
    }
}
