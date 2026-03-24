<?php

namespace App\Events;

use App\Http\Resources\CommentResource;
use App\Models\Comment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CommentCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Comment $comment) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("task.{$this->comment->task_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'comment' => CommentResource::make($this->comment->loadMissing('user'))->resolve(),
        ];
    }
}
