<?php

namespace App\Events;

use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AttachmentUploaded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly Attachment $attachment) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("task.{$this->attachment->task_id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'attachment' => AttachmentResource::make($this->attachment->loadMissing('user'))->resolve(),
        ];
    }
}
