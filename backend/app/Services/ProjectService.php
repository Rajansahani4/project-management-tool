<?php

namespace App\Services;

use App\Models\Project;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function index(User $user): LengthAwarePaginator
    {
        return $user->projects()->latest()->paginate(15);
    }

    public function store(User $user, array $data): Project
    {
        return $user->projects()->create($data);
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
