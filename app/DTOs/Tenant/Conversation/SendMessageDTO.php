<?php

namespace App\DTOs\Tenant\Conversation;

use App\DTOs\Abstract\BaseDTO;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class SendMessageDTO extends BaseDTO
{
    public function __construct(
        public ?string $body,
        public string $platform,
        public string $conversationId,
        public ?array $mediaIds = [],
        public ?string $replyToMessageId = null, // external_message_id
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            body: Arr::get($data, 'body'),
            platform: Arr::get($data, 'platform'),
            conversationId: Arr::get($data, 'conversationId'),
            mediaIds: Arr::get($data, 'media_ids'),
            replyToMessageId: Arr::get($data, 'replyToMessageId'),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            body: $request->body,
            platform: $request->platform,
            conversationId: $request->conversationId,
            mediaIds: $request->media_ids,
            replyToMessageId: $request->replyToMessageId,
        );
    }

    public function toArray(): array
    {
        return [

        ];
    }
}
