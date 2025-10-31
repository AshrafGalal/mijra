<?php

namespace App\Jobs;

use App\Models\Tenant\CampaignMessage;
use App\Models\Tenant\Message;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendCampaignMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2; // Fewer retries for campaigns
    public $backoff = [30, 60];

    public function __construct(
        public Message $message,
        public CampaignMessage $campaignMessage
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $conversation = $this->message->conversation;
            $platform = $conversation->platform;

            // Dispatch to appropriate platform
            $job = match ($platform) {
                'whatsapp' => new SendWhatsAppMessageJob($this->message),
                'facebook' => new SendFacebookMessageJob($this->message),
                'instagram' => new SendInstagramMessageJob($this->message),
                default => null,
            };

            if ($job) {
                dispatch($job);
                
                // Mark campaign message as sent
                $this->campaignMessage->markAsSent($this->message->id);
            }

        } catch (\Exception $e) {
            Log::error('Error sending campaign message', [
                'message_id' => $this->message->id,
                'campaign_message_id' => $this->campaignMessage->id,
                'error' => $e->getMessage(),
            ]);

            // Mark as failed if max retries exceeded
            if ($this->attempts() >= $this->tries) {
                $this->campaignMessage->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }
}

