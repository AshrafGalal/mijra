<?php

namespace App\Services\Tenant\Settings;

use App\Models\Settings\Tenant\MailSetting;
use App\Services\AbstractMailSettingService;

class MailSettingService extends AbstractMailSettingService
{
    protected function connectionName(): string
    {
        return 'tenant';
    }

    protected function getMailSettingModel(): string
    {
        return MailSetting::class;
    }
}
