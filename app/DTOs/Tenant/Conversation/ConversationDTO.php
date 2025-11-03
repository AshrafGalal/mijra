<?php

namespace App\DTOs\Tenant\Conversation;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\ConversationTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ConversationDTO extends BaseDTO
{
    public function __construct(
        public ?int    $contact_id,
        public string  $external_identifier_id,
        public ?string $tenant_platform_id = null,
        public ?string $last_message_id = null,
        public int     $unread_count = 0,
        public ?string $contact_identifier_id,
        public ?string $contact_name,
        public ?string $title,
        public bool    $is_muted = false,
        public bool    $is_story = false,
        public int     $type,
        public string  $platform,
        public string  $sent_at,
        public ?array  $metadata = [],
        public array   $messages = [], // for first time
        public ?string $platform_account_id = null,
        public ?string $platform_account_number = null,
    )
    {
    }

    public static function fromArray(array $data): static
    {
        return new self(
            contact_id: Arr::get($data, 'contact_id'),
            external_identifier_id: Arr::get($data, 'external_identifier_id'),
            tenant_platform_id: Arr::get($data, 'tenant_platform_id'),
            last_message_id: Arr::get($data, 'last_message_id'),
            unread_count: Arr::get($data, 'unread_count', 0),
            contact_identifier_id: Arr::get($data, 'contact_identifier_id'),
            contact_name: Arr::get($data, 'contact_name'),
            title: Arr::get($data, 'title'),
            is_muted: Arr::get($data, 'is_muted', false),
            is_story: Arr::get($data, 'is_story', false),
            type: Arr::get($data, 'type', ConversationTypeEnum::INDIVIDUAL->value),
            platform: Arr::get($data, 'platform'),
            sent_at: Arr::get($data, 'sent_at'),
            metadata: Arr::get($data, 'metadata'),
            messages: Arr::get($data, 'messages', []),
            platform_account_id: Arr::get($data, 'platform_account_id'),
            platform_account_number: Arr::get($data, 'platform_account_number'),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            contact_id: $request->contact_id,
            external_identifier_id: $request->external_identifier_id,
            tenant_platform_id: $request->tenant_platform_id ?? null,
            last_message_id: $request->last_message_id ?? null,
            unread_count: $request->unread_count ?? 0,
            contact_identifier_id: $request->contact_identifier_id,
            contact_name: $request->contact_name,
            title: $request->title,
            is_muted: $request->is_muted ?? false,
            is_story: $request->is_story ?? false,
            type: $request->type ?? ConversationTypeEnum::INDIVIDUAL->value,
            platform: $request->platform,
            sent_at: $request->sent_at,
            metadata: $request->metadata ?? null,
            messages: $request->messages ?? [],
            platform_account_id: $request->platform_account_id,
            platform_account_number: $request->platform_account_number,

        );
    }

    public function toArray(): array
    {
        return [
            'contact_id' => $this->contact_id,
            'external_identifier_id' => $this->external_identifier_id,
            'tenant_platform_id' => $this->tenant_platform_id,
            'last_message_id' => $this->last_message_id,
            'unread_count' => $this->unread_count,
            'contact_identifier_id' => $this->contact_identifier_id,
            'contact_name' => $this->contact_name,
            'title' => $this->title,
            'is_muted' => $this->is_muted,
            'is_story' => $this->is_story,
            'type' => $this->type,
            'platform' => $this->platform,
            'metadata' => $this->metadata,
            'platform_account_id' => $this->platform_account_id,
            'platform_account_number' => $this->platform_account_number,
        ];
    }
}
