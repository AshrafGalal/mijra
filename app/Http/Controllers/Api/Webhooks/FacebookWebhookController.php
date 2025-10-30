<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FacebookWebhookController extends Controller
{
    /**
     * Verify webhook (GET request from Meta).
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.facebook.verify_token', config('services.whatsapp.verify_token'));
        $mode = $request->input('hub_mode');
        $token = $request->input('hub_verify_token');
        $challenge = $request->input('hub_challenge');

        // Check if mode and token are correct
        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('Facebook Messenger webhook verified successfully');
            
            // Respond with the challenge token
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Facebook Messenger webhook verification failed', [
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

        Log::info('Facebook Messenger webhook received', ['payload' => $payload]);

        // Verify webhook signature
        if (!$this->verifySignature($request)) {
            Log::error('Facebook Messenger webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Process the webhook payload
        try {
            // Meta sends webhooks in this structure:
            // { "object": "page", "entry": [...] }
            
            if ($request->input('object') === 'page') {
                $entries = $request->input('entry', []);
                
                foreach ($entries as $entry) {
                    $messaging = $entry['messaging'] ?? [];
                    
                    foreach ($messaging as $event) {
                        $this->processEvent($event);
                    }
                }
            }

            // Meta requires 200 OK response
            return response()->json(['status' => 'ok'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error processing Facebook Messenger webhook', [
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
            // Fallback to old signature format
            $signature = $request->header('X-Hub-Signature');
            if (!$signature) {
                return false;
            }
            $signature = str_replace('sha1=', '', $signature);
            $appSecret = config('services.facebook.client_secret');
            $payload = $request->getContent();
            $expectedSignature = hash_hmac('sha1', $payload, $appSecret);
            return hash_equals($expectedSignature, $signature);
        }

        $appSecret = config('services.facebook.client_secret');
        $payload = $request->getContent();
        
        // Remove 'sha256=' prefix
        $signature = str_replace('sha256=', '', $signature);
        
        // Calculate expected signature
        $expectedSignature = hash_hmac('sha256', $payload, $appSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process a single messaging event.
     */
    protected function processEvent(array $event): void
    {
        // Handle different event types
        if (isset($event['message'])) {
            $this->handleMessage($event);
        } elseif (isset($event['postback'])) {
            $this->handlePostback($event);
        } elseif (isset($event['delivery'])) {
            $this->handleDelivery($event);
        } elseif (isset($event['read'])) {
            $this->handleRead($event);
        }
    }

    /**
     * Handle incoming message.
     */
    protected function handleMessage(array $event): void
    {
        $sender = $event['sender']['id'] ?? null;
        $message = $event['message'] ?? [];

        Log::info('Facebook Messenger message received', [
            'sender_id' => $sender,
            'message_id' => $message['mid'] ?? null,
        ]);

        // Dispatch job to process message
        dispatch(new \App\Jobs\ProcessFacebookMessageJob($event));
    }

    /**
     * Handle postback (button click).
     */
    protected function handlePostback(array $event): void
    {
        $sender = $event['sender']['id'] ?? null;
        $postback = $event['postback'] ?? [];

        Log::info('Facebook Messenger postback received', [
            'sender_id' => $sender,
            'payload' => $postback['payload'] ?? null,
        ]);

        // Dispatch job to process postback as a message
        dispatch(new \App\Jobs\ProcessFacebookPostbackJob($event));
    }

    /**
     * Handle delivery receipt.
     */
    protected function handleDelivery(array $event): void
    {
        $delivery = $event['delivery'] ?? [];
        $mids = $delivery['mids'] ?? [];

        foreach ($mids as $mid) {
            dispatch(new \App\Jobs\UpdateFacebookMessageStatusJob($mid, 'delivered'));
        }
    }

    /**
     * Handle read receipt.
     */
    protected function handleRead(array $event): void
    {
        $read = $event['read'] ?? [];
        $watermark = $read['watermark'] ?? null;

        // All messages before watermark timestamp are read
        if ($watermark) {
            dispatch(new \App\Jobs\UpdateFacebookMessagesReadJob($watermark));
        }
    }
}

