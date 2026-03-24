<?php

namespace Database\Factories;

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id'    => User::factory(),
            'role_id'    => fn () => Role::where('name', ProjectRole::Member->value)->value('id'),
        ];
    }

    public function owner(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name', ProjectRole::Owner->value)->value('id'),
        ]);
    }

    public function admin(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name', ProjectRole::Admin->value)->value('id'),
        ]);
    }

    public function member(): static
    {
        return $this->state(fn () => [
            'role_id' => Role::where('name', ProjectRole::Member->value)->value('id'),
        ]);
    }
}
