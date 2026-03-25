<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddProjectMemberRequest;
use App\Http\Requests\UpdateProjectMemberRequest;
use App\Http\Resources\ProjectMemberResource;
use App\Models\Project;
use App\Models\ProjectMember;
use App\Services\ProjectMemberService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProjectMemberController extends Controller
{
    public function __construct(private readonly ProjectMemberService $memberService) {}

    /**
     * List all members of a project.
     */
    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('view', $project);

        $members = $this->memberService->getMembers($project);

        return ProjectMemberResource::collection($members);
    }

    /**
     * Add a new member to the project by email.
     */
    public function store(AddProjectMemberRequest $request, Project $project): JsonResponse
    {
        $this->authorize('update', $project);

        $member = $this->memberService->addMember(
            project:  $project,
            email:    $request->string('email')->toString(),
            roleName: $request->string('role')->toString(),
        );

        return response()->json([
            'data'    => ProjectMemberResource::make($member),
            'message' => 'Member added successfully.',
        ], 201);
    }

    /**
     * Update a member's role.
     */
    public function update(UpdateProjectMemberRequest $request, Project $project, ProjectMember $member): JsonResponse
    {
        $this->authorize('update', $project);

        $member = $this->memberService->updateRole(
            member:   $member,
            roleName: $request->string('role')->toString(),
        );

        return response()->json([
            'data'    => ProjectMemberResource::make($member),
            'message' => 'Member role updated.',
        ]);
    }

    /**
     * Remove a member from the project.
     */
    public function destroy(Request $request, Project $project, ProjectMember $member): JsonResponse
    {
        $this->authorize('update', $project);

        $this->memberService->removeMember($member);

        return response()->json([
            'data'    => null,
            'message' => 'Member removed.',
        ]);
    }
}
