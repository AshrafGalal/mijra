<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MoyasarWebhookController extends Controller
{
    /**
     * Handle Moyasar payment webhooks.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();

        Log::info('Moyasar webhook received', ['payload' => $payload]);

        // Verify signature
        if (!$this->verifySignature($request)) {
            Log::error('Moyasar webhook verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            $type = $payload['type'] ?? null;

            match ($type) {
                'payment_paid' => $this->handlePaymentPaid($payload),
                'payment_failed' => $this->handlePaymentFailed($payload),
                'payment_authorized' => $this->handlePaymentAuthorized($payload),
                'payment_captured' => $this->handlePaymentCaptured($payload),
                'payment_refunded' => $this->handlePaymentRefunded($payload),
                default => Log::info("Unhandled Moyasar event: {$type}"),
            };

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Moyasar webhook', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 200);
        }
    }

    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Moyasar-Signature');
        
        if (!$signature) {
            return false;
        }

        $secret = config('services.moyasar.secret_key');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    protected function handlePaymentPaid(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessMoyasarPaymentJob($payload, 'paid'));
    }

    protected function handlePaymentFailed(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessMoyasarPaymentJob($payload, 'failed'));
    }

    protected function handlePaymentAuthorized(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessMoyasarPaymentJob($payload, 'authorized'));
    }

    protected function handlePaymentCaptured(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessMoyasarPaymentJob($payload, 'captured'));
    }

    protected function handlePaymentRefunded(array $payload): void
    {
        dispatch(new \App\Jobs\ProcessMoyasarPaymentJob($payload, 'refunded'));
    }
}

