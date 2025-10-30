<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\ActivationStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class GroupDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public ?string $description = null,
        public ?string $color = null,
        public ?int $is_active = ActivationStatusEnum::ACTIVE->value,
    ) {}

    /**
     * Create DTO from HTTP request
     */
    public static function fromRequest(Request $request): static
    {
        return new static(
            name: $request->name,
            description: $request->description,
            color: $request->color,
            is_active: $request->is_active,
        );
    }

    /**
     * Create DTO from array
     */
    public static function fromArray(array $data): static
    {
        return new static(
            name: Arr::get($data, 'name'),
            description: Arr::get($data, 'description'),
            color: Arr::get($data, 'color'),
            is_active: Arr::get($data, 'is_active'),

        );
    }

    /**
     * Convert DTO to array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'color' => $this->color,
            'is_active' => $this->is_active,
        ];
    }
}
