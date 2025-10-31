<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends BaseTenantModel
{
    protected $fillable = [
        'name',
        'description',
        'first_response_time_minutes',
        'resolution_time_hours',
        'conditions',
        'is_active',
        'is_default',
    ];

    protected $casts = [
        'conditions' => 'json',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'first_response_time_minutes' => 'integer',
        'resolution_time_hours' => 'integer',
    ];

    /**
     * Get conversations using this SLA.
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }

    /**
     * Check if SLA applies to a conversation.
     */
    public function appliesTo(Conversation $conversation): bool
    {
        if (empty($this->conditions)) {
            return true;
        }

        // Check priority condition
        if (isset($this->conditions['priority'])) {
            $priority = $conversation->metadata['priority'] ?? null;
            if (!in_array($priority, (array) $this->conditions['priority'])) {
                return false;
            }
        }

        // Check platform condition
        if (isset($this->conditions['platforms'])) {
            if (!in_array($conversation->platform, (array) $this->conditions['platforms'])) {
                return false;
            }
        }

        // Check customer status
        if (isset($this->conditions['customer_status'])) {
            if (!in_array($conversation->customer->status, (array) $this->conditions['customer_status'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Calculate SLA deadlines for a conversation.
     */
    public function calculateDeadlines(\DateTime $startTime): array
    {
        return [
            'first_response_due_at' => (clone $startTime)->modify("+{$this->first_response_time_minutes} minutes"),
            'resolution_due_at' => (clone $startTime)->modify("+{$this->resolution_time_hours} hours"),
        ];
    }
}

