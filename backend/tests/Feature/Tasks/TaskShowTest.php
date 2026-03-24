<?php

use App\Enums\ProjectRoleEnum;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\Task;
use App\Models\TaskStatusLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->owner   = User::factory()->create();
    $this->token   = JWTAuth::fromUser($this->owner);
    $this->project = Project::factory()->create(['user_id' => $this->owner->id]);

    $ownerRole = Role::where('name', ProjectRoleEnum::Owner->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $this->owner->id,
        'role_id'    => $ownerRole->id,
    ]);

    $this->task = Task::factory()->create(['project_id' => $this->project->id]);
});

it('returns task details for a project member', function () {
    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $this->task->id)
        ->assertJsonPath('data.title', $this->task->title)
        ->assertJsonPath('message', 'Task retrieved successfully.');
});

it('includes status_logs in the response', function () {
    TaskStatusLog::create([
        'task_id'     => $this->task->id,
        'changed_by'  => $this->owner->id,
        'from_status' => 'todo',
        'to_status'   => 'in_progress',
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonCount(1, 'data.status_logs')
        ->assertJsonPath('data.status_logs.0.from_status', 'todo')
        ->assertJsonPath('data.status_logs.0.to_status', 'in_progress');
});

it('includes assignee details when task is assigned', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRoleEnum::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->task->update(['assigned_to' => $member->id]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonPath('data.assignee.id', $member->id)
        ->assertJsonPath('data.assignee.name', $member->name);
});

it('returns 403 for a non-member', function () {
    $outsider = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($outsider))
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}")
        ->assertForbidden();
});
