<?php

namespace App\Models\Landlord;

use App\Traits\HasTranslatedFallback;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\LaravelPackageTools\Concerns\Package\HasTranslations;

class Platform extends BaseLandlordModel
{
    use HasTranslatedFallback, HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'is_active',
        'base_url',
        'auth_url',
        'token_url',
        'api_version',
        'settings',
    ];

    public $translatable = ['name'];

    public function connections(): HasMany
    {
        return $this->hasMany(TenantPlatformConnection::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(TenantPlatformChannel::class);
    }

    public function url(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->base_url.'/'.$this->api_version,
        );
    }


}
