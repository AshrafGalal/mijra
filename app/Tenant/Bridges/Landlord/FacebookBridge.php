<?php

namespace App\Tenant\Bridges\Landlord;

use App\Services\Landlord\Facebook\FacebookService;
use Illuminate\Http\Client\ConnectionException;

readonly class FacebookBridge
{
    public function __construct(protected FacebookService $facebookService)
    {
    }

    /**
     * @throws ConnectionException
     */
    public function getPages(): array
    {
        return $this->facebookService->getPages();
    }

}
