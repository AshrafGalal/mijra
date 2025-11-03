<?php

use App\Enum\RotationTypeEnum;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('assignment_setting.rotation_type', RotationTypeEnum::SEQUENTIAL->value);
        $this->migrator->add('assignment_setting.consider_workload', 1);
        $this->migrator->add('assignment_setting.enable_department_based_assignment', 0);
        $this->migrator->add('assignment_setting.enable_ai_suggestions', 0);
    }
};
