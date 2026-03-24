<?php

namespace App\Policies;

use App\Models\Attachment;
use App\Models\ProjectMember;
use App\Models\Task;
use App\Models\User;

class AttachmentPolicy
{
    public function create(User $user, Task $task): bool
    {
        return ProjectMember::where('project_id', $task->project_id)
            ->where('user_id', $user->id)
            ->exists();
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        return $attachment->user_id === $user->id;
    }
}
