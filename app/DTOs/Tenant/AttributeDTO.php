<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\ActivationStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AttributeDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public ?string $slug = null,
        public string $status = ActivationStatusEnum::ACTIVE->value,
        public ?array $values = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: Arr::get($data, 'name'),
            slug: Arr::get($data, 'slug', str(Arr::get($data, 'name'))->slug()->toString()),
            status: Arr::get($data, 'status', ActivationStatusEnum::ACTIVE->value),
            values: Arr::get($data, 'values', []),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            name: $request->name,
            slug: $request->slug ?? str($request->name)->slug()->toString(),
            status: $request->status ?? ActivationStatusEnum::ACTIVE->value,
            values: $request->values ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => $this->status,
        ];
    }
}
