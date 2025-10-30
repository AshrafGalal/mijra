<?php

namespace App\Models\Settings;

use Spatie\LaravelSettings\Settings;
use Spatie\Multitenancy\Models\Concerns\UsesLandlordConnection;

abstract class BaseLandlordSettings extends Settings
{
    use UsesLandlordConnection;
}
