<?php

namespace App\Jobs;

use App\Models\Tenant\Message;
use App\Services\Platforms\SmsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendSmsMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [30, 60, 120];

    public function __construct(
        public Message $message
    ) {
        //
    }

    public function handle(SmsService $smsService): void
    {
        $conversation = $this->message->conversation;
        $result = $smsService->sendSms($conversation, $this->message);

        if (!$result['success']) {
            throw new \Exception($result['error'] ?? 'Failed to send SMS');
        }
    }
}

