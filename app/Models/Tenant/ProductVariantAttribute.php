<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductVariantAttribute extends BaseTenantModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_variant_id',
        'attribute_value_id',
    ];

    /**
     * Get the product variant that owns the attribute.
     */
    public function productVariant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the attribute value that owns the product variant attribute.
     */
    public function attributeValue(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class);
    }

    /**
     * Get the attribute through the attribute value.
     */
    public function attribute()
    {
        return $this->hasOneThrough(
            Attribute::class,
            AttributeValue::class,
            'id',
            'id',
            'attribute_value_id',
            'attribute_id'
        );
    }
}
