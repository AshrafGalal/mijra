<?php

namespace App\Jobs;

use App\Models\Tenant\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncSallaProductJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $productData,
        public string $action
    ) {
        //
    }

    public function handle(): void
    {
        $sallaProductId = $this->productData['id'] ?? null;
        $name = $this->productData['name'] ?? null;

        if (!$sallaProductId || !$name) {
            return;
        }

        $product = Product::where('sku', "salla_{$sallaProductId}")->first();

        $productAttributes = [
            'name' => $name,
            'description' => $this->productData['description'] ?? '',
            'sku' => "salla_{$sallaProductId}",
            'price' => $this->productData['price'] ?? 0,
            'quantity' => $this->productData['quantity'] ?? 0,
            'status' => $this->productData['status'] === 'sale' ? \App\Enum\ProductStatusEnum::ACTIVE->value : \App\Enum\ProductStatusEnum::INACTIVE->value,
            'metadata' => [
                'salla_product_id' => $sallaProductId,
                'currency' => 'SAR',
            ],
        ];

        if ($product) {
            $product->update($productAttributes);
        } else {
            Product::create($productAttributes);
        }
    }
}
