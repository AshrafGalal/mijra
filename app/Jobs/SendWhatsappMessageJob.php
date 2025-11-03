<?php

namespace App\Jobs;

use App\Enum\MessageStatusEnum;
use App\Models\Landlord\Tenant;
use App\Models\Tenant\Conversation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class SendWhatsappMessageJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $message, public Conversation $conversation, public Tenant $tenant)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {

        $accountId = $this->conversation->platform_account_id;
        $tenant = $this->tenant->id;
        $payload = [
            'tenant_id' => $tenant,
            'account_id' => $accountId,
            'to' => $this->conversation->contact_identifier_id,
            'body' => $this->message->body,
            'message_id' => $this->message->id,
        ];

        if (isset($this->message->media)) {
            $payload['media_path'] = $this->message->media[0]->getPath();
        }
        $replyToMessageId = $this->message->reply_to_external_message_id;
        if ($replyToMessageId) {
            $payload['reply_to_message_id'] = $replyToMessageId;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.whatsapp.api_secret_token'),
            ])->post(config('services.whatsapp.node_service_url') . '/messages/send', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $messageData = Arr::first(Arr::get($data, 'messageData.messages', []));
                $this->message->update([
                    'external_message_id' => $messageData['external_message_id'] ?? null,
                    'isForwarded' => $messageData['isForwarded'] ?? false,
                    'sender' => $messageData['sender'],
                    'receiver' => $messageData['receiver'],
                    'reply_to_external_message_id' => $messageData['reply_to_message_id'],
                    'status' => Arr::get($data, 'status', MessageStatusEnum::RECEIVED->value),
                    'sent_at' => Arr::get($data, 'timestamp'),
                ]);
            } else {
                // 🧠 Log everything for debugging
                logger()->error('❌ WhatsApp message send failed', [
                    'url' => config('services.whatsapp.node_service_url') . '/messages/send',
                    'status' => $response->status(),
                    'error' => $response->body(),
                    'payload' => $payload,
                ]);
                $this->fail($response->body());
            }
        } catch (\Throwable $e) {
            $this->fail($e);
        }
    }
}
