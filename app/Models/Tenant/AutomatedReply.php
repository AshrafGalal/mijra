<?php

namespace App\Models\Tenant;

class AutomatedReply extends BaseTenantModel
{
    protected $fillable = [
        'name',
        'trigger_type',
        'keywords',
        'reply_message',
        'reply_type',
        'reply_metadata',
        'is_active',
        'priority',
        'conditions',
    ];

    protected $casts = [
        'keywords' => 'json',
        'reply_metadata' => 'json',
        'conditions' => 'json',
        'is_active' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * Scope for active rules.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by trigger type.
     */
    public function scopeTriggerType($query, string $type)
    {
        return $query->where('trigger_type', $type);
    }

    /**
     * Check if keyword matches.
     */
    public function matchesKeyword(string $message): bool
    {
        if (empty($this->keywords)) {
            return false;
        }

        $messageLower = strtolower($message);

        foreach ($this->keywords as $keyword) {
            if (str_contains($messageLower, strtolower($keyword))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if conditions are met.
     */
    public function conditionsMet(array $context = []): bool
    {
        if (empty($this->conditions)) {
            return true;
        }

        // Check platform condition
        if (isset($this->conditions['platforms'])) {
            $platform = $context['platform'] ?? null;
            if (!in_array($platform, $this->conditions['platforms'])) {
                return false;
            }
        }

        // Check time condition
        if (isset($this->conditions['time'])) {
            $now = now();
            $startTime = $this->conditions['time']['start'] ?? '00:00';
            $endTime = $this->conditions['time']['end'] ?? '23:59';
            
            if (!$now->between(
                now()->setTimeFromTimeString($startTime),
                now()->setTimeFromTimeString($endTime)
            )) {
                return false;
            }
        }

        return true;
    }
}

