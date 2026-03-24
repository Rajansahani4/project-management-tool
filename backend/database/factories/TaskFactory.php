<?php

namespace Database\Factories;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id'  => Project::factory(),
            'title'       => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'status'      => TaskStatusEnum::Todo,
            'priority'    => TaskPriorityEnum::Medium,
            'assigned_to' => null,
            'due_date'    => $this->faker->optional()->dateTimeBetween('+1 day', '+6 months')?->format('Y-m-d'),
        ];
    }

    public function todo(): static
    {
        return $this->state(['status' => TaskStatusEnum::Todo]);
    }

    public function inProgress(): static
    {
        return $this->state(['status' => TaskStatusEnum::InProgress]);
    }

    public function completed(): static
    {
        return $this->state(['status' => TaskStatusEnum::Completed]);
    }

    public function archived(): static
    {
        return $this->state(['status' => TaskStatusEnum::Archived]);
    }

    public function highPriority(): static
    {
        return $this->state(['priority' => TaskPriorityEnum::High]);
    }
}
