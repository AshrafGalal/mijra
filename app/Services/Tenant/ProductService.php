<?php

namespace App\Services\Tenant;

use App\DTOs\Tenant\ProductDTO;
use App\Models\Tenant\Filters\ProductFilters;
use App\Models\Tenant\Product;
use App\Services\BaseService;
use App\Services\UploadFileService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductService extends BaseService
{
    public function __construct(protected readonly UploadFileService $uploadFileService) {}

    protected function getFilterClass(): ?string
    {
        return ProductFilters::class;
    }

    protected function baseQuery(): Builder
    {
        return Product::query();
    }

    public function paginate(?array $filters = [], int $perPage = 15)
    {
        return $this->getQuery(filters: $filters)
            ->with(['category', 'thumbnail'])
            ->withSum('variants as total_stock', 'stock')
            ->paginate($perPage);
    }

    /**
     * @throws \Throwable
     */
    public function create(ProductDTO $productDTO): Product
    {
        return DB::connection('tenant')
            ->transaction(function () use ($productDTO) {
                $product = $this->getQuery()->create($productDTO->toArray());

                $this->uploadFileService->assignMediaToModel(model: $product, media_ids: $productDTO->media_ids, collection_name: 'products');

                // upload thumbnail if exists
                if (isset($productDTO->thumbnail_id)) {
                    $this->uploadFileService->assignMediaToModel(model: $product, media_ids: [$productDTO->thumbnail_id], collection_name: 'thumbnail');
                }

                // create product variants
                $this->createProductVariants(product: $product, productDTO: $productDTO);

                return $product;
            });
    }

    public function update(Product|int $product, ProductDTO $productDTO): Product
    {
        if (is_int($product)) {
            $product = parent::findById($product);
        }

        return DB::connection('tenant')
            ->transaction(function () use ($productDTO, $product) {
                $product->update($productDTO->toArray());

                $this->uploadFileService->assignMediaToModel(model: $product, media_ids: $productDTO->media_ids, collection_name: 'product_gallery');

                // upload thumbnail if exists
                if (isset($productDTO->thumbnail_id)) {
                    $this->uploadFileService->assignMediaToModel(model: $product, media_ids: [$productDTO->thumbnail_id], collection_name: 'thumbnails', replace: true);
                }

                // create product variants
                $this->createOrUpdateProductVariants(product: $product, dto: $productDTO);

                return $product;
            });
    }

    public function delete(Product|int $product): ?bool
    {
        if (is_int($product)) {
            $product = parent::findById($product);
        }

        return $product->delete();
    }

    public function details(int $product_id)
    {
        $withRelations = [
            'category', 'productGallery', 'thumbnail', 'variants.attributeValues',
        ];
        $product = $this->getQuery()
            ->with($withRelations)
            ->withSum('variants as total_stock', 'stock')
            ->find($product_id);
        if (! $product) {
            throw new NotFoundHttpException('Product not found');
        }

        return $product;
    }

    private function createProductVariants(Product $product, ProductDTO $productDTO): void
    {
        foreach ($productDTO->variants as $variantData) {
            $variant = $product->variants()->create([
                'sku' => Arr::get($variantData, 'sku'),
                'price' => Arr::get($variantData, 'price'),
                'stock' => Arr::get($variantData, 'stock'),
                'barcode' => Arr::get($variantData, 'barcode'),
            ]);

            $attributes = Arr::get($variantData, 'attributes');

            if (empty($attributes)) {
                continue;
            }
            $variant->attributeValues()->sync(
                collect($attributes)->pluck('attribute_value_id')
            );
        }
    }

    public function createOrUpdateProductVariants(Product $product, ProductDTO $dto)
    {
        $variantsData = collect($dto->variants);

        // 1️⃣  Prepare all variant rows for upsert
        $upsertData = $variantsData->map(fn ($variant) => [
            'id' => $variant['id'] ?? null,
            'product_id' => $product->id,
            'sku' => $variant['sku'],
            'price' => $variant['price'],
            'stock' => $variant['stock'],
            'barcode' => $variant['barcode'] ?? null,
            'weight' => $variant['weight'] ?? null,
            'updated_at' => now(),
            'created_at' => now(),
        ])->toArray();

        // 2️⃣  Perform upsert (insert or update by unique key: id or sku)
        DB::connection('tenant')
            ->table('product_variants')->upsert(
                $upsertData,
                uniqueBy: ['id'],  // you can also use ['sku'] if SKU is unique
                update: ['sku', 'price', 'stock', 'barcode', 'updated_at']
            );

        // 3️⃣  Fetch all current variant IDs (newly created + existing)
        $currentVariantIds = DB::connection('tenant')
            ->table('product_variants')
            ->where('product_id', $product->id)
            ->pluck('id', 'sku')
            ->toArray();

        // 4️⃣  Prepare attribute sync data
        $attributeRows = [];
        foreach ($variantsData as $variant) {
            $variantId = $variant['id'] ?? $currentVariantIds[$variant['sku']] ?? null;
            if (! $variantId) {
                continue;
            }

            foreach ($variant['attributes'] as $attr) {
                $attributeRows[] = [
                    'product_variant_id' => $variantId,
                    'attribute_value_id' => $attr['attribute_value_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 5️⃣  Clean old attributes and insert new in bulk
        DB::connection('tenant')->table('product_variant_attributes')
            ->whereIn('product_variant_id', array_values($currentVariantIds))
            ->delete();

        DB::connection('tenant')->table('product_variant_attributes')->insert($attributeRows);

        // 6️⃣  Delete removed variants (that are not in payload)
        $payloadVariantIds = collect($currentVariantIds)
            ->only($variantsData->pluck('sku'))
            ->values()
            ->toArray();

        DB::connection('tenant')->table('product_variants')
            ->where('product_id', $product->id)
            ->whereNotIn('id', $payloadVariantIds)
            ->delete();
    }
}
