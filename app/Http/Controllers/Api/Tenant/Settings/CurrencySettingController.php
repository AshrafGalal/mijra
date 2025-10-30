<?php

namespace App\Http\Controllers\Api\Tenant\Settings;

use App\DTOs\Tenant\Settings\CurrencySettingsDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Settings\CurrencySettingsRequest;
use App\Models\Settings\Tenant\CurrencySettings;
use App\Services\Tenant\Settings\CurrencySettingService;

class CurrencySettingController extends Controller
{
    public function __construct(protected CurrencySettingService $currencySettingService) {}

    public function index(CurrencySettings $currencySettings)
    {
        return ApiResponse::success(data: $currencySettings->toArray());
    }

    public function update(CurrencySettingsRequest $currencySettingsRequest, CurrencySettings $currencySettings)
    {
        $currencySettingsDTO = CurrencySettingsDTO::fromRequest($currencySettingsRequest);
        $this->currencySettingService->handle(currencySettingsDTO: $currencySettingsDTO, currencySettings: $currencySettings);

        return ApiResponse::success(message: __('app.settings_updated_successfully'));
    }
}
