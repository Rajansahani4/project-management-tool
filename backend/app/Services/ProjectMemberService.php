<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ProjectMemberService
{
    /**
     * Return all members of a project with their user and role loaded.
     */
    public function getMembers(Project $project): Collection
    {
        return $project->members()->with(['user', 'role'])->get();
    }

    /**
     * Add a user (looked up by email) to the project with the given role.
     *
     * @throws ValidationException
     */
    public function addMember(Project $project, string $email, string $roleName): ProjectMember
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'email' => ['No user found with that email address.'],
            ]);
        }

        if ($project->members()->where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['This user is already a member of the project.'],
            ]);
        }

        $role = Role::where('name', $roleName)->firstOrFail();

        $member = $project->members()->create([
            'user_id' => $user->id,
            'role_id' => $role->id,
        ]);

        return $member->load(['user', 'role']);
    }

    /**
     * Change a member's role.
     *
     * @throws ValidationException
     */
    public function updateRole(ProjectMember $member, string $roleName): ProjectMember
    {
        // Protect the owner role from being changed
        if ($member->role?->name === 'owner') {
            throw ValidationException::withMessages([
                'role' => ['The project owner role cannot be changed.'],
            ]);
        }

        $role = Role::where('name', $roleName)->firstOrFail();

        $member->update(['role_id' => $role->id]);

        return $member->load(['user', 'role']);
    }

    /**
     * Remove a member from the project.
     *
     * @throws ValidationException
     */
    public function removeMember(ProjectMember $member): void
    {
        if ($member->role?->name === 'owner') {
            throw ValidationException::withMessages([
                'member' => ['The project owner cannot be removed.'],
            ]);
        }

        $member->delete();
    }
}
