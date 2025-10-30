<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationAssignment extends BaseTenantModel
{
    protected $fillable = [
        'conversation_id',
        'assigned_to',
        'assigned_by',
        'assignment_type',
        'assigned_at',
        'unassigned_at',
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'unassigned_at' => 'datetime',
    ];

    /**
     * Get the conversation.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user assigned to.
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the user who assigned.
     */
    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Check if assignment is active.
     */
    public function isActive(): bool
    {
        return is_null($this->unassigned_at);
    }

    /**
     * Get duration of assignment in minutes.
     */
    public function getDurationAttribute(): ?int
    {
        if (!$this->assigned_at) {
            return null;
        }

        $endTime = $this->unassigned_at ?? now();
        return $this->assigned_at->diffInMinutes($endTime);
    }
}

