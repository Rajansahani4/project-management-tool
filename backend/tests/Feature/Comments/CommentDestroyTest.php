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
    ]);
});

it('allows a user to delete their own comment', function () {
    $this->withToken($this->token)
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$this->comment->id}")
        ->assertOk()
        ->assertJsonPath('data', null);

    expect(Comment::count())->toBe(0);
});

it('forbids a user from deleting another user\'s comment', function () {
    $other      = User::factory()->create();
    $memberRole = Role::where('name', ProjectRoleEnum::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $other->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($other))
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments/{$this->comment->id}")
        ->assertForbidden();

    expect(Comment::count())->toBe(1);
});

it('returns 404 for a non-existent comment', function () {
    $this->withToken($this->token)
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/comments/9999")
        ->assertNotFound();
});
