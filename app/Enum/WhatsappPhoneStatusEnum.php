<?php

namespace App\Enum;

enum WhatsappPhoneStatusEnum: int
{
    case INITIALIZING = 1;
    case QR_PENDING = 2;
    case CONNECTED = 3;
    case DISCONNECTED = 4;
    case AUTH_FAILED = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::INITIALIZING => 'initializing',
            self::QR_PENDING => 'qr_pending',
            self::CONNECTED => 'connected',
            self::DISCONNECTED => 'disconnected',
            self::AUTH_FAILED => 'auth_failed',
        };
    }

    public static function getValues(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->getLabel();
        }

        return $options;
    }
}
