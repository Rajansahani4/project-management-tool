<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskStatusLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'from_status' => $this->from_status,
            'to_status'   => $this->to_status,
            'changed_by'  => UserResource::make($this->whenLoaded('changedBy')),
            'created_at'  => $this->created_at,
        ];
    }
}
