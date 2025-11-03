<?php

namespace App\Models\Settings\Tenant;

use App\Models\Settings\BaseTenantSettings;

class MailSetting extends BaseTenantSettings
{
    public ?string $smtp_host;

    public ?string $smtp_port;

    public ?string $encryption;

    public ?string $mail_username;

    public ?string $mail_password;

    public ?string $from_email_address;

    public ?string $from_name;

    public static function group(): string
    {
        return 'mail_settings';
    }
}
