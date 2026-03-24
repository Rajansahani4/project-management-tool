<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('returns a paginated list of the authenticated user projects', function () {
    $user = User::factory()->create();
    Project::factory(3)->for($user)->create();

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->getJson('/api/v1/projects');

    $response->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonStructure([
            'data' => [['id', 'name', 'description', 'status', 'due_date', 'created_at', 'updated_at']],
            'links',
            'meta',
        ]);
});

it('returns only projects belonging to the authenticated user', function () {
    $user  = User::factory()->create();
    $other = User::factory()->create();

    Project::factory(2)->for($user)->create();
    Project::factory(3)->for($other)->create();

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->getJson('/api/v1/projects');

    $response->assertOk()->assertJsonCount(2, 'data');
});

it('returns an empty list when the user has no projects', function () {
    $user = User::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->getJson('/api/v1/projects');

    $response->assertOk()->assertJsonCount(0, 'data');
});

it('returns 401 when unauthenticated', function () {
    $this->getJson('/api/v1/projects')->assertStatus(401);
});
