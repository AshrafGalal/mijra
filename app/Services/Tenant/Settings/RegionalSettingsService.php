<?php

namespace App\Services\Tenant\Settings;

use App\DTOs\Tenant\Settings\RegionalSettingsDTO;
use App\Models\Settings\Tenant\RegionalSettings;

class RegionalSettingsService
{
    public function handle(RegionalSettingsDTO $regionalSettingsDTO, RegionalSettings $regionalSettings): RegionalSettings
    {
        $regionalSettings->fill($regionalSettingsDTO->toArray());
        $regionalSettings->save();

        return $regionalSettings;
    }
}
