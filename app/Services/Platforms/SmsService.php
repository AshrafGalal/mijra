<?php

namespace App\Services\Platforms;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected string $twilioSid;
    protected string $twilioToken;
    protected string $twilioFrom;

    public function __construct()
    {
        $this->twilioSid = config('services.twilio.sid');
        $this->twilioToken = config('services.twilio.token');
        $this->twilioFrom = config('services.twilio.from');
    }

    /**
     * Send SMS message via Twilio.
     */
    public function sendSms(Conversation $conversation, Message $message): array
    {
        try {
            $to = $this->extractPhoneNumber($conversation);

            if (!$to) {
                throw new \Exception('No phone number found for customer');
            }

            // Send via Twilio API
            $response = Http::withBasicAuth($this->twilioSid, $this->twilioToken)
                ->asForm()
                ->post("https://api.twilio.com/2010-04-01/Accounts/{$this->twilioSid}/Messages.json", [
                    'From' => $this->twilioFrom,
                    'To' => $to,
                    'Body' => $message->content,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $platformMessageId = $data['sid'] ?? null;

                $message->markAsSent($platformMessageId);

                Log::info('SMS sent successfully', [
                    'message_id' => $message->id,
                    'platform_message_id' => $platformMessageId,
                    'to' => $to,
                ]);

                return [
                    'success' => true,
                    'platform_message_id' => $platformMessageId,
                    'data' => $data,
                ];
            }

            $error = $response->json();
            $message->markAsFailed($error['message'] ?? 'Unknown error');

            return [
                'success' => false,
                'error' => $error,
            ];

        } catch (\Exception $e) {
            $message->markAsFailed($e->getMessage());

            Log::error('Error sending SMS', [
                'message_id' => $message->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Extract phone number from conversation.
     */
    protected function extractPhoneNumber(Conversation $conversation): ?string
    {
        if ($conversation->platform_conversation_id) {
            return $conversation->platform_conversation_id;
        }

        return $conversation->customer->phone;
    }

    /**
     * Process incoming SMS webhook (Twilio).
     */
    public function processIncomingSms(array $smsData): void
    {
        Log::info('Incoming SMS received', ['sms' => $smsData]);
        
        // Dispatch job to process
        dispatch(new \App\Jobs\ProcessIncomingSmsJob($smsData));
    }
}

