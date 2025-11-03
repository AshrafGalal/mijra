<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialLogin extends BaseLandlordModel
{
    protected $fillable = [
        'user_id',
        'provider_name',
        'provider_id',
        'access_token',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
