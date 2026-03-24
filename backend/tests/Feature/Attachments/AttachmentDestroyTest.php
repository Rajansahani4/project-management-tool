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

    // Place a real fake file on the fake disk so the service can delete it
    Storage::disk('local')->put("attachments/{$this->task->id}/report.pdf", 'fake content');

    $this->attachment = Attachment::factory()->create([
        'task_id'   => $this->task->id,
        'user_id'   => $this->owner->id,
        'file_path' => "attachments/{$this->task->id}/report.pdf",
    ]);
});

it('allows the uploader to delete their attachment', function () {
    $this->withToken($this->token)
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments/{$this->attachment->id}")
        ->assertOk()
        ->assertJsonPath('data', null)
        ->assertJsonPath('message', 'Attachment deleted successfully.');

    expect(Attachment::count())->toBe(0);
    Storage::disk('local')->assertMissing($this->attachment->file_path);
});

it('removes the physical file from storage on delete', function () {
    Storage::disk('local')->assertExists($this->attachment->file_path);

    $this->withToken($this->token)
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments/{$this->attachment->id}")
        ->assertOk();

    Storage::disk('local')->assertMissing($this->attachment->file_path);
});

it('forbids a team member from deleting another member\'s attachment', function () {
    $other      = User::factory()->create();
    $memberRole = Role::where('name', ProjectRoleEnum::Member->value)->firstOrFail();
    ProjectMember::factory()->create([
        'project_id' => $this->project->id,
        'user_id'    => $other->id,
        'role_id'    => $memberRole->id,
    ]);

    $this->withToken(JWTAuth::fromUser($other))
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments/{$this->attachment->id}")
        ->assertForbidden();

    expect(Attachment::count())->toBe(1);
});

it('returns 404 for a non-existent attachment', function () {
    $this->withToken($this->token)
        ->deleteJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments/9999")
        ->assertNotFound();
});
