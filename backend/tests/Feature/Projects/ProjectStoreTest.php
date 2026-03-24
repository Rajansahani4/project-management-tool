<?php

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('creates a project and returns 201 with the resource', function () {
    $user = User::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/projects', [
            'name'        => 'New Project',
            'description' => 'A test project',
            'due_date'    => now()->addMonth()->toDateString(),
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['data' => ['id', 'name', 'description', 'status', 'due_date'], 'message'])
        ->assertJsonPath('data.name', 'New Project')
        ->assertJsonPath('data.status', ProjectStatusEnum::Active->value)
        ->assertJsonPath('message', 'Project created successfully.');

    expect(Project::where('name', 'New Project')->where('user_id', $user->id)->exists())->toBeTrue();
});

it('defaults status to active when not provided', function () {
    $user = User::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/projects', ['name' => 'My Project']);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'active');
});

it('creates a project with archived status', function () {
    $user = User::factory()->create();

    $response = $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/projects', [
            'name'   => 'Old Project',
            'status' => 'archived',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.status', 'archived');
});

it('returns 422 when name is missing', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/projects', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('returns 422 when status is invalid', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/projects', [
            'name'   => 'Project',
            'status' => 'invalid-status',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

it('returns 422 when due_date is in the past', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/projects', [
            'name'     => 'Project',
            'due_date' => now()->subDay()->toDateString(),
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['due_date']);
});

it('returns 401 when unauthenticated', function () {
    $this->postJson('/api/v1/projects', ['name' => 'Project'])
        ->assertStatus(401);
});
