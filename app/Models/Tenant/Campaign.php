<?php

namespace App\Models\Tenant;

use App\Enum\CampaignStatusEnum;
use App\Enum\CampaignTargetEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Campaign extends BaseTenantModel
{
    protected $fillable = [
        'title',
        'content',
        'channel',
        'type',
        'template_id',
        'status',
        'target',
        'started_at',
        'scheduled_at',
        'completed_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    /**
     * Get the template used for this campaign.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(Template::class);
    }

    /**
     * Get the customers targeted by this campaign.
     */
    public function customers(): BelongsToMany
    {
        return $this->belongsToMany(Customer::class, 'campaign_customers')
            ->withTimestamps();
    }

    /**
     * Get the campaign messages sent.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(CampaignMessage::class);
    }

    /**
     * Scope for active campaigns.
     */
    public function scopeActive($query)
    {
        return $query->where('status', CampaignStatusEnum::ACTIVE->value);
    }

    /**
     * Scope for scheduled campaigns.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', CampaignStatusEnum::SCHEDULED->value)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    /**
     * Scope for completed campaigns.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', CampaignStatusEnum::COMPLETED->value);
    }

    /**
     * Check if campaign is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === CampaignStatusEnum::SCHEDULED->value && 
               $this->scheduled_at && 
               $this->scheduled_at->isFuture();
    }

    /**
     * Check if campaign is running.
     */
    public function isRunning(): bool
    {
        return $this->status === CampaignStatusEnum::ACTIVE->value && 
               $this->started_at && 
               !$this->completed_at;
    }

    /**
     * Check if campaign is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === CampaignStatusEnum::COMPLETED->value || 
               $this->completed_at !== null;
    }

    /**
     * Start the campaign.
     */
    public function start(): void
    {
        $this->update([
            'status' => CampaignStatusEnum::ACTIVE->value,
            'started_at' => now(),
        ]);
    }

    /**
     * Pause the campaign.
     */
    public function pause(): void
    {
        $this->update([
            'status' => CampaignStatusEnum::PAUSED->value,
        ]);
    }

    /**
     * Resume the campaign.
     */
    public function resume(): void
    {
        $this->update([
            'status' => CampaignStatusEnum::ACTIVE->value,
        ]);
    }

    /**
     * Complete the campaign.
     */
    public function complete(): void
    {
        $this->update([
            'status' => CampaignStatusEnum::COMPLETED->value,
            'completed_at' => now(),
        ]);
    }

    /**
     * Get campaign progress.
     */
    public function getProgress(): array
    {
        $totalRecipients = $this->customers()->count();
        $sentCount = $this->messages()->where('status', 'sent')->count();
        $deliveredCount = $this->messages()->where('status', 'delivered')->count();
        $readCount = $this->messages()->where('status', 'read')->count();
        $failedCount = $this->messages()->where('status', 'failed')->count();

        return [
            'total_recipients' => $totalRecipients,
            'sent' => $sentCount,
            'delivered' => $deliveredCount,
            'read' => $readCount,
            'failed' => $failedCount,
            'pending' => $totalRecipients - $sentCount,
            'percentage' => $totalRecipients > 0 ? round(($sentCount / $totalRecipients) * 100, 2) : 0,
        ];
    }
}
