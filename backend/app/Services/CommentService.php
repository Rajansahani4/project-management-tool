<?php

namespace App\Services;

use App\Events\CommentCreated;
use App\Models\Comment;
use App\Models\Task;
use App\Models\User;

class CommentService
{
    public function addComment(Task $task, User $author, string $content): Comment
    {
        $comment = Comment::create([
            'task_id' => $task->id,
            'user_id' => $author->id,
            'content' => $content,
        ]);

        $comment->load('user');

        broadcast(new CommentCreated($comment))->toOthers();

        return $comment;
    }

    public function updateComment(Comment $comment, string $content): Comment
    {
        $comment->update(['content' => $content]);

        return $comment->fresh();
    }

    public function deleteComment(Comment $comment): void
    {
        $comment->delete();
    }
}
