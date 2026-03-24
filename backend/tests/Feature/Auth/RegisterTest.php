<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('registers a new user and returns a JWT token', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Jane Doe',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'data' => ['user' => ['id', 'name', 'email', 'created_at'], 'token', 'token_type', 'expires_in'],
            'message',
        ])
        ->assertJsonPath('data.token_type', 'bearer')
        ->assertJsonPath('message', 'User registered successfully.');

    expect(User::where('email', 'jane@example.com')->exists())->toBeTrue();
});

it('hashes the user password on registration', function () {
    $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Jane Doe',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $user = User::where('email', 'jane@example.com')->first();

    expect($user->password)->not->toBe('password123');
    expect(password_verify('password123', $user->password))->toBeTrue();
});

it('returns 422 when name is missing', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

it('returns 422 when email is invalid', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Jane Doe',
        'email'                 => 'not-an-email',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when email is already taken', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Jane Doe',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when password is too short', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Jane Doe',
        'email'                 => 'jane@example.com',
        'password'              => 'short',
        'password_confirmation' => 'short',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('returns 422 when password confirmation does not match', function () {
    $response = $this->postJson('/api/v1/auth/register', [
        'name'                  => 'Jane Doe',
        'email'                 => 'jane@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'different456',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('returns 422 when all fields are missing', function () {
    $response = $this->postJson('/api/v1/auth/register', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name', 'email', 'password']);
});
