<?php

namespace App\Models\Tenant;

use App\Enum\MessageDirectionEnum;
use App\Enum\MessageStatusEnum;
use App\Enum\MessageTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends BaseTenantModel
{
    protected $fillable = [
        'conversation_id',
        'platform_message_id',
        'direction',
        'type',
        'content',
        'user_id',
        'sender_type',
        'status',
        'delivered_at',
        'read_at',
        'failed_at',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
        'metadata' => 'json',
    ];

    protected $with = ['attachments'];

    /**
     * Get the conversation that owns the message.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user who sent the message (for outbound messages).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    /**
     * Get the status updates for the message.
     */
    public function statusUpdates(): HasMany
    {
        return $this->hasMany(MessageStatusUpdate::class)->orderBy('status_at');
    }

    /**
     * Scope to filter by direction.
     */
    public function scopeDirection($query, $direction)
    {
        return $query->where('direction', $direction);
    }

    /**
     * Scope for inbound messages.
     */
    public function scopeInbound($query)
    {
        return $query->where('direction', MessageDirectionEnum::INBOUND->value);
    }

    /**
     * Scope for outbound messages.
     */
    public function scopeOutbound($query)
    {
        return $query->where('direction', MessageDirectionEnum::OUTBOUND->value);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if message is inbound.
     */
    public function isInbound(): bool
    {
        return $this->direction === MessageDirectionEnum::INBOUND->value;
    }

    /**
     * Check if message is outbound.
     */
    public function isOutbound(): bool
    {
        return $this->direction === MessageDirectionEnum::OUTBOUND->value;
    }

    /**
     * Check if message has attachments.
     */
    public function hasAttachments(): bool
    {
        return $this->attachments()->exists();
    }

    /**
     * Mark message as sent.
     */
    public function markAsSent(string $platformMessageId = null): void
    {
        $this->update([
            'status' => MessageStatusEnum::SENT->value,
            'platform_message_id' => $platformMessageId ?? $this->platform_message_id,
        ]);

        $this->recordStatusUpdate(MessageStatusEnum::SENT->value);
        
        // Broadcast status update
        broadcast(new \App\Events\MessageStatusUpdated($this, MessageStatusEnum::SENT->value))->toOthers();
    }

    /**
     * Mark message as delivered.
     */
    public function markAsDelivered(): void
    {
        $this->update([
            'status' => MessageStatusEnum::DELIVERED->value,
            'delivered_at' => now(),
        ]);

        $this->recordStatusUpdate(MessageStatusEnum::DELIVERED->value);
        
        // Broadcast status update
        broadcast(new \App\Events\MessageStatusUpdated($this, MessageStatusEnum::DELIVERED->value))->toOthers();
    }

    /**
     * Mark message as read.
     */
    public function markAsRead(): void
    {
        $this->update([
            'status' => MessageStatusEnum::READ->value,
            'read_at' => now(),
        ]);

        $this->recordStatusUpdate(MessageStatusEnum::READ->value);
        
        // Broadcast status update
        broadcast(new \App\Events\MessageStatusUpdated($this, MessageStatusEnum::READ->value))->toOthers();
    }

    /**
     * Mark message as failed.
     */
    public function markAsFailed(string $errorMessage = null): void
    {
        $this->update([
            'status' => MessageStatusEnum::FAILED->value,
            'failed_at' => now(),
            'error_message' => $errorMessage,
        ]);

        $this->recordStatusUpdate(MessageStatusEnum::FAILED->value, ['error' => $errorMessage]);
    }

    /**
     * Record a status update.
     */
    protected function recordStatusUpdate(string $status, array $metadata = []): void
    {
        $this->statusUpdates()->create([
            'status' => $status,
            'status_at' => now(),
            'metadata' => $metadata,
        ]);
    }

    /**
     * Boot method to handle events.
     */
    protected static function boot()
    {
        parent::boot();

        // Update conversation counters when message is created
        static::created(function ($message) {
            $conversation = $message->conversation;
            $conversation->increment('message_count');
            $conversation->updateLastMessageTime();

            // Increment unread count for inbound messages
            if ($message->isInbound()) {
                $conversation->incrementUnreadCount();
            }

            // Set first response time for outbound messages
            if ($message->isOutbound() && !$conversation->first_response_at) {
                $conversation->update(['first_response_at' => now()]);
            }

            // Auto-open conversation if it's new
            if ($conversation->status === \App\Enum\ConversationStatusEnum::NEW->value) {
                $conversation->changeStatus(\App\Enum\ConversationStatusEnum::OPEN->value);
            }

            // Broadcast new message event
            broadcast(new \App\Events\NewMessageReceived($message))->toOthers();
        });
    }
}

