<?php

use App\Enums\ProjectRoleEnum;
use App\Events\CommentCreated;
use App\Models\Comment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
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

it('allows a team member to add a comment', function () {
    Event::fake([CommentCreated::class]);

    $response = $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
            'content' => 'This is a great task!',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.content', 'This is a great task!')
        ->assertJsonPath('data.task_id', $this->task->id)
        ->assertJsonPath('data.user_id', $this->owner->id);

    expect(Comment::count())->toBe(1);
    Event::assertDispatched(CommentCreated::class);
});

it('rejects empty content', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
            'content' => '',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');
});

it('rejects content exceeding 5000 characters', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
            'content' => str_repeat('x', 5001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');
});

it('forbids a non-member from commenting', function () {
    $outsider = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($outsider))
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
            'content' => 'I should not be here.',
        ])
        ->assertForbidden();
});

it('requires authentication', function () {
    $this->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments", [
        'content' => 'No token.',
    ])
        ->assertUnauthorized();
});
