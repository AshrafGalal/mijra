<?php

namespace App\Models\Landlord;

use App\Enum\ChannelStatusEnum;
use App\Enum\ChannelTypeEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPlatformChannel extends BaseLandlordModel
{
    protected $fillable = [
        'tenant_id',
        'platform_id',
        'tenant_platform_connection_id',
        'external_id',
        'name',
        'access_token',
        'token_expires_at',
        'category',
        'category_list',
        'capabilities',
        'meta',
        'settings',
        'status',
    ];

    protected $casts = [
        'category_list' => 'array',
        'capabilities' => 'array',
        'meta' => 'array',
        'settings' => 'array',
        'token_expires_at' => 'datetime',
        'channel_type' => ChannelTypeEnum::class,
        'status' => ChannelStatusEnum::class,
    ];

    protected $hidden = [
        'access_token',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(TenantPlatformConnection::class, 'tenant_platform_connection_id');
    }

    public function isTokenExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }
}
