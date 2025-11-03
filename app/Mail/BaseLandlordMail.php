<?php

namespace App\Mail;

use App\Models\Settings\Landlord\MailSetting;
use App\Services\Landlord\Settings\MailSettingService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BaseLandlordMail extends Mailable
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
