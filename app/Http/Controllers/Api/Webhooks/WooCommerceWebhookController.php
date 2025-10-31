<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WooCommerceWebhookController extends Controller
{
    /**
     * Handle WooCommerce webhooks.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $event = $request->header('X-WC-Webhook-Event');
        $topic = $request->header('X-WC-Webhook-Topic');
        $source = $request->header('X-WC-Webhook-Source');

        Log::info('WooCommerce webhook received', [
            'event' => $event,
            'topic' => $topic,
            'source' => $source,
        ]);

        // Verify signature
        if (!$this->verifySignature($request)) {
            Log::error('WooCommerce webhook verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            // Route based on topic
            match ($topic) {
                'order.created' => dispatch(new \App\Jobs\SyncWooCommerceOrderJob($payload, $source, 'created')),
                'order.updated' => dispatch(new \App\Jobs\SyncWooCommerceOrderJob($payload, $source, 'updated')),
                'customer.created' => dispatch(new \App\Jobs\SyncWooCommerceCustomerJob($payload, $source, 'created')),
                'customer.updated' => dispatch(new \App\Jobs\SyncWooCommerceCustomerJob($payload, $source, 'updated')),
                'product.created' => dispatch(new \App\Jobs\SyncWooCommerceProductJob($payload, $source, 'created')),
                'product.updated' => dispatch(new \App\Jobs\SyncWooCommerceProductJob($payload, $source, 'updated')),
                default => Log::info("Unhandled WooCommerce topic: {$topic}"),
            };

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing WooCommerce webhook', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 200);
        }
    }

    /**
     * Verify WooCommerce signature.
     */
    protected function verifySignature(Request $request): bool
    {
        $signature = $request->header('X-WC-Webhook-Signature');
        
        if (!$signature) {
            return false;
        }

        $source = $request->header('X-WC-Webhook-Source');
        $secret = config('services.woocommerce.webhook_secret');
        $payload = $request->getContent();
        
        $expectedSignature = base64_encode(hash_hmac('sha256', $payload, $secret, true));

        return hash_equals($expectedSignature, $signature);
    }
}

