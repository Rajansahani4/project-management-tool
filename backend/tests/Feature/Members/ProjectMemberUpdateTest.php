<?php

use App\Enums\ProjectRoleEnum;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('updates a member role from member to admin', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $member  = User::factory()->create();

    $projectMember = ProjectMember::factory()->member()->create([
        'project_id' => $project->id,
        'user_id'    => $member->id,
    ]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->patchJson("/api/v1/projects/{$project->id}/members/{$projectMember->id}", [
            'role' => ProjectRoleEnum::Admin->value,
        ])
        ->assertOk()
        ->assertJsonPath('data.role', ProjectRoleEnum::Admin->value)
        ->assertJsonPath('message', 'Member role updated.');
});

it('returns 422 when attempting to assign owner role', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $member  = User::factory()->create();

    $projectMember = ProjectMember::factory()->member()->create([
        'project_id' => $project->id,
        'user_id'    => $member->id,
    ]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->patchJson("/api/v1/projects/{$project->id}/members/{$projectMember->id}", [
            'role' => ProjectRoleEnum::Owner->value,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('returns 422 when trying to change the owner member role', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $ownerMember = ProjectMember::factory()->owner()->create([
        'project_id' => $project->id,
        'user_id'    => $owner->id,
    ]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->patchJson("/api/v1/projects/{$project->id}/members/{$ownerMember->id}", [
            'role' => ProjectRoleEnum::Admin->value,
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('returns 422 when role field is missing', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $member  = User::factory()->create();

    $projectMember = ProjectMember::factory()->member()->create([
        'project_id' => $project->id,
        'user_id'    => $member->id,
    ]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->patchJson("/api/v1/projects/{$project->id}/members/{$projectMember->id}", [])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['role']);
});

it('returns 403 for a non-owner user', function () {
    $owner   = User::factory()->create();
    $other   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $member  = User::factory()->create();

    $projectMember = ProjectMember::factory()->member()->create([
        'project_id' => $project->id,
        'user_id'    => $member->id,
    ]);

    $this->withToken(JWTAuth::fromUser($other))
        ->patchJson("/api/v1/projects/{$project->id}/members/{$projectMember->id}", [
            'role' => ProjectRoleEnum::Admin->value,
        ])
        ->assertStatus(403);
});

it('returns 401 when unauthenticated', function () {
    $project       = Project::factory()->create();
    $projectMember = ProjectMember::factory()->member()->create(['project_id' => $project->id]);

    $this->patchJson("/api/v1/projects/{$project->id}/members/{$projectMember->id}", [
        'role' => ProjectRoleEnum::Admin->value,
    ])
        ->assertStatus(401);
});
