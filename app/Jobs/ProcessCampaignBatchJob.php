<?php

namespace App\Jobs;

use App\Models\Tenant\Campaign;
use App\Services\Tenant\CampaignExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessCampaignBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Campaign $campaign,
        public Collection $customers
    ) {
        //
    }

    public function handle(CampaignExecutionService $campaignService): void
    {
        try {
            Log::info('Processing campaign batch', [
                'campaign_id' => $this->campaign->id,
                'batch_size' => $this->customers->count(),
            ]);

            foreach ($this->customers as $index => $customer) {
                try {
                    // Send message to customer
                    $campaignService->sendToCustomer($this->campaign, $customer);
                    
                    // Rate limiting: delay between messages
                    // WhatsApp: 80 messages/second, Facebook: varies
                    if ($index > 0 && $index % 50 === 0) {
                        sleep(1); // Pause 1 second every 50 messages
                    }
                    
                } catch (\Exception $e) {
                    Log::error('Error sending campaign message to customer', [
                        'campaign_id' => $this->campaign->id,
                        'customer_id' => $customer->id,
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Continue with next customer
                    continue;
                }
            }

            // Check if campaign is complete
            $this->checkCampaignCompletion();

        } catch (\Exception $e) {
            Log::error('Error processing campaign batch', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Check if all batches are complete and mark campaign as complete.
     */
    protected function checkCampaignCompletion(): void
    {
        $totalRecipients = $this->campaign->customers()->count();
        $processedCount = $this->campaign->messages()
            ->whereIn('status', ['sent', 'delivered', 'read', 'failed'])
            ->count();

        if ($processedCount >= $totalRecipients) {
            $this->campaign->complete();
            
            Log::info('Campaign completed', [
                'campaign_id' => $this->campaign->id,
                'total_sent' => $processedCount,
            ]);
        }
    }
}

