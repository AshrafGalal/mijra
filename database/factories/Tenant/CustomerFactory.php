<?php

namespace Database\Factories\Tenant;

use App\Enum\CustomerSourceEnum;
use App\Enum\CustomerStatusEnum;
use App\Models\Tenant\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'first_name' => $this->faker->name(),
            'last_name' => $this->faker->name(),
            'country_code' => $this->faker->countryCode(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'source' => $this->faker->randomElement(CustomerSourceEnum::cases()),
            'address' => $this->faker->address(),
            'country' => $this->faker->country,
            'city' => $this->faker->city,
            'zipcode' => $this->faker->postcode,
            'status' => $this->faker->randomElement(CustomerStatusEnum::cases()),
        ];
    }

    /**
     * Set customer status to active
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatusEnum::ACTIVE,
        ]);
    }

    /**
     * Set customer as lead
     */
    public function lead(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CustomerStatusEnum::LEAD,
        ]);
    }

    /**
     * Set customer source as website
     */
    public function fromWebsite(): static
    {
        return $this->state(fn (array $attributes) => [
            'source' => CustomerSourceEnum::WEBSITE,
        ]);
    }
}
