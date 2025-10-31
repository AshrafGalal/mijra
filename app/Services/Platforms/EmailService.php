<?php

namespace App\Services\Platforms;

use App\Models\Tenant\Conversation;
use App\Models\Tenant\Message;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class EmailService
{
    /**
     * Send email message.
     */
    public function sendEmail(Conversation $conversation, Message $message): array
    {
        try {
            $customer = $conversation->customer;
            $emailAddress = $this->extractEmailAddress($conversation);

            if (!$emailAddress) {
                throw new \Exception('No email address found for customer');
            }

            // Send email
            Mail::send([], [], function ($mail) use ($emailAddress, $message, $customer) {
                $mail->to($emailAddress, $customer->name)
                    ->subject('Message from ' . config('app.name'))
                    ->html($message->content);

                // Add attachments if any
                foreach ($message->attachments as $attachment) {
                    if ($attachment->url) {
                        $mail->attach($attachment->url, [
                            'as' => $attachment->filename,
                            'mime' => $attachment->mime_type,
                        ]);
                    }
                }
            });

            $message->markAsSent();

            Log::info('Email sent successfully', [
                'message_id' => $message->id,
                'to' => $emailAddress,
            ]);

            return [
                'success' => true,
                'to' => $emailAddress,
            ];

        } catch (\Exception $e) {
            $message->markAsFailed($e->getMessage());

            Log::error('Error sending email', [
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
     * Extract email address from conversation.
     */
    protected function extractEmailAddress(Conversation $conversation): ?string
    {
        // Try platform_conversation_id first (for email platform)
        if ($conversation->platform === 'email' && $conversation->platform_conversation_id) {
            return $conversation->platform_conversation_id;
        }

        // Fallback to customer email
        return $conversation->customer->email;
    }

    /**
     * Parse incoming email and create conversation.
     */
    public function processIncomingEmail(array $emailData): void
    {
        // This would be called from an email webhook (e.g., SendGrid, Mailgun)
        // Implementation depends on email provider
        Log::info('Incoming email received', ['email' => $emailData]);
        
        // TODO: Implement based on email provider
        // dispatch(new ProcessIncomingEmailJob($emailData));
    }
}

