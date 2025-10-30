<?php

namespace App\Jobs;

use App\Models\Tenant\Product;
use App\Models\Tenant\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncShopifyProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $productData,
        public string $shopDomain,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        try {
            $shopifyProductId = $this->productData['id'] ?? null;
            $title = $this->productData['title'] ?? null;

            if (!$shopifyProductId || !$title) {
                return;
            }

            // Find or create product
            $product = Product::where('sku', "shopify_{$shopifyProductId}")->first();

            $productAttributes = [
                'name' => $title,
                'description' => strip_tags($this->productData['body_html'] ?? ''),
                'sku' => "shopify_{$shopifyProductId}",
                'price' => $this->productData['variants'][0]['price'] ?? 0,
                'quantity' => array_sum(array_column($this->productData['variants'] ?? [], 'inventory_quantity')),
                'status' => $this->productData['status'] === 'active' ? \App\Enum\ProductStatusEnum::ACTIVE->value : \App\Enum\ProductStatusEnum::INACTIVE->value,
                'metadata' => [
                    'shopify_product_id' => $shopifyProductId,
                    'vendor' => $this->productData['vendor'] ?? null,
                    'product_type' => $this->productData['product_type'] ?? null,
                    'tags' => $this->productData['tags'] ?? null,
                ],
            ];

            if ($product) {
                $product->update($productAttributes);
                Log::info('Shopify product updated', ['product_id' => $product->id]);
            } else {
                $product = Product::create($productAttributes);
                Log::info('New product created from Shopify', ['product_id' => $product->id]);
            }

            // Sync variants
            $this->syncVariants($product, $this->productData['variants'] ?? []);

        } catch (\Exception $e) {
            Log::error('Error syncing Shopify product', [
                'error' => $e->getMessage(),
                'product_id' => $this->productData['id'] ?? null,
            ]);
            throw $e;
        }
    }

    /**
     * Sync product variants.
     */
    protected function syncVariants(Product $product, array $variants): void
    {
        foreach ($variants as $variantData) {
            $shopifyVariantId = $variantData['id'] ?? null;
            
            if (!$shopifyVariantId) {
                continue;
            }

            $variant = ProductVariant::where('sku', "shopify_variant_{$shopifyVariantId}")->first();

            $variantAttributes = [
                'product_id' => $product->id,
                'sku' => "shopify_variant_{$shopifyVariantId}",
                'price' => $variantData['price'] ?? 0,
                'cost' => $variantData['compare_at_price'] ?? null,
                'quantity' => $variantData['inventory_quantity'] ?? 0,
                'is_active' => true,
            ];

            if ($variant) {
                $variant->update($variantAttributes);
            } else {
                ProductVariant::create($variantAttributes);
            }
        }
    }
}

