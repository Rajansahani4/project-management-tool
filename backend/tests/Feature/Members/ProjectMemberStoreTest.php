<?php

use App\Enums\ProjectRoleEnum;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('adds a member to the project by email', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $newUser = User::factory()->create(['email' => 'new@example.com']);

    $this->withToken(JWTAuth::fromUser($owner))
        ->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => 'new@example.com',
            'role'  => ProjectRoleEnum::Member->value,
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.user_id', $newUser->id)
        ->assertJsonPath('data.role', ProjectRoleEnum::Member->value)
        ->assertJsonPath('message', 'Member added successfully.');

    expect(ProjectMember::where('project_id', $project->id)->where('user_id', $newUser->id)->exists())->toBeTrue();
});

it('adds a member with admin role', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $newUser = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($owner))
        ->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => $newUser->email,
            'role'  => ProjectRoleEnum::Admin->value,
        ])
        ->assertStatus(201)
        ->assertJsonPath('data.role', ProjectRoleEnum::Admin->value);
});

it('returns 422 when email does not exist in the system', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => 'nobody@example.com',
            'role'  => ProjectRoleEnum::Member->value,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when user is already a member', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $member  = User::factory()->create();

    ProjectMember::factory()->member()->create([
        'project_id' => $project->id,
        'user_id'    => $member->id,
    ]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => $member->email,
            'role'  => ProjectRoleEnum::Member->value,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

it('returns 422 when role is owner', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $newUser = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($owner))
        ->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => $newUser->email,
            'role'  => ProjectRoleEnum::Owner->value,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('returns 422 when required fields are missing', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->postJson("/api/v1/projects/{$project->id}/members", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['email', 'role']);
});

it('returns 403 for a non-owner user', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $target  = User::factory()->create();

    $this->withToken(JWTAuth::fromUser($other))
        ->postJson("/api/v1/projects/{$project->id}/members", [
            'email' => $target->email,
            'role'  => ProjectRoleEnum::Member->value,
        ])
        ->assertStatus(403);
});

it('returns 401 when unauthenticated', function () {
    $project = Project::factory()->create();
    $target  = User::factory()->create();

    $this->postJson("/api/v1/projects/{$project->id}/members", [
        'email' => $target->email,
        'role'  => ProjectRoleEnum::Member->value,
    ])
        ->assertStatus(401);
});
