<?php

use App\Enums\ProjectRoleEnum;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');

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

it('includes attachments when fetching task details', function () {
    Attachment::factory()->count(2)->create([
        'task_id' => $this->task->id,
        'user_id' => $this->owner->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonCount(2, 'data.attachments');
});

it('returns an empty attachments array when there are none', function () {
    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonCount(0, 'data.attachments');
});

it('includes a download_url in each attachment', function () {
    Attachment::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->owner->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonStructure(['data' => ['attachments' => [['id', 'filename', 'file_size', 'mime_type', 'download_url']]]]);

    expect($response->json('data.attachments.0.download_url'))->toBeString()->not->toBeEmpty();
});

it('includes the uploader user data in each attachment', function () {
    Attachment::factory()->create([
        'task_id' => $this->task->id,
        'user_id' => $this->owner->id,
    ]);

    $response = $this->withToken($this->token)
        ->getJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}");

    $response->assertOk()
        ->assertJsonPath('data.attachments.0.user.id', $this->owner->id);
});
