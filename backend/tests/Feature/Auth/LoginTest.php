<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('logs in with valid credentials and returns a JWT token', function () {
    User::factory()->create([
        'email'    => 'john@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'john@example.com',
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'data' => ['token', 'token_type', 'expires_in'],
            'message',
        ])
        ->assertJsonPath('data.token_type', 'bearer')
        ->assertJsonPath('message', 'Login successful.');
});

it('returns a non-empty token string on login', function () {
    User::factory()->create([
        'email'    => 'john@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'john@example.com',
        'password' => 'secret123',
    ]);

    expect($response->json('data.token'))->toBeString()->not->toBeEmpty();
});

it('returns 401 for wrong password', function () {
    User::factory()->create([
        'email'    => 'john@example.com',
        'password' => Hash::make('secret123'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'john@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Invalid credentials.');
});

it('returns 401 for non-existent user', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'nobody@example.com',
        'password' => 'password123',
    ]);

    $response->assertStatus(401)
        ->assertJsonPath('message', 'Invalid credentials.');
});

it('returns 422 when email is missing', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when password is missing', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'john@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

it('returns 422 when email format is invalid', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'not-an-email',
        'password' => 'password123',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when all fields are missing', function () {
    $response = $this->postJson('/api/v1/auth/login', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'password']);
});
