<?php

namespace App\Jobs;

use App\Models\Tenant\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncWooCommerceProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $productData,
        public string $source,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        $wooProductId = $this->productData['id'] ?? null;
        $name = $this->productData['name'] ?? null;

        if (!$wooProductId || !$name) {
            return;
        }

        $product = Product::where('sku', "woo_{$wooProductId}")->first();

        $productAttributes = [
            'name' => $name,
            'description' => strip_tags($this->productData['description'] ?? ''),
            'sku' => "woo_{$wooProductId}",
            'price' => $this->productData['price'] ?? 0,
            'quantity' => $this->productData['stock_quantity'] ?? 0,
            'status' => $this->productData['status'] === 'publish' ? \App\Enum\ProductStatusEnum::ACTIVE->value : \App\Enum\ProductStatusEnum::INACTIVE->value,
            'metadata' => [
                'woocommerce_product_id' => $wooProductId,
                'regular_price' => $this->productData['regular_price'] ?? null,
                'sale_price' => $this->productData['sale_price'] ?? null,
            ],
        ];

        if ($product) {
            $product->update($productAttributes);
        } else {
            Product::create($productAttributes);
        }
    }
}

