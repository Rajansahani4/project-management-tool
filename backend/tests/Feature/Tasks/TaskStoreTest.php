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

it('creates a task in a project', function () {
    $response = $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", [
            'title'       => 'Fix critical login bug',
            'description' => 'Users cannot log in with SSO.',
            'priority'    => 'high',
            'due_date'    => now()->addDays(7)->toDateString(),
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Fix critical login bug')
        ->assertJsonPath('data.priority', 'high')
        ->assertJsonPath('data.status', TaskStatus::Todo->value)
        ->assertJsonPath('data.project_id', $this->project->id);

    expect(Task::count())->toBe(1);
});

it('defaults status to todo', function () {
    $response = $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", [
            'title' => 'Default status task',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.status', 'todo');
});

it('defaults priority to medium', function () {
    $response = $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", [
            'title' => 'Default priority task',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.priority', 'medium');
});

it('rejects title shorter than 5 characters', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", ['title' => 'Fix'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');
});

it('rejects title longer than 200 characters', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", ['title' => str_repeat('a', 201)])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('title');
});

it('rejects description longer than 5000 characters', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", [
            'title'       => 'Valid title here',
            'description' => str_repeat('x', 5001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('description');
});

it('rejects invalid priority value', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", [
            'title'    => 'Valid title here',
            'priority' => 'urgent',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('priority');
});

it('rejects a due_date in the past', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", [
            'title'    => 'Valid title here',
            'due_date' => now()->subDay()->toDateString(),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('due_date');
});

it('returns 403 when a non-member tries to create a task', function () {
    $outsider = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($outsider))
        ->postJson("/api/v1/projects/{$this->project->id}/tasks", ['title' => 'Should fail here'])
        ->assertForbidden();
});
