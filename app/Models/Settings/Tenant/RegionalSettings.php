<?php

namespace App\Models\Settings\Tenant;

use App\Models\Settings\BaseTenantSettings;

class RegionalSettings extends BaseTenantSettings
{
    public ?string $country = null;

    public ?string $timezone = 'UTC';

    public static function group(): string
    {
        return 'regional_settings';
    }
}
