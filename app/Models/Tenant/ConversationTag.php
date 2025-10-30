<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConversationTag extends BaseTenantModel
{
    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    /**
     * Get the conversations with this tag.
     */
    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_tag', 'conversation_tag_id', 'conversation_id')
            ->withTimestamps();
    }

    /**
     * Scope to search by name.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}

