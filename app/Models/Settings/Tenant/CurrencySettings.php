<?php

namespace App\Models\Settings\Tenant;

use App\Models\Settings\BaseTenantSettings;

class CurrencySettings extends BaseTenantSettings
{
    public ?string $default_currency;

    public ?string $show_decimal_places;

    public static function group(): string
    {
        return 'currency_settings';
    }
}
