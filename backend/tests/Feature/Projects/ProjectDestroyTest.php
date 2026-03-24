<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('deletes a project owned by the authenticated user', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->deleteJson("/api/v1/projects/{$project->id}");

    $response->assertOk()
        ->assertJsonPath('data', null)
        ->assertJsonPath('message', 'Project deleted successfully.');

    expect(Project::find($project->id))->toBeNull();
});

it('returns 403 when deleting another user project', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->withToken(JWTAuth::fromUser($other))
        ->deleteJson("/api/v1/projects/{$project->id}")
        ->assertStatus(403);

    expect(Project::find($project->id))->not->toBeNull();
});

it('returns 404 for a non-existent project', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->deleteJson('/api/v1/projects/99999')
        ->assertStatus(404);
});

it('returns 401 when unauthenticated', function () {
    $project = Project::factory()->create();

    $this->deleteJson("/api/v1/projects/{$project->id}")
        ->assertStatus(401);
});
