<?php

namespace App\DTOs\Tenant\Settings;

use App\DTOs\Abstract\BaseDTO;
use Illuminate\Support\Arr;

class CurrencySettingsDTO extends BaseDTO
{
    public function __construct(
        public ?string $default_currency = null,
        public ?string $show_decimal_places = null,

    ) {}

    public static function fromRequest($request): static
    {
        return new self(
            default_currency: $request->default_currency,
            show_decimal_places: $request->show_decimal_places,

        );
    }

    /**
     * @return $this
     */
    public static function fromArray(array $data): static
    {
        return new self(
            default_currency: Arr::get($data, 'default_currency'),
            show_decimal_places: Arr::get($data, 'show_decimal_places'),
        );
    }

    public function toArray(): array
    {
        return [

            'default_currency' => $this->default_currency,
            'show_decimal_places' => $this->show_decimal_places,

        ];
    }
}
