<?php

namespace Database\Factories\Tenant;

use App\Enum\CustomerSourceEnum;
use App\Enum\CustomerStatusEnum;
use App\Models\Tenant\Workflow;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkflowFactory extends Factory
{
    protected $model = Workflow::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'description' => fake()->text(),
            'is_active' => fake()->boolean(),
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
