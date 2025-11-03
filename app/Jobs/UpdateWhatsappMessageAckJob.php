<?php

namespace App\Jobs;

use App\Services\Tenant\MessageService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateWhatsappMessageAckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public string $external_message_id, public ?array $payload = [])
    {
        //
    }

    public function handle(MessageService $messageService): void
    {
        if (empty($this->payload)) {
            return;
        }
        $messageService->updateMessage($this->external_message_id, $this->payload);
    }
}
