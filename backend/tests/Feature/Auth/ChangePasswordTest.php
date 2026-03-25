<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('changes password with correct current password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password1'),
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/auth/change-password', [
            'current_password'      => 'old-password1',
            'password'              => 'new-password1',
            'password_confirmation' => 'new-password1',
        ])
        ->assertOk()
        ->assertJsonPath('message', 'Password changed successfully.')
        ->assertJsonPath('data', null);

    expect(Hash::check('new-password1', $user->fresh()->password))->toBeTrue();
});

it('returns 422 when current_password is wrong', function () {
    $user = User::factory()->create([
        'password' => Hash::make('real-password'),
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/auth/change-password', [
            'current_password'      => 'wrong-password',
            'password'              => 'new-password1',
            'password_confirmation' => 'new-password1',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['current_password']);
});

it('returns 422 when password confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password1'),
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/auth/change-password', [
            'current_password'      => 'old-password1',
            'password'              => 'new-password1',
            'password_confirmation' => 'different-password',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('returns 422 when new password is too short', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old-password1'),
    ]);

    $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/auth/change-password', [
            'current_password'      => 'old-password1',
            'password'              => 'short',
            'password_confirmation' => 'short',
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('returns 422 when required fields are missing', function () {
    $user = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($user))
        ->postJson('/api/v1/auth/change-password', [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['current_password', 'password']);
});

it('returns 401 when unauthenticated', function () {
    $this->postJson('/api/v1/auth/change-password', [
        'current_password'      => 'old-password1',
        'password'              => 'new-password1',
        'password_confirmation' => 'new-password1',
    ])
        ->assertStatus(401);
});
