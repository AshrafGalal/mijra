<?php

namespace App\Services\Tenant;

use App\Enum\ConversationStatusEnum;
use App\Models\Tenant\Conversation;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Filters\ConversationFilters;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ConversationService extends BaseService
{
    protected function getFilterClass(): ?string
    {
        return ConversationFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return Conversation::query();
    }

    /**
     * Get paginated conversations with filters.
     */
    public function paginate(array $filters = [], int $limit = 15): LengthAwarePaginator
    {
        return $this->getQuery($filters)
            ->with([
                'customer:id,name,phone,email',
                'assignedUser:id,name,email',
                'latestMessage',
                'tags',
            ])
            ->orderByDesc('last_message_at')
            ->paginate($limit);
    }

    /**
     * Find or create conversation for a customer and platform.
     */
    public function findOrCreate(
        int $customerId,
        string $platform,
        string $platformConversationId = null
    ): Conversation {
        $query = $this->baseQuery()
            ->where('customer_id', $customerId)
            ->where('platform', $platform);

        if ($platformConversationId) {
            $query->where('platform_conversation_id', $platformConversationId);
        }

        $conversation = $query->first();

        if (!$conversation) {
            $conversation = $this->create([
                'customer_id' => $customerId,
                'platform' => $platform,
                'platform_conversation_id' => $platformConversationId,
                'status' => ConversationStatusEnum::NEW->value,
                'last_message_at' => now(),
            ]);
        }

        return $conversation;
    }

    /**
     * Create a new conversation.
     */
    public function create(array $data): Conversation
    {
        return DB::connection('tenant')->transaction(function () use ($data) {
            return $this->baseQuery()->create($data);
        });
    }

    /**
     * Assign conversation to a user.
     */
    public function assign(int $conversationId, int $userId, int $assignedBy = null, string $type = 'manual'): Conversation
    {
        $conversation = $this->findById($conversationId);
        $conversation->assignTo($userId, $assignedBy, $type);
        
        return $conversation->fresh();
    }

    /**
     * Unassign conversation.
     */
    public function unassign(int $conversationId): Conversation
    {
        $conversation = $this->findById($conversationId);
        $conversation->unassign();
        
        return $conversation->fresh();
    }

    /**
     * Change conversation status.
     */
    public function changeStatus(int $conversationId, string $status): Conversation
    {
        $conversation = $this->findById($conversationId);
        $conversation->changeStatus($status);
        
        return $conversation->fresh();
    }

    /**
     * Add note to conversation.
     */
    public function addNote(int $conversationId, int $userId, string $content, bool $isPinned = false): void
    {
        $conversation = $this->findById($conversationId);
        
        $conversation->notes()->create([
            'user_id' => $userId,
            'content' => $content,
            'is_pinned' => $isPinned,
        ]);
    }

    /**
     * Add tags to conversation.
     */
    public function addTags(int $conversationId, array $tagIds): void
    {
        $conversation = $this->findById($conversationId);
        $conversation->tags()->syncWithoutDetaching($tagIds);
    }

    /**
     * Remove tags from conversation.
     */
    public function removeTags(int $conversationId, array $tagIds): void
    {
        $conversation = $this->findById($conversationId);
        $conversation->tags()->detach($tagIds);
    }

    /**
     * Mark conversation as read.
     */
    public function markAsRead(int $conversationId): void
    {
        $conversation = $this->findById($conversationId);
        $conversation->markAsRead();
    }

    /**
     * Get conversation statistics.
     */
    public function getStatistics(): array
    {
        $stats = DB::connection('tenant')->table('conversations')
            ->selectRaw('
                status,
                COUNT(*) as count,
                SUM(unread_count) as total_unread,
                AVG(message_count) as avg_messages
            ')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $result = [];
        foreach (ConversationStatusEnum::cases() as $status) {
            $stat = $stats->get($status->value);
            $result[$status->value] = [
                'label' => $status->label(),
                'count' => $stat->count ?? 0,
                'total_unread' => $stat->total_unread ?? 0,
                'avg_messages' => round($stat->avg_messages ?? 0, 1),
            ];
        }

        $result['total'] = $this->baseQuery()->count();
        $result['unassigned'] = $this->baseQuery()->whereNull('assigned_to')->count();
        $result['with_unread'] = $this->baseQuery()->where('unread_count', '>', 0)->count();

        return $result;
    }

    /**
     * Get conversations by customer.
     */
    public function getByCustomer(int $customerId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->baseQuery()
            ->where('customer_id', $customerId)
            ->with(['latestMessage', 'tags'])
            ->orderByDesc('last_message_at')
            ->get();
    }

    /**
     * Get open conversations assigned to user.
     */
    public function getOpenByUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return $this->baseQuery()
            ->where('assigned_to', $userId)
            ->whereIn('status', [ConversationStatusEnum::NEW->value, ConversationStatusEnum::OPEN->value])
            ->with(['customer', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();
    }
}

