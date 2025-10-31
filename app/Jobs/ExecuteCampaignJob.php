<?php

namespace App\Jobs;

use App\Models\Tenant\Campaign;
use App\Services\Tenant\CampaignExecutionService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecuteCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 3600; // 1 hour timeout

    public function __construct(
        public Campaign $campaign
    ) {
        //
    }

    public function handle(CampaignExecutionService $campaignService): void
    {
        try {
            // Get target audience
            $customers = $campaignService->getTargetAudience($this->campaign);

            if ($customers->isEmpty()) {
                Log::warning('Campaign has no target customers', ['campaign_id' => $this->campaign->id]);
                $this->campaign->complete();
                return;
            }

            Log::info('Starting campaign execution', [
                'campaign_id' => $this->campaign->id,
                'total_customers' => $customers->count(),
            ]);

            // Attach customers to campaign if not already attached
            if ($this->campaign->customers()->count() === 0) {
                $this->campaign->customers()->attach($customers->pluck('id'));
            }

            // Start the campaign if not already started
            if (!$this->campaign->started_at) {
                $this->campaign->start();
            }

            // Send to each customer (in batches to avoid overwhelming the queue)
            $customers->chunk(100)->each(function ($batch, $index) use ($campaignService) {
                // Stagger batches by 30 seconds each
                $delay = now()->addSeconds($index * 30);
                
                dispatch(new \App\Jobs\ProcessCampaignBatchJob($this->campaign, $batch))
                    ->delay($delay);
            });

            Log::info('Campaign batches dispatched', [
                'campaign_id' => $this->campaign->id,
                'batches' => ceil($customers->count() / 100),
            ]);

        } catch (\Exception $e) {
            Log::error('Error executing campaign', [
                'campaign_id' => $this->campaign->id,
                'error' => $e->getMessage(),
            ]);
            
            $this->campaign->update(['status' => \App\Enum\CampaignStatusEnum::FAILED->value]);
            throw $e;
        }
    }
}

