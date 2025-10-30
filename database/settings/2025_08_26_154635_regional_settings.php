<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('regional_settings.country', null);
        $this->migrator->add('regional_settings.timezone', null);
    }
};
