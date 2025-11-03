<?php

namespace App\Enum;

enum MessageTypeEnum: string
{
    // 🔹 Basic types
    case TEXT = 'text';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case STICKER = 'sticker';

    // 🔹 Interactive / special
    case LOCATION = 'location';
    case CONTACT = 'contact';
    case TEMPLATE = 'template';
    case BUTTONS = 'buttons';
    case LIST = 'list';
    case POLL = 'poll';
    case REACTION = 'reaction';

    // 🔹 System or internal
    case REVOKED = 'revoked';
    case SYSTEM = 'system';

    public function isMedia(): bool
    {
        return in_array($this, [
            self::IMAGE,
            self::VIDEO,
            self::AUDIO,
            self::DOCUMENT,
            self::STICKER,
        ]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
