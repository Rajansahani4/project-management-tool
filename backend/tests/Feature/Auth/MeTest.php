<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('returns the authenticated user profile', function () {
    $user  = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withToken($token)->getJson('/api/v1/auth/me');

    $response->assertOk()
        ->assertJsonStructure(['data' => ['id', 'name', 'email', 'created_at'], 'message'])
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('returns 401 when unauthenticated', function () {
    $response = $this->getJson('/api/v1/auth/me');

    $response->assertStatus(401);
});

it('returns 401 with an invalid token', function () {
    $response = $this->withToken('invalid.token.here')->getJson('/api/v1/auth/me');

    $response->assertStatus(401);
});
