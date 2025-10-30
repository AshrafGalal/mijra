<?php

namespace App\Enum;

enum MessageTypeEnum: string
{
    case TEXT = 'text';
    case IMAGE = 'image';
    case VIDEO = 'video';
    case AUDIO = 'audio';
    case DOCUMENT = 'document';
    case LOCATION = 'location';
    case CONTACT = 'contact';
    case TEMPLATE = 'template';
    case STICKER = 'sticker';
    case VOICE = 'voice';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::IMAGE => 'Image',
            self::VIDEO => 'Video',
            self::AUDIO => 'Audio',
            self::DOCUMENT => 'Document',
            self::LOCATION => 'Location',
            self::CONTACT => 'Contact',
            self::TEMPLATE => 'Template',
            self::STICKER => 'Sticker',
            self::VOICE => 'Voice',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

