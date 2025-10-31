<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PymobWebhookController extends Controller
{
    /**
     * Handle Pymob payment webhooks.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Pymob webhook received', ['payload' => $payload]);

        // Verify signature
        if (!$this->verifySignature($request)) {
            Log::error('Pymob webhook verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $event = $payload['event'] ?? null;

            match ($event) {
                'payment.success' => $this->handlePaymentSuccess($payload),
                'payment.failed' => $this->handlePaymentFailed($payload),
                'payment.pending' => $this->handlePaymentPending($payload),
                'payment.refunded' => $this->handlePaymentRefunded($payload),
                default => Log::info("Unhandled Pymob event: {$event}"),
            };

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Pymob webhook', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 200);
        }
    }

    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Pymob-Signature');
        
        if (!$signature) {
            return false;
        }

        $secret = config('services.pymob.secret_key');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    protected function handlePaymentSuccess(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessPymobPaymentJob($payload, 'success'));
    }

    protected function handlePaymentFailed(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessPymobPaymentJob($payload, 'failed'));
    }

    protected function handlePaymentPending(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessPymobPaymentJob($payload, 'pending'));
    }

    protected function handlePaymentRefunded(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessPymobPaymentJob($payload, 'refunded'));
    }
}

