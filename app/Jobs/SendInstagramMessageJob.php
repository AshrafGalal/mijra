<?php

namespace App\Jobs;

use App\Enum\MessageTypeEnum;
use App\Models\Tenant\Message;
use App\Services\Platforms\InstagramService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendInstagramMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public Message $message
    ) {
        //
    }

    public function handle(InstagramService $instagramService): void
    {
        try {
            $conversation = $this->message->conversation;

            $result = match ($this->message->type) {
                MessageTypeEnum::TEXT->value => $instagramService->sendTextMessage($conversation, $this->message),
                MessageTypeEnum::IMAGE->value => $this->sendImageMessage($instagramService, $conversation),
                MessageTypeEnum::VIDEO->value => $this->sendVideoMessage($instagramService, $conversation),
                default => $instagramService->sendTextMessage($conversation, $this->message),
            };

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Failed to send Instagram message');
            }

            Log::info('Instagram message sent via job', [
                'message_id' => $this->message->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in SendInstagramMessageJob', [
                'message_id' => $this->message->id,
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->message->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    protected function sendImageMessage(InstagramService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendImageMessage($conversation, $this->message, $attachment->url);
    }

    protected function sendVideoMessage(InstagramService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendVideoMessage($conversation, $this->message, $attachment->url);
    }
}

