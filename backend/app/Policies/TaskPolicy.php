<?php

namespace App\Policies;

use App\Enums\ProjectRoleEnum;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;

class TaskPolicy
{
    public function viewAny(User $user, Project $project): bool
    {
        return $this->isMemberOf($user, $project);
    }

    public function view(User $user, Task $task): bool
    {
        return $this->isMemberOf($user, $task->project);
    }

    public function create(User $user, Project $project): bool
    {
        return $this->isMemberOf($user, $project);
    }

    public function update(User $user, Task $task): bool
    {
        return $this->isPrivilegedIn($user, $task->project)
            || $task->assigned_to === $user->id;
    }

    public function delete(User $user, Task $task): bool
    {
        return $this->isPrivilegedIn($user, $task->project);
    }

    public function updateStatus(User $user, Task $task): bool
    {
        return $this->isMemberOf($user, $task->project);
    }

    public function assign(User $user, Task $task): bool
    {
        return $this->isPrivilegedIn($user, $task->project);
    }

    public function restore(User $user, Task $task): bool
    {
        return $this->isPrivilegedIn($user, $task->project);
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function isMemberOf(User $user, Project $project): bool
    {
        return ProjectMember::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->exists();
    }

    private function isPrivilegedIn(User $user, Project $project): bool
    {
        return ProjectMember::where('project_id', $project->id)
            ->where('user_id', $user->id)
            ->whereHas('role', fn ($q) => $q->whereIn('name', [
                ProjectRoleEnum::Owner->value,
                ProjectRoleEnum::Admin->value,
            ]))
            ->exists();
    }
}
