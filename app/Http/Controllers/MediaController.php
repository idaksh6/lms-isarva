<?php

namespace App\Http\Controllers;

use App\Models\AssignmentAttachment;
use App\Models\Submission;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    public function assignmentAttachment(AssignmentAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment->assignment);

        return $this->inlineFromDisk($attachment->path, $attachment->name, $attachment->mime);
    }

    public function submission(Submission $submission): StreamedResponse
    {
        $this->authorize('view', $submission);

        abort_unless($submission->file_path && Storage::disk('public')->exists($submission->file_path), 404);

        $mime = Storage::disk('public')->mimeType($submission->file_path) ?: null;

        return $this->inlineFromDisk($submission->file_path, $submission->file_name, $mime);
    }

    public function downloadAssignmentAttachment(AssignmentAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment->assignment);

        return Storage::disk('public')->download($attachment->path, $attachment->name);
    }

    public function downloadSubmission(Submission $submission): StreamedResponse
    {
        $this->authorize('view', $submission);

        abort_unless($submission->file_path && Storage::disk('public')->exists($submission->file_path), 404);

        return Storage::disk('public')->download($submission->file_path, $submission->file_name);
    }

    private function inlineFromDisk(string $path, string $name, ?string $mime): StreamedResponse
    {
        abort_unless(Storage::disk('public')->exists($path), 404);

        $mime ??= Storage::disk('public')->mimeType($path) ?: 'application/octet-stream';

        return Storage::disk('public')->response($path, $name, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.addslashes($name).'"',
        ]);
    }
}
