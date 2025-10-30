<?php

namespace App\Models\Landlord;

use App\Enum\ActivationStatusEnum;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Source extends BaseLandlordModel
{
    protected $fillable = [
        'name',
        'payout_percentage',
        'is_active',
    ];

    protected $casts = [
        'is_active' => ActivationStatusEnum::class,
        'payout_percentage' => 'decimal:2',
    ];

    /**
     * Get the activation codes for the source.
     */
    public function activationCodes(): HasMany
    {
        return $this->hasMany(ActivationCode::class);
    }

    public function payoutBatches(): HasMany|Source
    {
        return $this->hasMany(SourcePayoutBatch::class);
    }

    public function payoutItems()
    {
        return $this->hasMany(SourcePayoutItem::class);
    }
}
