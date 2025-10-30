<?php

namespace App\Jobs;

use App\Enum\MessageStatusEnum;
use App\Services\Tenant\MessageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateFacebookMessageStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $messageId,
        public string $status
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(MessageService $messageService): void
    {
        try {
            // Find message by platform_message_id
            $message = $messageService->getByPlatformMessageId($this->messageId);

            if (!$message) {
                Log::warning('Message not found for Facebook status update', ['platform_message_id' => $this->messageId]);
                return;
            }

            // Update status
            $internalStatus = match ($this->status) {
                'delivered' => MessageStatusEnum::DELIVERED->value,
                'read' => MessageStatusEnum::READ->value,
                default => null,
            };

            if ($internalStatus) {
                $messageService->updateStatus(
                    messageId: $message->id,
                    status: $internalStatus
                );

                Log::info('Facebook message status updated', [
                    'message_id' => $message->id,
                    'status' => $this->status,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating Facebook message status', [
                'error' => $e->getMessage(),
                'message_id' => $this->messageId,
            ]);
            throw $e;
        }
    }
}

