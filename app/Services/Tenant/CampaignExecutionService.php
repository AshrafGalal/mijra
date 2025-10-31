<?php

namespace App\Services\Tenant;

use App\Enum\CampaignStatusEnum;
use App\Enum\CampaignTargetEnum;
use App\Models\Tenant\Campaign;
use App\Models\Tenant\Customer;
use App\Models\Tenant\CustomerGroup;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CampaignExecutionService
{
    public function __construct(
        protected ConversationService $conversationService,
        protected MessageService $messageService
    ) {
    }

    /**
     * Execute campaign immediately or schedule it.
     */
    public function execute(Campaign $campaign): void
    {
        if ($campaign->scheduled_at && $campaign->scheduled_at->isFuture()) {
            // Schedule for later
            dispatch(new \App\Jobs\ExecuteCampaignJob($campaign))
                ->delay($campaign->scheduled_at);
            
            $campaign->update(['status' => CampaignStatusEnum::SCHEDULED->value]);
            
            Log::info('Campaign scheduled', [
                'campaign_id' => $campaign->id,
                'scheduled_at' => $campaign->scheduled_at,
            ]);
        } else {
            // Execute immediately
            dispatch(new \App\Jobs\ExecuteCampaignJob($campaign));
            
            $campaign->start();
            
            Log::info('Campaign started', ['campaign_id' => $campaign->id]);
        }
    }

    /**
     * Get target audience for campaign.
     */
    public function getTargetAudience(Campaign $campaign): Collection
    {
        return match ($campaign->target) {
            CampaignTargetEnum::ALL_CUSTOMERS->value => $this->getAllCustomers(),
            CampaignTargetEnum::SPECIFIC_CUSTOMERS->value => $this->getSpecificCustomers($campaign),
            CampaignTargetEnum::CUSTOMER_GROUP->value => $this->getCustomersByGroup($campaign),
            CampaignTargetEnum::CUSTOMER_SEGMENT->value => $this->getCustomersBySegment($campaign),
            default => collect(),
        };
    }

    /**
     * Get all active customers.
     */
    protected function getAllCustomers(): Collection
    {
        return Customer::where('status', '!=', \App\Enum\CustomerStatusEnum::INACTIVE->value)
            ->get();
    }

    /**
     * Get specific customers attached to campaign.
     */
    protected function getSpecificCustomers(Campaign $campaign): Collection
    {
        return $campaign->customers;
    }

    /**
     * Get customers by group.
     */
    protected function getCustomersByGroup(Campaign $campaign): Collection
    {
        // Assuming campaign metadata contains group_id
        $metadata = $campaign->metadata ?? [];
        $groupId = $metadata['group_id'] ?? null;

        if (!$groupId) {
            return collect();
        }

        return Customer::whereHas('groups', function ($query) use ($groupId) {
            $query->where('groups.id', $groupId);
        })->get();
    }

    /**
     * Get customers by segment (advanced filtering).
     */
    protected function getCustomersBySegment(Campaign $campaign): Collection
    {
        $metadata = $campaign->metadata ?? [];
        $segment = $metadata['segment'] ?? [];

        $query = Customer::query();

        // Apply segment filters
        if (isset($segment['status'])) {
            $query->whereIn('status', (array) $segment['status']);
        }

        if (isset($segment['source'])) {
            $query->whereIn('source', (array) $segment['source']);
        }

        if (isset($segment['tags'])) {
            $query->whereJsonContains('tags', $segment['tags']);
        }

        if (isset($segment['country'])) {
            $query->whereIn('country', (array) $segment['country']);
        }

        if (isset($segment['created_after'])) {
            $query->where('created_at', '>=', $segment['created_after']);
        }

        if (isset($segment['created_before'])) {
            $query->where('created_at', '<=', $segment['created_before']);
        }

        return $query->get();
    }

    /**
     * Send campaign message to a customer.
     */
    public function sendToCustomer(Campaign $campaign, Customer $customer): void
    {
        // Find or create conversation for this platform
        $conversation = $this->conversationService->findOrCreate(
            customerId: $customer->id,
            platform: $campaign->channel,
            platformConversationId: $this->getCustomerPlatformId($customer, $campaign->channel)
        );

        // Create the message
        $message = $this->messageService->createOutboundMessage(
            conversationId: $conversation->id,
            content: $this->prepareContent($campaign, $customer),
            userId: 1, // System user - should be configurable
            type: $campaign->template ? 'template' : 'text',
            metadata: [
                'campaign_id' => $campaign->id,
                'template_id' => $campaign->template_id,
                'template_name' => $campaign->template?->name,
            ]
        );

        // Create campaign message tracking
        $campaignMessage = $campaign->messages()->create([
            'customer_id' => $customer->id,
            'message_id' => $message->id,
            'status' => 'pending',
        ]);

        // Dispatch job to send via platform
        dispatch(new \App\Jobs\SendCampaignMessageJob($message, $campaignMessage));
    }

    /**
     * Prepare content with variable substitution.
     */
    protected function prepareContent(Campaign $campaign, Customer $customer): string
    {
        $content = $campaign->content;

        // Replace common variables
        $variables = [
            '{{customer_name}}' => $customer->name,
            '{{customer_email}}' => $customer->email,
            '{{customer_phone}}' => $customer->phone,
            '{{customer_city}}' => $customer->city,
            '{{customer_country}}' => $customer->country,
        ];

        return str_replace(array_keys($variables), array_values($variables), $content);
    }

    /**
     * Get customer's platform ID (phone for WhatsApp, sender ID for Facebook/Instagram).
     */
    protected function getCustomerPlatformId(Customer $customer, string $platform): ?string
    {
        return match ($platform) {
            'whatsapp' => $customer->phone,
            'facebook', 'instagram' => $customer->socialAccounts()
                ->where('provider_name', $platform)
                ->value('platform_account_id'),
            default => null,
        };
    }

    /**
     * Get campaign analytics.
     */
    public function getAnalytics(Campaign $campaign): array
    {
        $progress = $campaign->getProgress();
        
        $totalRecipients = $progress['total_recipients'];
        $sent = $progress['sent'];
        $delivered = $progress['delivered'];
        $read = $progress['read'];
        $failed = $progress['failed'];

        return [
            'campaign_id' => $campaign->id,
            'title' => $campaign->title,
            'channel' => $campaign->channel,
            'status' => $campaign->status,
            'started_at' => $campaign->started_at?->toISOString(),
            'completed_at' => $campaign->completed_at?->toISOString(),
            'progress' => $progress,
            'delivery_rate' => $sent > 0 ? round(($delivered / $sent) * 100, 2) : 0,
            'read_rate' => $delivered > 0 ? round(($read / $delivered) * 100, 2) : 0,
            'failure_rate' => $sent > 0 ? round(($failed / $sent) * 100, 2) : 0,
        ];
    }
}

