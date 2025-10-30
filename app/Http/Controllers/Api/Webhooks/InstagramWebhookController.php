<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InstagramWebhookController extends Controller
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
            Log::info('Instagram webhook verified successfully');
            
            // Respond with the challenge token
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        Log::warning('Instagram webhook verification failed');

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Handle incoming webhook events (POST request from Meta).
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Instagram webhook received', ['payload' => $payload]);

        // Verify webhook signature
        if (!$this->verifySignature($request)) {
            Log::error('Instagram webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // Process the webhook payload
        try {
            // Instagram uses similar structure to Messenger
            if ($request->input('object') === 'instagram') {
                $entries = $request->input('entry', []);
                
                foreach ($entries as $entry) {
                    $messaging = $entry['messaging'] ?? [];
                    
                    foreach ($messaging as $event) {
                        $this->processEvent($event);
                    }
                }
            }

            return response()->json(['status' => 'ok'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error processing Instagram webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

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

        $appSecret = config('services.facebook.client_secret');
        $payload = $request->getContent();
        
        $signature = str_replace('sha256=', '', $signature);
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
        } elseif (isset($event['reaction'])) {
            $this->handleReaction($event);
        }
    }

    /**
     * Handle incoming message.
     */
    protected function handleMessage(array $event): void
    {
        $sender = $event['sender']['id'] ?? null;
        $message = $event['message'] ?? [];

        Log::info('Instagram message received', [
            'sender_id' => $sender,
            'message_id' => $message['mid'] ?? null,
        ]);

        // Dispatch job to process message
        dispatch(new \App\Jobs\ProcessInstagramMessageJob($event));
    }

    /**
     * Handle postback (button click).
     */
    protected function handlePostback(array $event): void
    {
        $sender = $event['sender']['id'] ?? null;
        $postback = $event['postback'] ?? [];

        Log::info('Instagram postback received', [
            'sender_id' => $sender,
            'payload' => $postback['payload'] ?? null,
        ]);

        dispatch(new \App\Jobs\ProcessInstagramPostbackJob($event));
    }

    /**
     * Handle reaction (story reply or message reaction).
     */
    protected function handleReaction(array $event): void
    {
        $sender = $event['sender']['id'] ?? null;
        $reaction = $event['reaction'] ?? [];

        Log::info('Instagram reaction received', [
            'sender_id' => $sender,
            'reaction' => $reaction,
        ]);

        // Process reaction as a message
        dispatch(new \App\Jobs\ProcessInstagramReactionJob($event));
    }
}

