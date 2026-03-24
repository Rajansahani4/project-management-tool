<?php

namespace App\Http\Resources;

use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'task_id'      => $this->task_id,
            'user_id'      => $this->user_id,
            'filename'     => $this->filename,
            'file_size'    => $this->file_size,
            'mime_type'    => $this->mime_type,
            'download_url' => app(AttachmentService::class)->generateDownloadUrl($this->resource),
            'user'         => UserResource::make($this->whenLoaded('user')),
            'created_at'   => $this->created_at,
            'updated_at'   => $this->updated_at,
        ];
    }
}
