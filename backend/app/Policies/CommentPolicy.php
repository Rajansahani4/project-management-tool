<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;

class CommentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return ProjectMember::where('project_id', $task->project_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->user_id === $user->id;
    }
}
