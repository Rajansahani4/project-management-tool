<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'project_id'  => $this->project_id,
            'title'       => $this->title,
            'description' => $this->description,
            'status'      => $this->status->value,
            'priority'    => $this->priority->value,
            'due_date'    => $this->due_date?->toDateString(),
            'assigned_to' => $this->assigned_to,
            'assignee'    => UserResource::make($this->whenLoaded('assignee')),
            'status_logs' => TaskStatusLogResource::collection($this->whenLoaded('statusLogs')),
            'comments'     => CommentResource::collection($this->whenLoaded('comments')),
            'attachments'  => AttachmentResource::collection($this->whenLoaded('attachments')),
            'deleted_at'  => $this->deleted_at,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
        ];
    }
}
