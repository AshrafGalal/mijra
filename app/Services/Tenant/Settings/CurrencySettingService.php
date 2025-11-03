<?php

namespace App\Services\Tenant\Settings;

use App\DTOs\Tenant\Settings\CurrencySettingsDTO;
use App\Models\Settings\Tenant\CurrencySettings;

class CurrencySettingService
{
    public function handle(CurrencySettingsDTO $currencySettingsDTO, CurrencySettings $currencySettings): CurrencySettings
    {
        $currencySettings->fill($currencySettingsDTO->toArray());
        $currencySettings->save();

        return $currencySettings;
    }
}
