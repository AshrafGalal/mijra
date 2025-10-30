<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationNote extends BaseTenantModel
{
    protected $fillable = [
        'conversation_id',
        'user_id',
        'content',
        'is_pinned',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
    ];

    protected $with = ['user'];

    /**
     * Get the conversation that owns the note.
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Get the user who created the note.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Toggle pin status.
     */
    public function togglePin(): void
    {
        $this->update(['is_pinned' => !$this->is_pinned]);
    }
}



