<?php

namespace App\Mail;

use App\Models\Settings\Tenant\MailSetting;
use App\Services\Tenant\Settings\MailSettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BaseTenantMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct()
    {
        $tenantSettings = app(MailSetting::class);
        MailSettingService::setMailDriver($tenantSettings);
    }
}
