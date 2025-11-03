<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\MessageTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class StoryDTO extends BaseDTO
{
    public function __construct(
        public string  $platform,
        public string  $external_identifier_id,
        public string  $contact_identifier_id,
        public ?string $expires_at,
        public ?string $body = null,
        public bool    $has_media = false,
        public string  $type = MessageTypeEnum::TEXT->value,
        public ?array  $mediaData = null,
        public ?string $contact_name = null,
        public ?int    $customer_id = null,
    )
    {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            platform: Arr::get($data, 'platform'),
            external_identifier_id: Arr::get($data, 'external_identifier_id'),
            body: Arr::get($data, 'body'),
            has_media: Arr::get($data, 'has_media', false),
            type: Arr::get($data, 'type', 'image'),
            expires_at: Arr::get($data, 'expires_at'),
            mediaData: Arr::get($data, 'mediaData'),
            contact_identifier_id: Arr::get($data, 'contact_identifier_id'),
            contact_name: Arr::get($data, 'contact_name'),
            customer_id: Arr::get($data, 'customer_id'),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            platform: $request->platform,
            external_identifier_id: $request->external_identifier_id,
            contact_identifier_id: $request->contact_identifier,
            expires_at: $request->expires_at,
            body: $request->body,
            has_media: $request->has_media ?? false,
            type: $request->type ?? 'image',
            mediaData: $request->mediaData,
            contact_name: $request->contact_name,
            customer_id: $request->customer_id,
        );
    }

    public function toArray(): array
    {
        return [
            'platform' => $this->platform,
            'external_identifier_id' => $this->external_identifier_id,
            'body' => $this->body,
            'has_media' => $this->has_media,
            'type' => $this->type,
            'expires_at' => $this->expires_at,
            'mediaData' => $this->mediaData,
            'contact_identifier_id' => $this->contact_identifier_id,
            'contact_name' => $this->contact_name,
            'customer_id' => $this->customer_id,
        ];
    }
}
