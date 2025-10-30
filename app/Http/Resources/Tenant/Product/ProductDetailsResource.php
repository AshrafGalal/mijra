<?php

namespace App\Http\Resources\Tenant\Product;

use App\Http\Resources\Tenant\CategoryResource;
use App\Http\Resources\Tenant\Media\MediaResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResource extends JsonResource
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
            'name' => $this->name,
            'thumbnail' => MediaResource::make($this->whenLoaded('thumbnail')),
            'media' => MediaResource::collection($this->whenLoaded('productGallery')),
            'description' => $this->description,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'base_price' => (float) $this->base_price,
            'stock' => (int) $this->total_stock,
            'tags' => $this->tags,
            'status' => $this->status->value,
            'status_text' => $this->status->getLabel(),
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
        ];
    }
}
