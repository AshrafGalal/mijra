<?php

namespace Database\Factories\Landlord;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tenant\User>
 */
class TenantFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->name;

        return [
            'name' => $name,
            'database' => 'barmagiat_tenant_database'.fake()->numberBetween(1, 2),
            'slug' => Str::slug(fake()->words(2, true), '_'), // e.g., "fresh_tomato"
        ];
    }
}
