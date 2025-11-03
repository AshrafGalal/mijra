<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\MessageStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class MessageDTO extends BaseDTO
{
    public function __construct(
        public string $conversation_id,
        public ?string $external_message_id = null,
        public ?string $sender = null,
        public ?string $receiver,
        public ?string $reply_to_message_id = null,
        public ?string $reply_to_external_message_id = null,
        public ?string $body = null,
        public ?string $direction = null,
        public ?bool $is_forward = false,
        public ?bool $has_media = false,
        public ?string $sent_at = null,
        public ?string $delivered_at = null,
        public ?string $read_at = null,
        public ?array $mediaData = null,
        public ?string $platform_account_id = null,
        public ?string $status = MessageStatusEnum::RECEIVED->value,
        public ?string $message_type = 'text',
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            conversation_id: Arr::get($data, 'conversation_id'),
            external_message_id: Arr::get($data, 'external_message_id'),
            sender: Arr::get($data, 'sender'),
            receiver: Arr::get($data, 'receiver'),
            reply_to_message_id: Arr::get($data, 'reply_to_message_id'),
            reply_to_external_message_id: Arr::get($data, 'reply_to_external_message_id'),
            body: Arr::get($data, 'body'),
            direction: Arr::get($data, 'direction'),
            is_forward: Arr::get($data, 'is_forward', false),
            has_media: Arr::get($data, 'has_media', false),
            sent_at: Arr::get($data, 'sent_at'),
            delivered_at: Arr::get($data, 'delivered_at'),
            read_at: Arr::get($data, 'read_at'),
            mediaData: Arr::get($data, 'mediaData', []),
            platform_account_id: Arr::get($data, 'platform_account_id'),
            status: Arr::get($data, 'status', MessageStatusEnum::RECEIVED->value)
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            conversation_id: $request->conversation_id,
            external_message_id: $request->external_message_id,
            sender: $request->sender,
            receiver: $request->receiver,
            reply_to_message_id: $request->reply_to_message_id,
            reply_to_external_message_id: $request->reply_to_external_message_id,
            body: $request->body,
            direction: $request->direction,
            is_forward: $request->is_forward ?? false,
            has_media: $request->has_media ?? false,
            sent_at: $request->sent_at,
            delivered_at: $request->delivered_at,
            read_at: $request->read_at,
            mediaData: $request->mediaData ?? [],
            platform_account_id: $request->platform_account_id,
            status: $request->status ?? MessageStatusEnum::RECEIVED->value,
        );
    }

    public function toArray(): array
    {
        return [
            'conversation_id' => $this->conversation_id,
            'external_message_id' => $this->external_message_id ?? null,
            'has_media' => $this->has_media ?? false,
            'sender' => $this->sender,
            'receiver' => $this->receiver,
            'direction' => $this->direction,
            'platform_account_id' => $this->platform_account_id,
            'reply_to_message_id' => $this->reply_to_message_id,
            'reply_to_external_message_id' => $this->reply_to_external_message_id,
            'body' => $this->body,
            'sent_at' => $this->sent_at,
            'delivered_at' => $this->delivered_at,
            'read_at' => $this->read_at,
            'status' => $this->status,
            'message_type' => 'text',
        ];
    }
}
