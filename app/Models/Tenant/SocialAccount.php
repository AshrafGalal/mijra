<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SocialAccount extends BaseTenantModel
{
    protected $fillable = [
        'platform_id', 'platform_account_id', 'account_name',
        'phone_number', 'username', 'access_token', 'refresh_token',
        'token_expires_at', 'webhook_verify_token', 'is_active', 'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'json',
        'token_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token', 'refresh_token', 'webhook_verify_token',
    ];

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }
}
