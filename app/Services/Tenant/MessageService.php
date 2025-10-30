<?php

namespace App\Services\Tenant;

use App\Enum\MessageDirectionEnum;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class MessageService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return null;
    }

    protected function baseQuery(): Builder
    {
        return Message::query();
    }

    /**
     * Get paginated messages for a conversation.
     */
    public function getConversationMessages(int $conversationId, int $limit = 50): LengthAwarePaginator
    {
        return $this->baseQuery()
            ->where('conversation_id', $conversationId)
            ->with(['user:id,name', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->paginate($limit);
    }

    /**
     * Create an inbound message.
     */
    public function createInboundMessage(
        int $conversationId,
        string $content,
        string $type = 'text',
        string $platformMessageId = null,
        array $metadata = [],
        array $attachments = []
    ): Message {
        return DB::connection('tenant')->transaction(function () use (
            $conversationId,
            $content,
            $type,
            $platformMessageId,
            $metadata,
            $attachments
        ) {
            $message = $this->baseQuery()->create([
                'conversation_id' => $conversationId,
                'platform_message_id' => $platformMessageId,
                'direction' => MessageDirectionEnum::INBOUND->value,
                'type' => $type,
                'content' => $content,
                'sender_type' => 'customer',
                'status' => MessageStatusEnum::DELIVERED->value,
                'delivered_at' => now(),
                'metadata' => $metadata,
            ]);

            // Create attachments if any
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $message->attachments()->create($attachment);
                }
            }

            return $message->fresh('attachments');
        });
    }

    /**
     * Create an outbound message.
     */
    public function createOutboundMessage(
        int $conversationId,
        string $content,
        int $userId,
        string $type = 'text',
        array $metadata = [],
        array $attachments = []
    ): Message {
        return DB::connection('tenant')->transaction(function () use (
            $conversationId,
            $content,
            $userId,
            $type,
            $metadata,
            $attachments
        ) {
            $message = $this->baseQuery()->create([
                'conversation_id' => $conversationId,
                'direction' => MessageDirectionEnum::OUTBOUND->value,
                'type' => $type,
                'content' => $content,
                'user_id' => $userId,
                'sender_type' => 'user',
                'status' => MessageStatusEnum::PENDING->value,
                'metadata' => $metadata,
            ]);

            // Create attachments if any
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $message->attachments()->create($attachment);
                }
            }

            return $message->fresh('attachments');
        });
    }

    /**
     * Update message status.
     */
    public function updateStatus(int $messageId, string $status, string $platformMessageId = null): Message
    {
        $message = $this->findById($messageId);

        match ($status) {
            MessageStatusEnum::SENT->value => $message->markAsSent($platformMessageId),
            MessageStatusEnum::DELIVERED->value => $message->markAsDelivered(),
            MessageStatusEnum::READ->value => $message->markAsRead(),
            MessageStatusEnum::FAILED->value => $message->markAsFailed(),
            default => null,
        };

        return $message->fresh();
    }

    /**
     * Mark message as failed with error.
     */
    public function markAsFailed(int $messageId, string $errorMessage): Message
    {
        $message = $this->findById($messageId);
        $message->markAsFailed($errorMessage);
        
        return $message->fresh();
    }

    /**
     * Get message by platform message ID.
     */
    public function getByPlatformMessageId(string $platformMessageId): ?Message
    {
        return $this->baseQuery()
            ->where('platform_message_id', $platformMessageId)
            ->first();
    }

    /**
     * Get latest messages across conversations.
     */
    public function getLatestMessages(int $limit = 100): \Illuminate\Database\Eloquent\Collection
    {
        return $this->baseQuery()
            ->with(['conversation.customer', 'user:id,name'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Get message statistics for a conversation.
     */
    public function getConversationStats(int $conversationId): array
    {
        $stats = DB::connection('tenant')->table('messages')
            ->where('conversation_id', $conversationId)
            ->selectRaw('
                direction,
                COUNT(*) as count,
                SUM(CASE WHEN type != "text" THEN 1 ELSE 0 END) as media_count
            ')
            ->groupBy('direction')
            ->get()
            ->keyBy('direction');

        return [
            'total' => $stats->sum('count'),
            'inbound' => $stats->get(MessageDirectionEnum::INBOUND->value)->count ?? 0,
            'outbound' => $stats->get(MessageDirectionEnum::OUTBOUND->value)->count ?? 0,
            'media_messages' => $stats->sum('media_count'),
        ];
    }
}

