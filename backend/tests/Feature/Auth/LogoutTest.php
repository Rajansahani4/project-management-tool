<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('logs out an authenticated user and invalidates the token', function () {
    $user  = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withToken($token)->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJsonPath('message', 'Logged out successfully.')
        ->assertJsonPath('data', null);
});

it('returns 401 when logging out without a token', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
});

it('returns null data on logout', function () {
    $user  = User::factory()->create();
    $token = JWTAuth::fromUser($user);

    $response = $this->withToken($token)->postJson('/api/v1/auth/logout');

    $response->assertOk()
        ->assertJsonStructure(['data', 'message'])
        ->assertJsonPath('data', null);
});
