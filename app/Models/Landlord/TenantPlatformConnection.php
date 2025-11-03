<?php

namespace App\Models\Landlord;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantPlatformConnection extends BaseLandlordModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'platform_id',

        // OAuth-related fields
        'user_access_token',
        'refresh_token',
        'token_expires_at',

        // Platform-specific identifiers
        'external_user_id',
        'external_account_id',

        // Webhook-related fields
        'webhook_id',
        'webhook_secret',

        // Additional data
        'credentials',
        'meta',
        'settings',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'token_expires_at' => 'datetime',
        'credentials' => 'array',
        'meta' => 'array',
        'settings' => 'array',
    ];


    protected $hidden = [
        'access_token',
        'refresh_token',
    ];
    /**
     * Get the tenant that owns the platform.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the platform that owns the tenant platform.
     */
    public function platform(): BelongsTo
    {
        return $this->belongsTo(Platform::class);
    }

    public function updatePageInfo(array $pageData): bool
    {
        $this->external_id = $pageData['id']; // Store page ID as external_id

        return $this->update([
            'external_id' => $pageData['id'],
            'meta' => array_merge($this->meta ?? [], [
                'page_name' => $pageData['name'],
                'page_access_token' => $pageData['access_token'],
                'page_category' => $pageData['category'] ?? null,
                'page_category_list' => $pageData['category_list'] ?? [],
                'last_synced_at' => Carbon::now()->toISOString(),
            ])
        ]);
    }
}
