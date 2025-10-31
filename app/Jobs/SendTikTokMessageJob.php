<?php

namespace App\Jobs;

use App\Enum\MessageTypeEnum;
use App\Models\Tenant\Message;
use App\Services\Platforms\TikTokService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTikTokMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public Message $message
    ) {
        //
    }

    public function handle(TikTokService $tiktokService): void
    {
        $conversation = $this->message->conversation;

        $result = match ($this->message->type) {
            MessageTypeEnum::TEXT->value => $tiktokService->sendTextMessage($conversation, $this->message),
            MessageTypeEnum::IMAGE->value => $this->sendImageMessage($tiktokService, $conversation),
            default => $tiktokService->sendTextMessage($conversation, $this->message),
        };

        if (!$result['success']) {
            throw new \Exception($result['error']['message'] ?? 'Failed to send TikTok message');
        }
    }

    protected function sendImageMessage(TikTokService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        return $attachment 
            ? $service->sendImageMessage($conversation, $this->message, $attachment->url)
            : $service->sendTextMessage($conversation, $this->message);
    }
}

