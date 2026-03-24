<?php

use App\Enums\ProjectRole;
use App\Enums\TaskStatus;
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
});

it('lists all tasks for a project member', function () {
    Task::factory()->count(3)->create(['project_id' => $this->project->id]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks");

    $response->assertOk()
        ->assertJsonCount(3, 'data');
});

it('filters tasks by status', function () {
    Task::factory()->count(2)->todo()->create(['project_id' => $this->project->id]);
    Task::factory()->count(3)->inProgress()->create(['project_id' => $this->project->id]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks?status=in_progress");

    $response->assertOk()
        ->assertJsonCount(3, 'data');

    collect($response->json('data'))->each(
        fn ($task) => expect($task['status'])->toBe(TaskStatus::InProgress->value)
    );
});

it('filters tasks by assigned_to', function () {
    $member     = User::factory()->create();
    $memberRole = Role::where('name', ProjectRole::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $member->id,
        'role_id'    => $memberRole->id,
    ]);

    Task::factory()->count(2)->create(['project_id' => $this->project->id, 'assigned_to' => $member->id]);
    Task::factory()->create(['project_id' => $this->project->id]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks?assigned_to={$member->id}");

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('returns 403 for non-members', function () {
    $outsider = User::factory()->create();
    $token    = JWTAuth::fromUser($outsider);

    $this->withToken($token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks")
        ->assertForbidden();
});

it('returns 401 for unauthenticated requests', function () {
    $this->getJson("/api/v1/projects/{$this->project->id}/tasks")
        ->assertUnauthorized();
});
