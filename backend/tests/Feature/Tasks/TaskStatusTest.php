<?php

use App\Enums\ProjectRole;
use App\Enums\TaskStatus;
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

    $ownerRole = Role::where('name', ProjectRole::Owner->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $this->owner->id,
        'role_id'    => $ownerRole->id,
    ]);

    $this->task = Task::factory()->todo()->create(['project_id' => $this->project->id]);
});

it('any project member can change task status', function () {
    $response = $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/status", [
            'status' => 'in_progress',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.status', TaskStatus::InProgress->value)
        ->assertJsonPath('message', 'Task status updated successfully.');
});

it('creates a status log entry on status change', function () {
    $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/status", [
            'status' => 'in_progress',
        ]);

    expect(TaskStatusLog::count())->toBe(1);

    $log = TaskStatusLog::first();
    expect($log->task_id)->toBe($this->task->id)
        ->and($log->from_status)->toBe(TaskStatus::Todo->value)
        ->and($log->to_status)->toBe(TaskStatus::InProgress->value)
        ->and($log->changed_by)->toBe($this->owner->id);
});

it('records each status transition in order', function () {
    $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/status", [
            'status' => 'in_progress',
        ]);

    $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/status", [
            'status' => 'completed',
        ]);

    expect(TaskStatusLog::count())->toBe(2);

    $logs = TaskStatusLog::orderBy('id')->get();
    expect($logs[0]->from_status)->toBe('todo')
        ->and($logs[0]->to_status)->toBe('in_progress')
        ->and($logs[1]->from_status)->toBe('in_progress')
        ->and($logs[1]->to_status)->toBe('completed');
});

it('rejects an invalid status value', function () {
    $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/status", [
            'status' => 'pending',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('returns 403 when a non-member changes task status', function () {
    $outsider = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($outsider))
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/status", [
            'status' => 'in_progress',
        ])
        ->assertForbidden();
});

it('regular member can also change task status', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($member))
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/status", [
            'status' => 'completed',
        ])
        ->assertOk()
        ->assertJsonPath('data.status', 'completed');
});
