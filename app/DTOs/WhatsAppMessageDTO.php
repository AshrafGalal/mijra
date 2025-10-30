<?php

namespace App\DTOs;

class WhatsAppMessageDTO
{
    public function __construct(
        public string $to,
        public string $type,
        public ?string $content = null,
        public ?string $mediaUrl = null,
        public ?string $caption = null,
        public ?string $filename = null,
        public ?array $buttons = null,
        public ?array $listSections = null,
        public ?string $templateName = null,
        public ?array $templateParameters = null,
        public ?string $languageCode = 'en',
        public ?array $metadata = null,
    ) {
    }

    /**
     * Create DTO for text message.
     */
    public static function text(string $to, string $content): self
    {
        return new self(
            to: $to,
            type: 'text',
            content: $content
        );
    }

    /**
     * Create DTO for image message.
     */
    public static function image(string $to, string $imageUrl, ?string $caption = null): self
    {
        return new self(
            to: $to,
            type: 'image',
            mediaUrl: $imageUrl,
            caption: $caption
        );
    }

    /**
     * Create DTO for video message.
     */
    public static function video(string $to, string $videoUrl, ?string $caption = null): self
    {
        return new self(
            to: $to,
            type: 'video',
            mediaUrl: $videoUrl,
            caption: $caption
        );
    }

    /**
     * Create DTO for document message.
     */
    public static function document(string $to, string $documentUrl, ?string $filename = null, ?string $caption = null): self
    {
        return new self(
            to: $to,
            type: 'document',
            mediaUrl: $documentUrl,
            filename: $filename,
            caption: $caption
        );
    }

    /**
     * Create DTO for template message.
     */
    public static function template(
        string $to,
        string $templateName,
        array $parameters = [],
        string $languageCode = 'en'
    ): self {
        return new self(
            to: $to,
            type: 'template',
            templateName: $templateName,
            templateParameters: $parameters,
            languageCode: $languageCode
        );
    }

    /**
     * Create DTO for button message.
     */
    public static function buttons(
        string $to,
        string $content,
        array $buttons
    ): self {
        return new self(
            to: $to,
            type: 'interactive_buttons',
            content: $content,
            buttons: $buttons
        );
    }

    /**
     * Convert to array.
     */
    public function toArray(): array
    {
        return array_filter([
            'to' => $this->to,
            'type' => $this->type,
            'content' => $this->content,
            'media_url' => $this->mediaUrl,
            'caption' => $this->caption,
            'filename' => $this->filename,
            'buttons' => $this->buttons,
            'list_sections' => $this->listSections,
            'template_name' => $this->templateName,
            'template_parameters' => $this->templateParameters,
            'language_code' => $this->languageCode,
            'metadata' => $this->metadata,
        ], fn($value) => !is_null($value));
    }
}

