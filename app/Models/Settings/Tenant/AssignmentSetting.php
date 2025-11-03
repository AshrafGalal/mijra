<?php

namespace App\Models\Settings\Tenant;

use App\Enum\ActivationStatusEnum;
use App\Models\Settings\BaseTenantSettings;

class AssignmentSetting extends BaseTenantSettings
{
    public ?int $rotation_type = null;

    public int $consider_workload = ActivationStatusEnum::ACTIVE->value;

    public int $enable_department_based_assignment = ActivationStatusEnum::INACTIVE->value;

    public int $enable_ai_suggestions = ActivationStatusEnum::INACTIVE->value;

    public static function group(): string
    {
        return 'assignment_setting';
    }
}
