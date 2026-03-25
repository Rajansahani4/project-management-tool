<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('updates the authenticated user profile name', function () {
    $user = User::factory()->create(['name' => 'Old Name']);

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/v1/auth/profile', ['name' => 'New Name'])
        ->assertOk()
        ->assertJsonPath('data.name', 'New Name')
        ->assertJsonPath('data.email', $user->email)
        ->assertJsonPath('message', 'Profile updated successfully.');

    expect($user->fresh()->name)->toBe('New Name');
});

it('returns 422 when name is missing', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/v1/auth/profile', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('returns 422 when name exceeds 255 characters', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->patchJson('/api/v1/auth/profile', ['name' => str_repeat('a', 256)])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('returns 401 when unauthenticated', function () {
    $this->patchJson('/api/v1/auth/profile', ['name' => 'New Name'])
        ->assertStatus(401);
});
