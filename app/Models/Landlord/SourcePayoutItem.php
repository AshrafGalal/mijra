<?php

namespace App\Models\Landlord;

use App\Enum\SourcePayoutCollectionEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SourcePayoutItem extends BaseLandlordModel
{
    protected $fillable = ['source_payout_batch_id', 'activation_code_id', 'payout_amount', 'collected_at'];

    public function payoutBatch(): BelongsTo
    {
        return $this->belongsTo(SourcePayoutBatch::class);
    }

    public function activationCode(): BelongsTo
    {
        return $this->belongsTo(ActivationCode::class);
    }

    public function isCollected(): string
    {
        return ! is_null($this->collected_at) ? SourcePayoutCollectionEnum::COLLECTED->value : SourcePayoutCollectionEnum::PENDING->value;
    }
}
