<?php

namespace App\Models\Tenant;

use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Story extends BaseTenantModel implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'platform',
        'external_identifier_id',
        'body',
        'has_media',
        'type',
        'expires_at',
        'metadata',
        'contact_identifier_id',
        'contact_name',
        'customer_id'
    ];
}
