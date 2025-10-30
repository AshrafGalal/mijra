<?php

namespace App\Jobs;

use App\Enum\MessageTypeEnum;
use App\Models\Tenant\Message;
use App\Services\Platforms\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60]; // Retry after 10s, 30s, 60s

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Message $message
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(WhatsAppService $whatsappService): void
    {
        try {
            $conversation = $this->message->conversation;
            $metadata = $this->message->metadata ?? [];

            // Determine send method based on message type
            $result = match ($this->message->type) {
                MessageTypeEnum::TEXT->value => $whatsappService->sendTextMessage($conversation, $this->message),
                MessageTypeEnum::IMAGE->value => $this->sendImageMessage($whatsappService, $conversation),
                MessageTypeEnum::VIDEO->value => $this->sendVideoMessage($whatsappService, $conversation),
                MessageTypeEnum::AUDIO->value, MessageTypeEnum::VOICE->value => $this->sendAudioMessage($whatsappService, $conversation),
                MessageTypeEnum::DOCUMENT->value => $this->sendDocumentMessage($whatsappService, $conversation),
                MessageTypeEnum::TEMPLATE->value => $this->sendTemplateMessage($whatsappService, $conversation, $metadata),
                default => $whatsappService->sendTextMessage($conversation, $this->message),
            };

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Failed to send WhatsApp message');
            }

            Log::info('WhatsApp message sent via job', [
                'message_id' => $this->message->id,
                'platform_message_id' => $result['platform_message_id'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in SendWhatsAppMessageJob', [
                'message_id' => $this->message->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            // Mark as failed if max retries exceeded
            if ($this->attempts() >= $this->tries) {
                $this->message->markAsFailed($e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Send image with attachment.
     */
    protected function sendImageMessage(WhatsAppService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendImageMessage(
            $conversation,
            $this->message,
            $attachment->url,
            $this->message->content
        );
    }

    /**
     * Send video with attachment.
     */
    protected function sendVideoMessage(WhatsAppService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendVideoMessage(
            $conversation,
            $this->message,
            $attachment->url,
            $this->message->content
        );
    }

    /**
     * Send audio with attachment.
     */
    protected function sendAudioMessage(WhatsAppService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendAudioMessage(
            $conversation,
            $this->message,
            $attachment->url
        );
    }

    /**
     * Send document with attachment.
     */
    protected function sendDocumentMessage(WhatsAppService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendDocumentMessage(
            $conversation,
            $this->message,
            $attachment->url,
            $attachment->filename,
            $this->message->content
        );
    }

    /**
     * Send template message.
     */
    protected function sendTemplateMessage(WhatsAppService $service, $conversation, array $metadata): array
    {
        $templateName = $metadata['template_name'] ?? null;
        $parameters = $metadata['parameters'] ?? [];
        $languageCode = $metadata['language_code'] ?? 'en';

        if (!$templateName) {
            throw new \Exception('Template name is required for template messages');
        }

        return $service->sendTemplateMessage(
            $conversation,
            $this->message,
            $templateName,
            $parameters,
            $languageCode
        );
    }
}

