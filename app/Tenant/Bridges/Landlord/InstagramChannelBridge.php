<?php


namespace App\Tenant\Bridges\Landlord;

use App\Services\Landlord\Channels\InstagramService;

readonly class InstagramChannelBridge
{
    public function __construct(protected InstagramService $instagramService)
    {
    }


}
