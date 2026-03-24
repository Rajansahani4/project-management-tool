<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\UpdateCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Project;
use App\Models\Task;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $commentService) {}

    public function store(StoreCommentRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('create', [Comment::class, $task]);

        $comment = $this->commentService->addComment(
            task: $task,
            author: $request->user(),
            content: $request->string('content')->toString(),
        );

        return response()->json([
            'data'    => CommentResource::make($comment),
            'message' => 'Comment added successfully.',
            'status'  => true,
        ], 201);
    }

    public function update(UpdateCommentRequest $request, Project $project, Task $task, Comment $comment): JsonResponse
    {
        $this->authorize('update', $comment);

        $comment = $this->commentService->updateComment(
            comment: $comment,
            content: $request->string('content')->toString(),
        );

        return response()->json([
            'data'    => CommentResource::make($comment),
            'message' => 'Comment updated successfully.',
            'status'  => true,
        ]);
    }

    public function destroy(Request $request, Project $project, Task $task, Comment $comment): JsonResponse
    {
        $this->authorize('delete', $comment);

        $this->commentService->deleteComment($comment);

        return response()->json([
            'data'    => null,
            'message' => 'Comment deleted successfully.',
            'status'  => true,
        ]);
    }
}
