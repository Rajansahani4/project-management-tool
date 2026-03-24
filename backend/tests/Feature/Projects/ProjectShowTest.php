<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('returns a project owned by the authenticated user', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->getJson("/api/v1/projects/{$project->id}");

    $response->assertOk()
        ->assertJsonStructure(['data' => ['id', 'name', 'description', 'status', 'due_date'], 'message'])
        ->assertJsonPath('data.id', $project->id)
        ->assertJsonPath('data.name', $project->name)
        ->assertJsonPath('message', 'Project retrieved successfully.');
});

it('returns 403 when accessing another user project', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->withToken(JWTAuth::fromUser($other))
        ->getJson("/api/v1/projects/{$project->id}")
        ->assertStatus(403);
});

it('returns 404 for a non-existent project', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->getJson('/api/v1/projects/99999')
        ->assertStatus(404);
});

it('returns 401 when unauthenticated', function () {
    $project = Project::factory()->create();

    $this->getJson("/api/v1/projects/{$project->id}")
        ->assertStatus(401);
});
