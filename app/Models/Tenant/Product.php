<?php

namespace App\Models\Tenant;

use App\Enum\DiscountTypeEnum;
use App\Enum\ProductStatusEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends BaseTenantModel implements HasMedia
{
    use InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'slug', 'base_price',
        'discount', 'discount_type',
        'status', 'vat_percentage',
        'tags', 'category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'vat_percentage' => 'decimal:2',
        'discount_type' => DiscountTypeEnum::class,
        'status' => ProductStatusEnum::class,
        'tags' => 'array',
    ];

    /**
     * Get the variants for the product.
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // One-to-many collection: product gallery
    public function productGallery(): MorphMany
    {
        return $this->media()->where('collection_name', 'product');
    }

    // Single media collection: thumbnail

    public function thumbnail(): MorphOne
    {
        return $this->morphOne($this->getMediaModel(), 'model')
            ->where('collection_name', 'thumbnail');
    }
}
