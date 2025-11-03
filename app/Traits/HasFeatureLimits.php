<?php

namespace App\Traits;

use App\Services\Landlord\Actions\Subscription\FeatureUsageService;

trait HasFeatureLimits
{
    /**
     * @throws \Throwable
     */
    public function consumeFeature(string $slug, int $amount = 1): bool
    {
        return app(FeatureUsageService::class)->consumeFeature($this->id, $slug, $amount);
    }

    public function releaseFeatureUsage(string $slug, int $amount = 1): void
    {
        app(FeatureUsageService::class)->releaseFeature($this->id, $slug, $amount);
    }

    public function canUseFeature(string $slug, int $amount = 1): void
    {
        app(FeatureUsageService::class)->canUseFeature($this->id, $slug, $amount);
    }
}
