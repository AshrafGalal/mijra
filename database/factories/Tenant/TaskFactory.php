<?php

namespace Database\Factories\Tenant;

use App\Enum\CustomerSourceEnum;
use App\Enum\CustomerStatusEnum;
use App\Enum\PriorityEnum;
use App\Enum\TaskStatusEnum;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Task;
use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence,
            'description' => fake()->sentence,
            'status' => fake()->randomElement(TaskStatusEnum::values()),
            'customer_id' => Customer::query()->inRandomOrder()->first()->id,
            'user_id' => User::query()->inRandomOrder()->first()->id,
            'priority' => fake()->randomElement(PriorityEnum::values()),
            'due_date' => fake()->dateTimeBetween('+1 week', '+1 month'),
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
