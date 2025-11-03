<?php

namespace App\Services\Tenant\Settings;

use App\DTOs\Tenant\Settings\AssignmentSettingDTO;
use App\Models\Settings\Tenant\AssignmentSetting;

class AssignmentSettingService
{
    public function handle(AssignmentSettingDTO $assignmentSettingDTO, AssignmentSetting $assignmentSetting): AssignmentSetting
    {
        $assignmentSetting->rotation_type = $assignmentSettingDTO->rotation_type;
        $assignmentSetting->consider_workload = $assignmentSettingDTO->consider_workload;
        $assignmentSetting->enable_department_based_assignment = $assignmentSettingDTO->enable_department_based_assignment;
        $assignmentSetting->enable_ai_suggestions = $assignmentSettingDTO->enable_ai_suggestions;

        return $assignmentSetting->save();
    }
}
