<?php

namespace App\Http\Controllers\Api\Tenant\Settings;

use App\DTOs\MailSettingsDTO;
use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\MailSettingsRequest;
use App\Models\Settings\Tenant\MailSetting;
use App\Services\Tenant\Settings\MailSettingService;

class MailSettingController extends Controller
{
    public function __construct(protected MailSettingService $mailSettingService) {}

    public function index(MailSetting $mailSetting)
    {
        return ApiResponse::success(data: [
            'smtp_host' => $mailSetting->smtp_host,
            'smtp_port' => $mailSetting->smtp_port,
            //            'encryption' => $encryption,
            'mail_username' => $mailSetting->mail_username,
            'mail_password' => $mailSetting->mail_password,
            'from_email_address' => $mailSetting->from_email_address,
            'from_name' => $mailSetting->from_name,
        ]);
    }

    public function update(MailSettingsRequest $mailSettingsRequest, MailSetting $mailSetting)
    {
        $mailSettingsDTO = MailSettingsDTO::fromRequest($mailSettingsRequest);
        $this->mailSettingService->handle(mailSettingsDTO: $mailSettingsDTO, mailSetting: $mailSetting);

        return ApiResponse::success(message: __('app.mail_settings_updated_successfully'));
    }
}
