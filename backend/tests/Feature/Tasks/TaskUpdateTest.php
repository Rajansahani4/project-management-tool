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

it('owner can update task details', function () {
    $response = $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}", [
            'title'    => 'Updated task title',
            'priority' => 'low',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated task title')
        ->assertJsonPath('data.priority', 'low')
        ->assertJsonPath('message', 'Task updated successfully.');
});

it('partial update only changes provided fields', function () {
    $originalDescription = $this->task->description;

    $response = $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}", [
            'title' => 'Only title changed here',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Only title changed here')
        ->assertJsonPath('data.description', $originalDescription);
});

it('assignee can update the task', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->task->update(['assigned_to' => $member->id]);

    $this->withToken(JWTAuth::fromUser($member))
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}", [
            'title' => 'Updated by assignee here',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated by assignee here');
});

it('regular member who is not assignee cannot update task', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    // Task is NOT assigned to this member
    $this->withToken(JWTAuth::fromUser($member))
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}", [
            'title' => 'Should not be allowed',
        ])
        ->assertForbidden();
});

it('rejects title shorter than 5 characters on update', function () {
    $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}", [
            'title' => 'Fix',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');
});

it('admin can update task', function () {
    $admin     = User::factory()->create();
    $adminRole = Role::where('name', ProjectRole::Admin->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $admin->id,
        'role_id'    => $adminRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($admin))
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}", [
            'title' => 'Admin updated this task',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Admin updated this task');
});
