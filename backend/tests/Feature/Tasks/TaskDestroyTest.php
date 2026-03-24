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

it('owner soft deletes a task', function () {
    $response = $this->withToken($this->token)
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonPath('data', null)
        ->assertJsonPath('message', 'Task deleted successfully.');

    expect(Task::count())->toBe(0);
    expect(Task::withTrashed()->count())->toBe(1);
});

it('soft deleted task does not appear in index', function () {
    Task::factory()->count(2)->create(['project_id' => $this->project->id]);
    $this->task->delete();

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('regular member cannot delete a task', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($member))
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}")
        ->assertForbidden();
});

it('owner restores a soft-deleted task', function () {
    $this->task->delete();
    expect(Task::count())->toBe(0);

    $response = $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/restore");

    $response->assertOk()
        ->assertJsonPath('data.id', $this->task->id)
        ->assertJsonPath('message', 'Task restored successfully.');

    expect(Task::count())->toBe(1);
});

it('regular member cannot restore a task', function () {
    $this->task->delete();

    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($member))
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/restore")
        ->assertForbidden();
});

it('returns 404 for non-existent task on restore', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/99999/restore")
        ->assertNotFound();
});
