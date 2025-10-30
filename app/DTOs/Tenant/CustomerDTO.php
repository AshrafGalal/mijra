<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\ActivationStatusEnum;
use App\Enum\CustomerSourceEnum;
use App\Enum\CustomerStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CustomerDTO extends BaseDTO
{
    public function __construct(
        public string $first_name,
        public ?string $last_name = null,
        public ?string $country_code = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $country = null,
        public ?string $city = null,
        public ?string $zipcode = null,
        public ?string $address = null,
        public ?array $tags = null,
        public ?string $notes = null,
        public int $source = CustomerSourceEnum::MANUAL->value,
        public int $status = CustomerStatusEnum::ACTIVE->value,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            first_name: Arr::get($data, 'first_name'),
            last_name: Arr::get($data, 'last_name'),
            country_code: Arr::get($data, 'country_code'),
            phone: Arr::get($data, 'phone'),
            email: Arr::get($data, 'email'),
            country: Arr::get($data, 'country'),
            city: Arr::get($data, 'city'),
            zipcode: Arr::get($data, 'zipcode'),
            address: Arr::get($data, 'address'), tags: Arr::get($data, 'tags'),
            notes: Arr::get($data, 'notes'),
            source: Arr::get($data, 'source', CustomerSourceEnum::MANUAL->value),
            status: Arr::get($data, 'status', ActivationStatusEnum::INACTIVE->value),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            first_name: $request->first_name,
            last_name: $request->last_name,
            country_code: $request->country_code,
            phone: $request->phone,
            email: $request->email,
            country: $request->country,
            city: $request->city,
            zipcode: $request->zipcode,
            address: $request->address,
            tags: $request->tags,
            notes: $request->notes,
            source: $request->source,
            status: $request->status,
        );
    }

    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'country_code' => $this->country_code,
            'phone' => $this->phone,
            'email' => $this->email,
            'country' => $this->country,
            'city' => $this->city,
            'zipcode' => $this->zipcode,
            'address' => $this->address,
            'tags' => $this->tags,
            'notes' => $this->notes,
            'source' => $this->source,
            'status' => $this->status,
        ];
    }
}
