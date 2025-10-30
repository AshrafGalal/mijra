<?php

namespace App\Http\Controllers\Api\Webhooks;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ShopifyWebhookController extends Controller
{
    /**
     * Handle incoming Shopify webhook.
     */
    public function handle(Request $request)
    {
        $payload = $request->all();
        $topic = $request->header('X-Shopify-Topic');
        $shopDomain = $request->header('X-Shopify-Shop-Domain');

        Log::info('Shopify webhook received', [
            'topic' => $topic,
            'shop' => $shopDomain,
        ]);

        // Verify webhook signature
        if (!$this->verifyWebhook($request)) {
            Log::error('Shopify webhook verification failed');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        try {
            // Route to appropriate handler based on topic
            match ($topic) {
                'orders/create' => $this->handleOrderCreate($payload, $shopDomain),
                'orders/updated' => $this->handleOrderUpdate($payload, $shopDomain),
                'orders/cancelled' => $this->handleOrderCancelled($payload, $shopDomain),
                'orders/fulfilled' => $this->handleOrderFulfilled($payload, $shopDomain),
                'customers/create' => $this->handleCustomerCreate($payload, $shopDomain),
                'customers/update' => $this->handleCustomerUpdate($payload, $shopDomain),
                'products/create' => $this->handleProductCreate($payload, $shopDomain),
                'products/update' => $this->handleProductUpdate($payload, $shopDomain),
                'carts/create' => $this->handleCartCreate($payload, $shopDomain),
                'carts/update' => $this->handleCartUpdate($payload, $shopDomain),
                default => Log::info("Unhandled Shopify webhook topic: {$topic}"),
            };

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('Error processing Shopify webhook', [
                'error' => $e->getMessage(),
                'topic' => $topic,
            ]);

            return response()->json(['status' => 'error'], 200);
        }
    }

    /**
     * Verify Shopify webhook signature.
     */
    protected function verifyWebhook(Request $request): bool
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        
        if (!$hmacHeader) {
            return false;
        }

        $data = $request->getContent();
        $secret = config('services.shopify.webhook_secret');
        
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));
        
        return hash_equals($calculatedHmac, $hmacHeader);
    }

    /**
     * Handle new order.
     */
    protected function handleOrderCreate(array $order, string $shopDomain): void
    {
        dispatch(new \App\Jobs\SyncShopifyOrderJob($order, $shopDomain, 'created'));
    }

    /**
     * Handle order update.
     */
    protected function handleOrderUpdate(array $order, string $shopDomain): void
    {
        dispatch(new \App\Jobs\SyncShopifyOrderJob($order, $shopDomain, 'updated'));
    }

    /**
     * Handle order cancellation.
     */
    protected function handleOrderCancelled(array $order, string $shopDomain): void
    {
        dispatch(new \App\Jobs\SyncShopifyOrderJob($order, $shopDomain, 'cancelled'));
    }

    /**
     * Handle order fulfillment.
     */
    protected function handleOrderFulfilled(array $order, string $shopDomain): void
    {
        dispatch(new \App\Jobs\SyncShopifyOrderJob($order, $shopDomain, 'fulfilled'));
    }

    /**
     * Handle new customer.
     */
    protected function handleCustomerCreate(array $customer, string $shopDomain): void
    {
        dispatch(new \App\Jobs\SyncShopifyCustomerJob($customer, $shopDomain, 'created'));
    }

    /**
     * Handle customer update.
     */
    protected function handleCustomerUpdate(array $customer, string $shopDomain): void
    {
        dispatch(new \App\Jobs\SyncShopifyCustomerJob($customer, $shopDomain, 'updated'));
    }

    /**
     * Handle new product.
     */
    protected function handleProductCreate(array $product, string $shopDomain): void
    {
        dispatch(new \App\Jobs\SyncShopifyProductJob($product, $shopDomain, 'created'));
    }

    /**
     * Handle product update.
     */
    protected function handleProductUpdate(array $product, string $shopDomain): void
    {
        dispatch(new \App\Jobs\SyncShopifyProductJob($product, $shopDomain, 'updated'));
    }

    /**
     * Handle cart creation (abandoned cart detection).
     */
    protected function handleCartCreate(array $cart, string $shopDomain): void
    {
        dispatch(new \App\Jobs\DetectAbandonedCartJob($cart, $shopDomain));
    }

    /**
     * Handle cart update.
     */
    protected function handleCartUpdate(array $cart, string $shopDomain): void
    {
        dispatch(new \App\Jobs\DetectAbandonedCartJob($cart, $shopDomain));
    }
}

