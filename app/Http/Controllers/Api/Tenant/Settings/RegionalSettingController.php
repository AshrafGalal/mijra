<?php

namespace App\Http\Controllers\Api\Tenant\Settings;

use App\DTOs\Tenant\Settings\RegionalSettingsDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Settings\RegionalSettingsRequest;
use App\Models\Settings\Tenant\RegionalSettings;
use App\Services\Tenant\Settings\RegionalSettingsService;

class RegionalSettingController extends Controller
{
    public function __construct(protected RegionalSettingsService $regionalSettingsService) {}

    public function index(RegionalSettings $regionalSettings)
    {
        return ApiResponse::success(data: $regionalSettings->toArray());
    }

    public function update(RegionalSettingsRequest $regionalSettingsRequest, RegionalSettings $regionalSettings)
    {
        $regionalSettingsDTO = RegionalSettingsDTO::fromRequest($regionalSettingsRequest);
        $this->regionalSettingsService->handle(regionalSettingsDTO: $regionalSettingsDTO, regionalSettings: $regionalSettings);

        return ApiResponse::success(message: __('app.settings_updated_successfully'));
    }
}
