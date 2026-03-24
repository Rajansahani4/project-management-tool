<?php

use App\Enums\ProjectRoleEnum;
use App\Models\Comment;
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

    $ownerRole = Role::where('name', ProjectRoleEnum::Owner->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $this->owner->id,
        'role_id'    => $ownerRole->id,
    ]);

    $this->task = Task::factory()->create(['project_id' => $this->project->id]);
});

it('includes comments when fetching task details', function () {
    Comment::factory()->count(3)->create([
        'task_id' => $this->task->id,
        'user_id' => $this->owner->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonPath('data.id', $this->task->id)
        ->assertJsonCount(3, 'data.comments');
});

it('returns comments ordered by created_at ascending', function () {
    $first  = Comment::factory()->create(['task_id' => $this->task->id, 'user_id' => $this->owner->id, 'content' => 'First']);
    $second = Comment::factory()->create(['task_id' => $this->task->id, 'user_id' => $this->owner->id, 'content' => 'Second']);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk();

    $comments = $response->json('data.comments');
    expect($comments[0]['id'])->toBe($first->id)
        ->and($comments[1]['id'])->toBe($second->id);
});

it('returns an empty comments array when there are no comments', function () {
    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonCount(0, 'data.comments');
});

it('includes the commenter user data in each comment', function () {
    Comment::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->owner->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonPath('data.comments.0.user.id', $this->owner->id);
});
