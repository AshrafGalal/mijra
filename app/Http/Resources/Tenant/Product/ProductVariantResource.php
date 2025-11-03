<?php

namespace App\Http\Resources\Tenant\Product;

use App\Http\Resources\Tenant\AttributeValueResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'price' => (float) $this->price,
            'stock' => (int) $this->stock,
            'barcode' => $this->barcode,
            'weight' => (float) $this->weight,
            'attributes' => AttributeValueResource::collection($this->whenLoaded('attributeValues')),

        ];
    }
}
