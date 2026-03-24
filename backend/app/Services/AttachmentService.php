<?php

namespace App\Services;

use App\Events\AttachmentUploaded;
use App\Models\Attachment;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class AttachmentService
{
    public function uploadFile(Task $task, User $uploader, UploadedFile $file): Attachment
    {
        $filename  = $file->getClientOriginalName();
        $storedAs  = Str::uuid() . '_' . $filename;
        $path      = $file->storeAs("attachments/{$task->id}", $storedAs, 'local');

        $attachment = Attachment::create([
            'task_id'   => $task->id,
            'user_id'   => $uploader->id,
            'filename'  => $filename,
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType() ?? $file->getClientMimeType(),
        ]);

        $attachment->load('user');

        broadcast(new AttachmentUploaded($attachment))->toOthers();

        return $attachment;
    }

    public function deleteFile(Attachment $attachment): void
    {
        Storage::disk('local')->delete($attachment->file_path);

        $attachment->delete();
    }

    public function generateDownloadUrl(Attachment $attachment): string
    {
        return URL::temporarySignedRoute(
            'attachments.download',
            now()->addHour(),
            ['attachment' => $attachment->id],
        );
    }

    public function streamDownload(Attachment $attachment): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download($attachment->file_path, $attachment->filename);
    }
}
