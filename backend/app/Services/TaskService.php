<?php

namespace App\Services;

use App\Enums\TaskStatusEnum;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskStatusLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TaskService
{
    public function createTask(Project $project, array $data): Task
    {
        return $project->tasks()->create($data);
    }

    public function updateTask(Task $task, array $data): Task
    {
        $task->update($data);

        return $task->fresh();
    }

    public function updateTaskStatus(Task $task, TaskStatusEnum $newStatus, User $changedBy): Task
    {
        $fromStatus = $task->status;

        $task->update(['status' => $newStatus]);

        TaskStatusLog::create([
            'task_id'     => $task->id,
            'changed_by'  => $changedBy->id,
            'from_status' => $fromStatus?->value,
            'to_status'   => $newStatus->value,
        ]);

        return $task->fresh();
    }

    public function assignTask(Task $task, ?int $userId): Task
    {
        $task->update(['assigned_to' => $userId]);

        return $task->fresh(['assignee']);
    }

    public function getTasksByStatus(Project $project, TaskStatusEnum $status): Collection
    {
        return $project->tasks()
            ->where('status', $status->value)
            ->with('assignee')
            ->get();
    }
}
