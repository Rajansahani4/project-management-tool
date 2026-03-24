<?php

namespace Database\Factories;

use App\Enums\ProjectStatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id'     => User::factory(),
            'name'        => $this->faker->sentence(3),
            'description' => $this->faker->paragraph(),
            'status'      => ProjectStatusEnum::Active,
            'due_date'    => $this->faker->optional()->dateTimeBetween('now', '+1 year')?->format('Y-m-d'),
        ];
    }

    public function archived(): static
    {
        return $this->state(['status' => ProjectStatusEnum::Archived]);
    }
}
