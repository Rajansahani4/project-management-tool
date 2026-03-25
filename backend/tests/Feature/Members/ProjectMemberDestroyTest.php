<?php

use App\Enums\ProjectRoleEnum;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

it('removes a member from the project', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);
    $member  = User::factory()->create();

    $projectMember = ProjectMember::factory()->member()->create([
        'project_id' => $project->id,
        'user_id'    => $member->id,
    ]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->deleteJson("/api/v1/projects/{$project->id}/members/{$projectMember->id}")
        ->assertOk()
        ->assertJsonPath('data', null)
        ->assertJsonPath('message', 'Member removed.');

    expect(ProjectMember::find($projectMember->id))->toBeNull();
});

it('returns 422 when attempting to remove the owner', function () {
    $owner   = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $ownerMember = ProjectMember::factory()->owner()->create([
        'project_id' => $project->id,
        'user_id'    => $owner->id,
    ]);

    $this->withToken(JWTAuth::fromUser($owner))
        ->deleteJson("/api/v1/projects/{$project->id}/members/{$ownerMember->id}")
        ->assertStatus(422)
        ->assertJsonValidationErrors(['member']);
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
        ->deleteJson("/api/v1/projects/{$project->id}/members/{$projectMember->id}")
        ->assertStatus(403);
});

it('returns 401 when unauthenticated', function () {
    $project       = Project::factory()->create();
    $projectMember = ProjectMember::factory()->member()->create(['project_id' => $project->id]);

    $this->deleteJson("/api/v1/projects/{$project->id}/members/{$projectMember->id}")
        ->assertStatus(401);
});
