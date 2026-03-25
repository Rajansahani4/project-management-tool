<?php

use App\Enums\ProjectRoleEnum;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('returns the member list for the project owner', function () {
    $owner = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $member = User::factory()->create();
    ProjectMember::factory()->member()->create([
        'project_id' => $project->id,
        'user_id'    => $member->id,
    ]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->getJson("/api/v1/projects/{$project->id}/members")
        ->assertOk()
        ->assertJsonStructure(['data' => [['id', 'project_id', 'user_id', 'role', 'user']]]);
});

it('returns 403 for a non-owner user', function () {
    $owner = Project::factory()->create();
    $other = User::factory()->create();
    $project = Project::factory()->create();

    $this->withToken(JWTAuth::fromUser($other))
        ->getJson("/api/v1/projects/{$project->id}/members")
        ->assertStatus(403);
});

it('returns 401 when unauthenticated', function () {
    $project = Project::factory()->create();

    $this->getJson("/api/v1/projects/{$project->id}/members")
        ->assertStatus(401);
});
