<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SallaWebhookController extends Controller
{
    /**
     * Handle Salla webhooks.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $event = $payload['event'] ?? null;
        $merchant = $payload['merchant'] ?? null;

        Log::info('Salla webhook received', [
            'event' => $event,
            'merchant' => $merchant,
        ]);

        // Verify webhook signature
        if (!$this->verifySignature($request)) {
            Log::error('Salla webhook verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            // Route to appropriate handler
            match ($event) {
                'order.created' => $this->handleOrderCreated($payload),
                'order.updated' => $this->handleOrderUpdated($payload),
                'order.cancelled' => $this->handleOrderCancelled($payload),
                'customer.created' => $this->handleCustomerCreated($payload),
                'customer.updated' => $this->handleCustomerUpdated($payload),
                'product.created' => $this->handleProductCreated($payload),
                'product.updated' => $this->handleProductUpdated($payload),
                default => Log::info("Unhandled Salla event: {$event}"),
            };

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Salla webhook', [
                'error' => $e->getMessage(),
                'event' => $event,
            ]);

            return response()->json(['status' => 'error'], 200);
        }
    }

    /**
     * Verify Salla webhook signature.
     */
    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-Salla-Signature');
        
        if (!$signature) {
            return false;
        }

        $secret = config('services.salla.webhook_secret');
        $payload = $request->getContent();
        $expectedSignature = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    protected function handleOrderCreated(array $payload): void
    {
        $order = $payload['data'] ?? [];
        dispatch(new \App\Jobs\SyncSallaOrderJob($order, 'created'));
    }

    protected function handleOrderUpdated(array $payload): void
    {
        $order = $payload['data'] ?? [];
        dispatch(new \App\Jobs\SyncSallaOrderJob($order, 'updated'));
    }

    protected function handleOrderCancelled(array $payload): void
    {
        $order = $payload['data'] ?? [];
        dispatch(new \App\Jobs\SyncSallaOrderJob($order, 'cancelled'));
    }

    protected function handleCustomerCreated(array $payload): void
    {
        $customer = $payload['data'] ?? [];
        dispatch(new \App\Jobs\SyncSallaCustomerJob($customer, 'created'));
    }

    protected function handleCustomerUpdated(array $payload): void
    {
        $customer = $payload['data'] ?? [];
        dispatch(new \App\Jobs\SyncSallaCustomerJob($customer, 'updated'));
    }

    protected function handleProductCreated(array $payload): void
    {
        $product = $payload['data'] ?? [];
        dispatch(new \App\Jobs\SyncSallaProductJob($product, 'created'));
    }

    protected function handleProductUpdated(array $payload): void
    {
        $product = $payload['data'] ?? [];
        dispatch(new \App\Jobs\SyncSallaProductJob($product, 'updated'));
    }
}
