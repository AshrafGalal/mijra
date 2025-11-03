<?php

namespace App\Services\Landlord\Channels\Messaging\Mappers;

use App\DTOs\Tenant\Conversation\ConversationDTO;
use App\DTOs\Tenant\MessageDTO;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MessengerMessageMapper
{
    public static function fromWebhookPayload(array $payload): array
    {
        $conversations = [];

        foreach ($payload['entry'] ?? [] as $entry) {
            $pageId = $entry['id'] ?? null;

            foreach ($entry['messaging'] ?? [] as $event) {
                $senderId = Arr::get($event, 'sender.id');
                $message  = Arr::get($event, 'message');

                if (!$senderId || !$message || !$pageId) {
                    continue;
                }

                // 🔹 Conversation unique key (one per sender/page)
                $conversationKey = "{$pageId}_{$senderId}";

                // 🔹 Create the conversation if it doesn’t exist yet
                if (!isset($conversations[$conversationKey])) {
                    $conversationDTO = new ConversationDTO(
                        contact_id: null,
                        external_identifier_id: $senderId,
                        tenant_platform_id: null,
                        last_message_id: null,
                        unread_count: 0,
                        contact_identifier_id: $senderId,
                        contact_name: Arr::get($event, 'sender.name', 'Messenger User'),
                        title: 'Messenger Chat',
                        is_muted: false,
                        is_story: false,
                        type: 1,
                        platform: 'messenger',
                        sent_at: Carbon::now()->toDateTimeString(),
                        metadata: $event,
                        messages: [],
                        platform_account_id: $pageId,
                    );

                    $conversations[$conversationKey] = $conversationDTO;
                }

                // 🔹 Create Message DTO
                $messageDTO = new MessageDTO(
                    conversation_id: '', // will be linked later
                    external_message_id: $message['mid'] ?? null,
                    sender: $senderId,
                    receiver: $pageId,
                    reply_to_message_id: null,
                    reply_to_external_message_id: null,
                    body: $message['text'] ?? null,
                    direction: 'in',
                    has_media: isset($message['attachments']),
                    sent_at: now()->toDateTimeString(),
                    platform_account_id: $pageId,
                    message_type: isset($message['attachments']) ? 'media' : 'text',
                );

                // 🔹 Append message to conversation
                $conversations[$conversationKey]->messages[] = $messageDTO;
            }
        }

        // Return all conversation DTOs
        return array_values($conversations);
    }

}
