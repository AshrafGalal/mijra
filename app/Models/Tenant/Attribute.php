<?php

namespace App\Models\Tenant;

use App\Enum\ActivationStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attribute extends BaseTenantModel
{
    protected $fillable = [
        'name',
        'slug',
        'status',
    ];

    protected $casts = [
        'status' => ActivationStatusEnum::class,
    ];

    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function productVariantAttributes(): HasMany
    {
        return $this->hasMany(ProductVariantAttribute::class);
    }
}
