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

    $this->task    = Task::factory()->create(['project_id' => $this->project->id]);
    $this->comment = Comment::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->owner->id,
        'content' => 'Original content.',
    ]);
});

it('allows a user to edit their own comment', function () {
    $response = $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$this->comment->id}", [
            'content' => 'Updated content.',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.content', 'Updated content.');

    expect($this->comment->fresh()->content)->toBe('Updated content.');
});

it('forbids a user from editing another user\'s comment', function () {
    $other      = User::factory()->create();
    $memberRole = Role::where('name', ProjectRoleEnum::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $other->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($other))
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$this->comment->id}", [
            'content' => 'Sneaky edit.',
        ])
        ->assertForbidden();
});

it('rejects empty content on update', function () {
    $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$this->comment->id}", [
            'content' => '',
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');
});

it('rejects content exceeding 5000 characters on update', function () {
    $this->withToken($this->token)
        ->patchJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$this->comment->id}", [
            'content' => str_repeat('x', 5001),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('content');
});
