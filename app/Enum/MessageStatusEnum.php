<?php

namespace App\Enum;

enum MessageStatusEnum: int
{
    case ERROR = -1;       // Message failed to send

    case PENDING = 0;      // Message waiting to be sent
    case RECEIVED = 1;       // Message received by WhatsApp server
    case DELIVERED = 2;       // Message delivered to recipient’s device
    case READ = 3;         // Message read by recipient
    case PLAYED = 4;       // Media (audio/video) played

    public function getLabel(): string
    {
        return match ($this) {
            self::ERROR => __('message.status.error'),
            self::PENDING => __('message.status.pending'),
            self::RECEIVED => __('message.status.received'),
            self::DELIVERED => __('message.status.delivered'),
            self::READ => __('message.status.read'),
            self::PLAYED => __('message.status.played'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
