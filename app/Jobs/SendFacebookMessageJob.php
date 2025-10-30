<?php

namespace App\Jobs;

use App\Enum\MessageTypeEnum;
use App\Models\Tenant\Message;
use App\Services\Platforms\FacebookMessengerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendFacebookMessageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [10, 30, 60];

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
    public function handle(FacebookMessengerService $facebookService): void
    {
        try {
            $conversation = $this->message->conversation;
            $metadata = $this->message->metadata ?? [];

            // Send typing indicator
            $recipientId = $conversation->platform_conversation_id;
            if ($recipientId) {
                $facebookService->sendTypingOn($recipientId);
            }

            // Determine send method based on message type
            $result = match ($this->message->type) {
                MessageTypeEnum::TEXT->value => $facebookService->sendTextMessage($conversation, $this->message),
                MessageTypeEnum::IMAGE->value => $this->sendImageMessage($facebookService, $conversation),
                MessageTypeEnum::VIDEO->value => $this->sendVideoMessage($facebookService, $conversation),
                MessageTypeEnum::AUDIO->value, MessageTypeEnum::VOICE->value => $this->sendAudioMessage($facebookService, $conversation),
                MessageTypeEnum::DOCUMENT->value => $this->sendDocumentMessage($facebookService, $conversation),
                default => $facebookService->sendTextMessage($conversation, $this->message),
            };

            if (!$result['success']) {
                throw new \Exception($result['message'] ?? 'Failed to send Facebook message');
            }

            Log::info('Facebook message sent via job', [
                'message_id' => $this->message->id,
                'platform_message_id' => $result['platform_message_id'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in SendFacebookMessageJob', [
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
    protected function sendImageMessage(FacebookMessengerService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendImageMessage(
            $conversation,
            $this->message,
            $attachment->url
        );
    }

    /**
     * Send video with attachment.
     */
    protected function sendVideoMessage(FacebookMessengerService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendVideoMessage(
            $conversation,
            $this->message,
            $attachment->url
        );
    }

    /**
     * Send audio with attachment.
     */
    protected function sendAudioMessage(FacebookMessengerService $service, $conversation): array
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
    protected function sendDocumentMessage(FacebookMessengerService $service, $conversation): array
    {
        $attachment = $this->message->attachments->first();
        
        if (!$attachment) {
            return $service->sendTextMessage($conversation, $this->message);
        }

        return $service->sendFileMessage(
            $conversation,
            $this->message,
            $attachment->url
        );
    }
}

