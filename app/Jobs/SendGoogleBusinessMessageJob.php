<?php

namespace App\Jobs;

use App\Models\Tenant\Message;
use App\Services\Platforms\GoogleBusinessMessagesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendGoogleBusinessMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

    public function __construct(
        public Message $message
    ) {
        //
    }

    public function handle(GoogleBusinessMessagesService $gmbService): void
    {
        $conversation = $this->message->conversation;
        $result = $gmbService->sendTextMessage($conversation, $this->message);

        if (!$result['success']) {
            throw new \Exception($result['error']['message'] ?? 'Failed to send Google Business Message');
        }
    }
}

