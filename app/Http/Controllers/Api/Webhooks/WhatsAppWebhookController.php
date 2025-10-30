<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Verify webhook (GET request from Meta).
     * Meta will send a challenge token that we need to return.
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.whatsapp.verify_token');
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        // Check if mode and token are correct
        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('WhatsApp webhook verified successfully');
            
            // Respond with the challenge token
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('WhatsApp webhook verification failed', [
            'mode' => $mode,
            'token_match' => $token === $verifyToken,
        ]);

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Handle incoming webhook events (POST request from Meta).
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('WhatsApp webhook received', ['payload' => $payload]);

        // Verify webhook signature
        if (!$this->verifySignature($request)) {
            Log::error('WhatsApp webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Process the webhook payload
        try {
            // Meta sends webhooks in this structure:
            // { "object": "whatsapp_business_account", "entry": [...] }
            
            if ($request->input('object') === 'whatsapp_business_account') {
                $entries = $request->input('entry', []);
                
                foreach ($entries as $entry) {
                    $changes = $entry['changes'] ?? [];
                    
                    foreach ($changes as $change) {
                        $this->processChange($change);
                    }
                }
            }

            // Meta requires 200 OK response within 20 seconds
            return response()->json(['status' => 'ok'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error processing WhatsApp webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            // Still return 200 to prevent Meta from retrying
            return response()->json(['status' => 'error'], 200);
        }
    }

    /**
     * Verify the webhook signature from Meta.
     */
    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Hub-Signature-256');
        
        if (!$signature) {
            return false;
        }

        $appSecret = config('services.whatsapp.app_secret');
        $payload = $request->getContent();
        
        // Remove 'sha256=' prefix
        $signature = str_replace('sha256=', '', $signature);
        
        // Calculate expected signature
        $expectedSignature = hash_hmac('sha256', $payload, $appSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process a single webhook change.
     */
    protected function processChange(array $change): void
    {
        $value = $change['value'] ?? [];
        $field = $change['field'] ?? null;

        if ($field === 'messages') {
            // Handle incoming messages
            $messages = $value['messages'] ?? [];
            foreach ($messages as $message) {
                $this->handleIncomingMessage($message, $value);
            }

            // Handle message status updates
            $statuses = $value['statuses'] ?? [];
            foreach ($statuses as $status) {
                $this->handleStatusUpdate($status, $value);
            }
        }
    }

    /**
     * Handle incoming WhatsApp message.
     */
    protected function handleIncomingMessage(array $message, array $metadata): void
    {
        Log::info('WhatsApp message received', [
            'message_id' => $message['id'] ?? null,
            'from' => $message['from'] ?? null,
            'type' => $message['type'] ?? null,
        ]);

        // Dispatch job to process message asynchronously
        dispatch(new \App\Jobs\ProcessWhatsAppMessageJob($message, $metadata));
    }

    /**
     * Handle WhatsApp message status update.
     */
    protected function handleStatusUpdate(array $status, array $metadata): void
    {
        Log::info('WhatsApp status update', [
            'message_id' => $status['id'] ?? null,
            'status' => $status['status'] ?? null,
        ]);

        // Dispatch job to update message status
        dispatch(new \App\Jobs\UpdateWhatsAppMessageStatusJob($status));
    }
}

