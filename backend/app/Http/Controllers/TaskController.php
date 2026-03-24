<?php

namespace App\Http\Controllers;

use App\Enums\TaskStatus;
use App\Http\Requests\AssignTaskRequest;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Requests\UpdateTaskStatusRequest;
use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class TaskController extends Controller
{
    public function __construct(private readonly TaskService $taskService) {}

    public function index(Request $request, Project $project): AnonymousResourceCollection
    {
        $this->authorize('viewAny', [Task::class, $project]);

        $query = $project->tasks()->with('assignee');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->string('priority'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->integer('assigned_to'));
        }

        return TaskResource::collection($query->latest()->paginate(15));
    }

    public function show(Request $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('view', $task);

        $task->load(['assignee', 'statusLogs.changedBy']);

        return response()->json([
            'data'    => TaskResource::make($task),
            'message' => 'Task retrieved successfully.',
        ]);
    }

    public function store(StoreTaskRequest $request, Project $project): JsonResponse
    {
        $this->authorize('create', [Task::class, $project]);

        $task = $this->taskService->createTask($project, $request->validated());

        return response()->json([
            'data'    => TaskResource::make($task),
            'message' => 'Task created successfully.',
        ], 201);
    }

    public function update(UpdateTaskRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('update', $task);

        $task = $this->taskService->updateTask($task, $request->validated());

        return response()->json([
            'data'    => TaskResource::make($task),
            'message' => 'Task updated successfully.',
        ]);
    }

    public function destroy(Request $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('delete', $task);

        $task->delete();

        return response()->json([
            'data'    => null,
            'message' => 'Task deleted successfully.',
        ]);
    }

    public function updateStatus(UpdateTaskStatusRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('updateStatus', $task);

        $task = $this->taskService->updateTaskStatus(
            task: $task,
            newStatus: TaskStatus::from($request->string('status')->toString()),
            changedBy: $request->user(),
        );

        return response()->json([
            'data'    => TaskResource::make($task),
            'message' => 'Task status updated successfully.',
        ]);
    }

    public function assign(AssignTaskRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('assign', $task);

        $task = $this->taskService->assignTask($task, $request->integer('user_id') ?: null);

        return response()->json([
            'data'    => TaskResource::make($task),
            'message' => 'Task assigned successfully.',
        ]);
    }

    public function restore(Request $request, Project $project, int $taskId): JsonResponse
    {
        /** @var Task $task */
        $task = Task::withTrashed()
            ->where('project_id', $project->id)
            ->findOrFail($taskId);

        $this->authorize('restore', $task);

        $task->restore();

        return response()->json([
            'data'    => TaskResource::make($task->fresh()),
            'message' => 'Task restored successfully.',
        ]);
    }
}
