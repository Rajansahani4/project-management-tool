<?php

use App\Enums\ProjectRoleEnum;
use App\Events\AttachmentUploaded;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Event::fake([AttachmentUploaded::class]);

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

it('uploads a file and returns the attachment resource', function () {
    $file = UploadedFile::fake()->create('report.pdf', 500, 'application/pdf');

    $response = $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments", [
            'file' => $file,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.filename', 'report.pdf')
        ->assertJsonPath('data.task_id', $this->task->id)
        ->assertJsonPath('data.user_id', $this->owner->id)
        ->assertJsonStructure(['data' => ['id', 'filename', 'file_size', 'mime_type', 'download_url']]);

    expect(Attachment::count())->toBe(1);
    Event::assertDispatched(AttachmentUploaded::class);
});

it('stores the file inside the task directory', function () {
    $file = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments", [
            'file' => $file,
        ])
        ->assertCreated();

    $attachment = Attachment::first();
    Storage::disk('local')->assertExists($attachment->file_path);
    expect($attachment->file_path)->toStartWith("attachments/{$this->task->id}/");
});

it('rejects a file exceeding 10MB', function () {
    $file = UploadedFile::fake()->create('huge.pdf', 11_000, 'application/pdf');

    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments", [
            'file' => $file,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});

it('rejects a disallowed file type', function () {
    $file = UploadedFile::fake()->create('malware.exe', 100, 'application/octet-stream');

    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments", [
            'file' => $file,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});

it('rejects a request with no file', function () {
    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments", [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});

it('forbids a non-member from uploading', function () {
    $outsider = User::factory()->create();
    $file     = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

    $this->withToken(JWTAuth::fromUser($outsider))
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments", [
            'file' => $file,
        ])
        ->assertForbidden();
});

it('requires authentication to upload', function () {
    $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

    $this->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments", [
        'file' => $file,
    ])
        ->assertUnauthorized();
});

it('allows all permitted mime types', function (string $name, string $mime) {
    $file = UploadedFile::fake()->create($name, 100, $mime);

    $this->withToken($this->token)
        ->postJson("/api/v1/projects/{$this->project->id}/tasks/{$this->task->id}/attachments", [
            'file' => $file,
        ])
        ->assertCreated();
})->with([
    ['document.pdf',  'application/pdf'],
    ['sheet.xls',     'application/vnd.ms-excel'],
    ['sheet.xlsx',    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
    ['image.jpg',     'image/jpeg'],
    ['image.png',     'image/png'],
    ['notes.txt',     'text/plain'],
]);
