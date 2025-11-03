<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('mail_settings.smtp_host', null);
        $this->migrator->add('mail_settings.smtp_port', null);
        $this->migrator->add('mail_settings.encryption', null);
        $this->migrator->add('mail_settings.mail_username', null);
        $this->migrator->add('mail_settings.mail_password', null);
        $this->migrator->add('mail_settings.from_email_address', null);
        $this->migrator->add('mail_settings.from_name', null);
    }
};
