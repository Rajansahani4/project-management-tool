<?php

use App\Enums\ProjectRole;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner   = User::factory()->create();
    $this->token   = JWTAuth::fromUser($this->owner);
    $this->project = Project::factory()->create(['user_id' => $this->owner->id]);

    $ownerRole = Role::where('name', ProjectRole::Owner->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $this->owner->id,
        'role_id'    => $ownerRole->id,
    ]);

    $this->task = Task::factory()->create(['project_id' => $this->project->id]);
});

it('owner assigns a task to a project member', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $response = $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/assign", [
            'user_id' => $member->id,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.assigned_to', $member->id)
        ->assertJsonPath('message', 'Task assigned successfully.');
});

it('cannot assign task to a non-team member', function () {
    $outsider = User::factory()->create();

    $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/assign", [
            'user_id' => $outsider->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('user_id');
});

it('regular member cannot assign tasks', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($member))
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/assign", [
            'user_id' => $member->id,
        ])
        ->assertForbidden();
});

it('admin can assign a task to a project member', function () {
    $admin     = User::factory()->create();
    $adminRole = Role::where('name', ProjectRole::Admin->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $admin->id,
        'role_id'    => $adminRole->id,
    ]);

    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($admin))
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/assign", [
            'user_id' => $member->id,
        ])
        ->assertOk()
        ->assertJsonPath('data.assigned_to', $member->id);
});

it('owner can unassign a task by passing null user_id', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->task->update(['assigned_to' => $member->id]);

    $response = $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/assign", [
            'user_id' => null,
        ]);

    $response->assertOk()
        ->assertJsonPath('data.assigned_to', null);
});
