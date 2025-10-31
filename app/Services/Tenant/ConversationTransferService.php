<?php

namespace App\Services\Tenant;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\ConversationTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConversationTransferService
{
    public function __construct(
        protected ConversationService $conversationService,
        protected MessageService $messageService
    ) {
    }

    /**
     * Transfer conversation to another user.
     */
    public function transfer(
        int $conversationId,
        int $toUserId,
        int $transferredBy,
        ?string $reason = null
    ): Conversation {
        return DB::connection('tenant')->transaction(function () use (
            $conversationId,
            $toUserId,
            $transferredBy,
            $reason
        ) {
            $conversation = Conversation::findOrFail($conversationId);
            $fromUserId = $conversation->assigned_to;

            if (!$fromUserId) {
                throw new \Exception('Cannot transfer unassigned conversation');
            }

            if ($fromUserId === $toUserId) {
                throw new \Exception('Cannot transfer to the same user');
            }

            // Record transfer
            ConversationTransfer::create([
                'conversation_id' => $conversationId,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'transferred_by' => $transferredBy,
                'reason' => $reason,
                'transferred_at' => now(),
            ]);

            // Reassign conversation
            $this->conversationService->assign(
                conversationId: $conversationId,
                userId: $toUserId,
                assignedBy: $transferredBy,
                type: 'transfer'
            );

            // Add system note about transfer
            $fromUser = \App\Models\Tenant\User::find($fromUserId);
            $toUser = \App\Models\Tenant\User::find($toUserId);
            
            $noteContent = "Conversation transferred from {$fromUser->name} to {$toUser->name}";
            if ($reason) {
                $noteContent .= "\nReason: {$reason}";
            }

            $conversation->notes()->create([
                'user_id' => $transferredBy,
                'content' => $noteContent,
                'is_pinned' => false,
            ]);

            Log::info('Conversation transferred', [
                'conversation_id' => $conversationId,
                'from_user' => $fromUserId,
                'to_user' => $toUserId,
            ]);

            // Broadcast transfer event
            $transfer = ConversationTransfer::where('conversation_id', $conversationId)
                ->latest()
                ->first();
            
            if ($transfer) {
                broadcast(new \App\Events\ConversationTransferred($transfer))->toOthers();
            }

            return $conversation->fresh();
        });
    }

    /**
     * Get transfer history for a conversation.
     */
    public function getTransferHistory(int $conversationId): \Illuminate\Database\Eloquent\Collection
    {
        return ConversationTransfer::where('conversation_id', $conversationId)
            ->with(['fromUser', 'toUser', 'transferredByUser'])
            ->orderByDesc('transferred_at')
            ->get();
    }

    /**
     * Get transfer statistics for a user.
     */
    public function getUserTransferStats(int $userId): array
    {
        return [
            'transfers_sent' => ConversationTransfer::where('from_user_id', $userId)->count(),
            'transfers_received' => ConversationTransfer::where('to_user_id', $userId)->count(),
            'transfers_initiated' => ConversationTransfer::where('transferred_by', $userId)->count(),
        ];
    }
}

