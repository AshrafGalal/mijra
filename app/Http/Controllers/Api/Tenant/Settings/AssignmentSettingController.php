<?php

namespace App\Http\Controllers\Api\Tenant\Settings;

use App\DTOs\Tenant\Settings\AssignmentSettingDTO;
use App\Enum\RotationTypeEnum;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\Settings\AssignmentSettingsRequest;
use App\Models\Settings\Tenant\AssignmentSetting;
use App\Services\Tenant\Settings\AssignmentSettingService;

class AssignmentSettingController extends Controller
{
    public function __construct(protected AssignmentSettingService $assignmentSettingService) {}

    public function index(AssignmentSetting $assignmentSetting)
    {
        return ApiResponse::success(data: [
            'rotation_type' => $assignmentSetting->rotation_type,
            'rotation_type_text' => RotationTypeEnum::from($assignmentSetting->rotation_type)->getLabel(),
            'consider_workload' => $assignmentSetting->consider_workload,
            'enable_department_based_assignment' => $assignmentSetting->enable_department_based_assignment,
            'enable_ai_suggestions' => $assignmentSetting->enable_ai_suggestions,
        ]);
    }

    public function update(AssignmentSettingsRequest $assignmentSettingsRequest, AssignmentSetting $assignmentSetting)
    {
        $assignmentSettingDTO = AssignmentSettingDTO::fromRequest($assignmentSettingsRequest);
        $this->assignmentSettingService->handle(assignmentSettingDTO: $assignmentSettingDTO, assignmentSetting: $assignmentSetting);

        return ApiResponse::success(message: __('app.assignment_settings_updated_successfully'));
    }
}
