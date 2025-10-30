<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCodeUsage extends BaseLandlordModel
{
    protected $fillable = [
        'discount_code_id',
        'tenant_id',
        'subscription_id',
        'invoice_id',
    ];

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }
}
