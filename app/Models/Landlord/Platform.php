<?php

namespace App\Models\Landlord;

use App\Traits\HasTranslatedFallback;
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
}
