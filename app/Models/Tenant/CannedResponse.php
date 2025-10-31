<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CannedResponse extends BaseTenantModel
{
    protected $fillable = [
        'title',
        'shortcut',
        'content',
        'category',
        'user_id',
        'is_shared',
        'platforms',
        'usage_count',
        'last_used_at',
    ];

    protected $casts = [
        'is_shared' => 'boolean',
        'platforms' => 'json',
        'usage_count' => 'integer',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the user who owns this response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for shared responses.
     */
    public function scopeShared($query)
    {
        return $query->where('is_shared', true);
    }

    /**
     * Scope for user's personal responses.
     */
    public function scopePersonal($query, int $userId)
    {
        return $query->where('user_id', $userId)->where('is_shared', false);
    }

    /**
     * Scope for responses available to a user.
     */
    public function scopeAvailableFor($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('is_shared', true)
              ->orWhere('user_id', $userId);
        });
    }

    /**
     * Scope by category.
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope by platform.
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where(function ($q) use ($platform) {
            $q->whereNull('platforms')
              ->orWhereJsonContains('platforms', $platform);
        });
    }

    /**
     * Search by title or content.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('content', 'like', "%{$search}%")
              ->orWhere('shortcut', 'like', "%{$search}%");
        });
    }

    /**
     * Increment usage count.
     */
    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Get content with variables replaced.
     */
    public function getContentWithVariables(array $variables = []): string
    {
        $content = $this->content;

        foreach ($variables as $key => $value) {
            $content = str_replace("{{{$key}}}", $value, $content);
        }

        return $content;
    }
}

