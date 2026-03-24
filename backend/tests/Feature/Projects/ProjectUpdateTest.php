<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('updates a project and returns the updated resource', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Old Name']);

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->putJson("/api/v1/projects/{$project->id}", [
            'name'   => 'Updated Name',
            'status' => 'archived',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.name', 'Updated Name')
        ->assertJsonPath('data.status', 'archived')
        ->assertJsonPath('message', 'Project updated successfully.');

    expect($project->fresh()->name)->toBe('Updated Name');
});

it('updates only provided fields (partial update)', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->for($user)->create([
        'name'        => 'Original',
        'description' => 'Original description',
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->putJson("/api/v1/projects/{$project->id}", ['name' => 'Changed'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Changed')
        ->assertJsonPath('data.description', 'Original description');
});

it('can clear the description by setting it to null', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->for($user)->create(['description' => 'Some text']);

    $this->withToken(JWTAuth::fromUser($user))
        ->putJson("/api/v1/projects/{$project->id}", ['description' => null])
        ->assertOk()
        ->assertJsonPath('data.description', null);
});

it('returns 403 when updating another user project', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->withToken(JWTAuth::fromUser($other))
        ->putJson("/api/v1/projects/{$project->id}", ['name' => 'Hijack'])
        ->assertStatus(403);
});

it('returns 422 when name is empty string', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->putJson("/api/v1/projects/{$project->id}", ['name' => ''])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('returns 422 when status is invalid', function () {
    $user    = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->putJson("/api/v1/projects/{$project->id}", ['status' => 'unknown'])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('returns 404 for a non-existent project', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->putJson('/api/v1/projects/99999', ['name' => 'X'])
        ->assertStatus(404);
});

it('returns 401 when unauthenticated', function () {
    $project = Project::factory()->create();

    $this->putJson("/api/v1/projects/{$project->id}", ['name' => 'X'])
        ->assertStatus(401);
});
