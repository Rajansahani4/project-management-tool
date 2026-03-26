<?php

namespace App\Services;

use App\Enums\ProjectRoleEnum;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function index(User $user): LengthAwarePaginator
    {
        return $user->projects()->withCount(['members', 'tasks'])->latest()->paginate(15);
    }

    public function store(User $user, array $data): Project
    {
        $project = $user->projects()->create($data);

        $ownerRole = Role::where('name', ProjectRoleEnum::Owner->value)->firstOrFail();

        ProjectMember::create([
            'project_id' => $project->id,
            'user_id'    => $user->id,
            'role_id'    => $ownerRole->id,
        ]);

        return $project;
    }

    public function update(Project $project, array $data): Project
    {
        $project->update($data);

        return $project->fresh();
    }

    public function delete(Project $project): void
    {
        $project->delete();
    }
}
