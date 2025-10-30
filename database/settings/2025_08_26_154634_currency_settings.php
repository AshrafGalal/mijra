<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('currency_settings.default_currency', null);
        $this->migrator->add('currency_settings.show_decimal_places', null);
    }
};
