<?php

namespace App\Jobs;

use App\Models\Tenant\Message;
use App\Services\Platforms\EmailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendEmailMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        public Message $message
    ) {
        //
    }

    public function handle(EmailService $emailService): void
    {
        $conversation = $this->message->conversation;
        $result = $emailService->sendEmail($conversation, $this->message);

        if (!$result['success']) {
            throw new \Exception($result['error'] ?? 'Failed to send email');
        }
    }
}

