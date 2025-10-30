<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CategoryDTO extends BaseDTO
{
    public function __construct(
        public array $name,
        public ?string $description = null,
        public bool $is_active = true,
        public ?int $parent_id = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: Arr::get($data, 'name'),
            description: Arr::get($data, 'description'),
            is_active: Arr::get($data, 'is_active', true),
            parent_id: Arr::get($data, 'parent_id'),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            name: $request->name,
            description: $request->description,
            is_active: $request->is_active ?? true,
            parent_id: $request->parent_id,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'parent_id' => $this->parent_id,
        ];
    }
}
