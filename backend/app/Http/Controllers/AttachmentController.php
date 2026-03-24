<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\Project;
use App\Models\Task;
use App\Services\AttachmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function __construct(private readonly AttachmentService $attachmentService) {}

    public function store(StoreAttachmentRequest $request, Project $project, Task $task): JsonResponse
    {
        $this->authorize('create', [Attachment::class, $task]);

        $attachment = $this->attachmentService->uploadFile(
            task: $task,
            uploader: $request->user(),
            file: $request->file('file'),
        );

        return response()->json([
            'data'    => AttachmentResource::make($attachment),
            'message' => 'File uploaded successfully.',
            'status'  => true,
        ], 201);
    }

    public function destroy(Request $request, Project $project, Task $task, Attachment $attachment): JsonResponse
    {
        $this->authorize('delete', $attachment);

        $this->attachmentService->deleteFile($attachment);

        return response()->json([
            'data'    => null,
            'message' => 'Attachment deleted successfully.',
            'status'  => true,
        ]);
    }

    public function download(Request $request, Attachment $attachment): StreamedResponse
    {
        return $this->attachmentService->streamDownload($attachment);
    }
}
