<?php

namespace App\DTOs\Tenant;

use App\DTOs\Abstract\BaseDTO;
use App\Enum\DiscountTypeEnum;
use App\Enum\ProductStatusEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ProductDTO extends BaseDTO
{
    public function __construct(
        public string $name,
        public int $category_id,
        public ?string $description = null,
        public ?string $slug = null,
        public float $base_price = 0,
        public float $discount = 0,
        public string $discount_type = DiscountTypeEnum::PERCENTAGE->value,
        public string $status = ProductStatusEnum::PUBLISHED->value,
        public float $vat_percentage = 0,
        public ?string $thumbnail_id = null,
        public ?array $media_ids = null,
        public ?array $tags = null,
        public ?array $variants = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new self(
            name: Arr::get($data, 'name'),
            category_id: Arr::get($data, 'category_id'),
            description: Arr::get($data, 'name'),
            slug: Arr::get($data, 'slug', str(Arr::get($data, 'name'))->slug()->toString()),
            base_price: Arr::get($data, 'base_price'),
            discount: Arr::get($data, 'discount', 0),
            discount_type: Arr::get($data, 'discount', DiscountTypeEnum::PERCENTAGE->value),
            status: Arr::get($data, 'status', ProductStatusEnum::PUBLISHED->value),
            vat_percentage: Arr::get($data, 'vat_percentage', 0),
            thumbnail_id: Arr::get($data, 'thumbnail_id'),
            media_ids: Arr::get($data, 'media_ids'),
            tags: Arr::get($data, 'tags'),
            variants: Arr::get($data, 'variants', ProductStatusEnum::PUBLISHED->value),
        );
    }

    public static function fromRequest(Request $request): static
    {
        return new self(
            name: $request->name,
            category_id: $request->category_id,
            description: $request->description,
            slug: $request->slug ?? str($request->name)->slug()->toString(),
            base_price: $request->base_price,
            discount: $request->discount ?? 0,
            discount_type: $request->discount_type ?? DiscountTypeEnum::PERCENTAGE->value,
            status: $request->status ?? ProductStatusEnum::PUBLISHED->value,
            vat_percentage: $request->vat_percentage ?? 0,
            thumbnail_id: $request->thumbnail_id,
            media_ids: $request->media_ids,
            tags: $request->tags,
            variants: $request->variants,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'description' => $this->description,
            'slug' => $this->slug,
            'category_id' => $this->category_id,
            'base_price' => $this->base_price,
            'discount' => $this->discount,
            'discount_type' => $this->discount_type,
            'status' => $this->status,
            'vat_percentage' => $this->vat_percentage,
            'tags' => $this->tags,
        ];
    }
}
