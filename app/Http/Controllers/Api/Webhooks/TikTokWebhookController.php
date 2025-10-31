<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TikTokWebhookController extends Controller
{
    /**
     * Verify webhook (GET request from TikTok).
     */
    public function verify(Request $request)
    {
        $verifyToken = config('services.tiktok.verify_token');
        $challenge = $request->input('hub.challenge');
        $mode = $request->input('hub.mode');
        $token = $request->input('hub.verify_token');

        if ($mode === 'subscribe' && $token === $verifyToken) {
            Log::info('TikTok webhook verified successfully');
            return response($challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response()->json(['error' => 'Forbidden'], 403);
    }

    /**
     * Handle incoming webhook events.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('TikTok webhook received', ['payload' => $payload]);

        // Verify signature
        if (!$this->verifySignature($request)) {
            Log::error('TikTok webhook signature verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $events = $payload['events'] ?? [];

            foreach ($events as $event) {
                $this->processEvent($event);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing TikTok webhook', [
                'error' => $e->getMessage(),
                'payload' => $payload,
            ]);

            return response()->json(['status' => 'error'], 200);
        }
    }

    /**
     * Verify TikTok webhook signature.
     */
    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-TikTok-Signature');
        
        if (!$signature) {
            return false;
        }

        $secret = config('services.tiktok.app_secret');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Process TikTok event.
     */
    protected function processEvent(array $event): void
    {
        $eventType = $event['event_type'] ?? null;

        match ($eventType) {
            'message.received' => $this->handleMessage($event),
            'message.read' => $this->handleReadReceipt($event),
            default => Log::info("Unhandled TikTok event: {$eventType}"),
        };
    }

    /**
     * Handle incoming message.
     */
    protected function handleMessage(array $event): void
    {
        $message = $event['message'] ?? [];
        
        Log::info('TikTok message received', [
            'message_id' => $message['message_id'] ?? null,
            'sender_id' => $message['sender_id'] ?? null,
        ]);

        dispatch(new \App\Jobs\ProcessTikTokMessageJob($event));
    }

    /**
     * Handle read receipt.
     */
    protected function handleReadReceipt(array $event): void
    {
        Log::info('TikTok read receipt', ['event' => $event]);
        // Process read receipt
    }
}


