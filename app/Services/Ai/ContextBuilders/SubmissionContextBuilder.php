<?php

namespace App\Services\Ai\ContextBuilders;

use App\Models\Submission;

class SubmissionContextBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(Submission $submission): array
    {
        $submission->loadMissing(['assignment.course', 'student']);

        return [
            'course' => [
                'code' => $submission->assignment->course->code,
                'title' => $submission->assignment->course->title,
            ],
            'assignment' => [
                'title' => $submission->assignment->title,
                'instructions' => mb_substr((string) $submission->assignment->instructions, 0, 2000),
                'due_at' => $submission->assignment->due_at?->toDateTimeString(),
            ],
            'student' => [
                'name' => $submission->student->name,
                'student_id' => $submission->student->student_id,
            ],
            'submission' => [
                'status' => $submission->status?->value ?? $submission->status,
                'score' => $submission->score,
                'notes' => mb_substr((string) $submission->notes, 0, 1500),
                'feedback' => $submission->feedback,
                'file_name' => $submission->file_name,
                'is_external' => filled($submission->external_url),
            ],
        ];
    }
}
