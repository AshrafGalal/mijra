<?php

namespace App\DTOs\Tenant\Settings;

use App\DTOs\Abstract\BaseDTO;
use Illuminate\Support\Arr;

class RegionalSettingsDTO extends BaseDTO
{
    public function __construct(
        public ?string $country = null,
        public ?string $timezone = 'UTC',

    ) {}

    public static function fromRequest($request): static
    {
        return new self(
            country: $request->country,
            timezone: $request->timezone,

        );
    }

    /**
     * @return $this
     */
    public static function fromArray(array $data): static
    {
        return new self(
            country: Arr::get($data, 'country'),
            timezone: Arr::get($data, 'timezone'),
        );
    }

    public function toArray(): array
    {
        return [
            'country' => $this->country,
            'timezone' => $this->timezone,
        ];
    }
}
