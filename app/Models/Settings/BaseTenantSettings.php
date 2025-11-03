<?php

namespace App\Models\Settings;

use Spatie\LaravelSettings\Settings;
use Spatie\Multitenancy\Models\Concerns\UsesTenantConnection;

abstract class BaseTenantSettings extends Settings
{
    use UsesTenantConnection;

    public static function repository(): string
    {
        return 'tenant';
    }
}
