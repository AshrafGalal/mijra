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

class UpdateWhatsAppMessageStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public array $status
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(MessageService $messageService): void
    {
        try {
            $messageId = $this->status['id'] ?? null;
            $statusValue = $this->status['status'] ?? null;
            $timestamp = $this->status['timestamp'] ?? null;

            if (!$messageId || !$statusValue) {
                Log::warning('WhatsApp status update missing required fields', ['status' => $this->status]);
                return;
            }

            // Find message by platform_message_id
            $message = $messageService->getByPlatformMessageId($messageId);

            if (!$message) {
                Log::warning('Message not found for WhatsApp status update', ['platform_message_id' => $messageId]);
                return;
            }

            // Map WhatsApp status to internal status
            $internalStatus = $this->mapWhatsAppStatus($statusValue);

            if ($internalStatus) {
                $messageService->updateStatus(
                    messageId: $message->id,
                    status: $internalStatus
                );

                Log::info('WhatsApp message status updated', [
                    'message_id' => $message->id,
                    'platform_message_id' => $messageId,
                    'status' => $statusValue,
                ]);
            }

        } catch (\Exception $e) {
            Log::error('Error updating WhatsApp message status', [
                'error' => $e->getMessage(),
                'status' => $this->status,
            ]);
            throw $e;
        }
    }

    /**
     * Map WhatsApp status to internal MessageStatusEnum.
     */
    protected function mapWhatsAppStatus(string $whatsappStatus): ?string
    {
        return match ($whatsappStatus) {
            'sent' => MessageStatusEnum::SENT->value,
            'delivered' => MessageStatusEnum::DELIVERED->value,
            'read' => MessageStatusEnum::READ->value,
            'failed' => MessageStatusEnum::FAILED->value,
            default => null,
        };
    }
}

