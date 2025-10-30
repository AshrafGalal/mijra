<?php

use App\Enum\SubscriptionBillingCycleEnum;
use App\Models\Landlord\Plan;
use Illuminate\Support\Collection;
use voku\helper\ASCII;

if (! function_exists('countriesData')) {
    function countriesData(): Collection
    {
        // Load your JSON file (stored in storage or resources)
        $jsonPath = database_path('top_100_country_phone_codes.json'); // example path
        $countries = json_decode(file_get_contents($jsonPath), true);

        return collect($countries);
    }
}

if (! function_exists('currencyCodes')) {
    function currencyCodes(): array
    {
        $json = file_get_contents(database_path('currencies.json'));
        $currencies = json_decode($json, true);

        return array_column($currencies, 'code');
    }
}

if (! function_exists('countriesIsoCode')) {
    function countriesIsoCode(): array
    {
        $json = file_get_contents(database_path('top_100_country_phone_codes.json'));
        $currencies = json_decode($json, true);

        return array_column($currencies, 'iso2');
    }
}

if (! function_exists('getTimezones')) {
    function getTimezones(): array
    {
        $json = file_get_contents(database_path('timezone.json'));
        $timezones = json_decode($json, true);

        return array_column($timezones, 'tz');
    }
}

if (! function_exists('calculateSubscriptionEndDate')) {
    function calculateSubscriptionEndDate(SubscriptionBillingCycleEnum $duration): ?\DateTime
    {
        return match ($duration->value) {
            SubscriptionBillingCycleEnum::MONTHLY->value => now()->addMonth(),
            SubscriptionBillingCycleEnum::ANNUAL->value => now()->addYear(),
            SubscriptionBillingCycleEnum::LIFETIME->value => null,
        };
    }
}
if (! function_exists('calculateSubscriptionAmount')) {
    function calculateSubscriptionAmount(Plan $plan, SubscriptionBillingCycleEnum $duration): float
    {
        return match ($duration->value) {
            SubscriptionBillingCycleEnum::MONTHLY->value => $plan->monthly_price,
            SubscriptionBillingCycleEnum::ANNUAL->value => $plan->annual_price,
            SubscriptionBillingCycleEnum::LIFETIME->value => $plan->lifetime_price,
        };
    }
}

if (! function_exists('slugifyUnicode')) {
    function slugifyUnicode(string $string): string
    {
        $slug = preg_replace('/\s+/u', '-', trim($string));
        $slug = preg_replace('/[^\p{L}\p{N}\-]+/u', '', $slug);
        $slug = mb_strtolower($slug, 'UTF-8');

        return $slug;
    }
}
if (! function_exists('generateSlug')) {
    function generateSlug(string $name): string
    {
        // Try to transliterate Arabic to Latin characters
        $slug = Str::slug(ASCII::to_ascii($name, 'ar'), '-');

        // Fallback if transliteration fails
        if (empty($slug)) {
            $slug = slugifyUnicode($name);
        }

        return $slug;
    }
}
