<?php

namespace App\Models\Tenant;

use App\Enum\ConversationStatusEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends BaseTenantModel
{
    protected $fillable = [
        'customer_id',
        'platform',
        'platform_conversation_id',
        'status',
        'assigned_to',
        'channel_type',
        'last_message_at',
        'first_response_at',
        'resolved_at',
        'message_count',
        'unread_count',
        'metadata',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at' => 'datetime',
        'metadata' => 'json',
        'message_count' => 'integer',
        'unread_count' => 'integer',
    ];

    protected $with = ['customer'];

    /**
     * Get the customer that owns the conversation.
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the user assigned to the conversation.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the messages for the conversation.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    /**
     * Get the latest message.
     */
    public function latestMessage(): HasMany
    {
        return $this->hasMany(Message::class)->latest()->limit(1);
    }

    /**
     * Get the notes for the conversation.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(ConversationNote::class)->orderByDesc('is_pinned')->orderByDesc('created_at');
    }

    /**
     * Get the tags associated with the conversation.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(ConversationTag::class, 'conversation_tag', 'conversation_id', 'conversation_tag_id')
            ->withTimestamps();
    }

    /**
     * Get the assignment history.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(ConversationAssignment::class)->orderByDesc('assigned_at');
    }

    /**
     * Get the current assignment.
     */
    public function currentAssignment(): HasMany
    {
        return $this->hasMany(ConversationAssignment::class)
            ->whereNull('unassigned_at')
            ->latest();
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by platform.
     */
    public function scopePlatform($query, $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to filter by assigned user.
     */
    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    /**
     * Scope for unassigned conversations.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Scope for conversations with unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('unread_count', '>', 0);
    }

    /**
     * Scope for open conversations (new or open status).
     */
    public function scopeOpen($query)
    {
        return $query->whereIn('status', [ConversationStatusEnum::NEW->value, ConversationStatusEnum::OPEN->value]);
    }

    /**
     * Mark conversation as read.
     */
    public function markAsRead(): void
    {
        $this->update(['unread_count' => 0]);
    }

    /**
     * Increment unread count.
     */
    public function incrementUnreadCount(): void
    {
        $this->increment('unread_count');
    }

    /**
     * Update last message timestamp.
     */
    public function updateLastMessageTime(): void
    {
        $this->update(['last_message_at' => now()]);
    }

    /**
     * Assign conversation to a user.
     */
    public function assignTo(int $userId, int $assignedBy = null, string $type = 'manual'): void
    {
        $previousUserId = $this->assigned_to;
        
        // Unassign current assignment if exists
        $this->assignments()->whereNull('unassigned_at')->update(['unassigned_at' => now()]);

        // Create new assignment
        $this->assignments()->create([
            'assigned_to' => $userId,
            'assigned_by' => $assignedBy,
            'assignment_type' => $type,
            'assigned_at' => now(),
        ]);

        $this->update(['assigned_to' => $userId]);
        
        // Broadcast assignment event
        broadcast(new \App\Events\ConversationAssigned($this->fresh(), $userId, $previousUserId))->toOthers();
    }

    /**
     * Unassign conversation.
     */
    public function unassign(): void
    {
        $this->assignments()->whereNull('unassigned_at')->update(['unassigned_at' => now()]);
        $this->update(['assigned_to' => null]);
    }

    /**
     * Change conversation status.
     */
    public function changeStatus(string $status): void
    {
        $oldStatus = $this->status;
        
        $this->update([
            'status' => $status,
            'resolved_at' => $status === ConversationStatusEnum::RESOLVED->value ? now() : null,
        ]);
        
        // Broadcast status change event
        broadcast(new \App\Events\ConversationStatusChanged($this->fresh(), $oldStatus, $status))->toOthers();
    }
}

