<?php


namespace App\Tenant\Bridges\Landlord;

use App\Services\Landlord\Channels\MessengerService;

readonly class MessengerChannelBridge
{
    public function __construct(protected MessengerService $messengerService)
    {
    }


}
