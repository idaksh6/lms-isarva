<?php

namespace App\Services\Ai\ContextBuilders;

use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\StudentSupportCase;
use App\Models\Submission;
use App\Support\WeakStudentReport;
use Illuminate\Support\Collection;

class AtRiskContextBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(StudentSupportCase $case): array
    {
        $case->loadMissing(['course.materials', 'student', 'actions']);
        $metrics = WeakStudentReport::metricsForStudent($case->course, $case->student);
        $materials = $case->course->materials()
            ->latest()
            ->limit(5)
            ->get(['id', 'title', 'description', 'category']);

        $recentSubmission = Submission::query()
            ->where('user_id', $case->student->id)
            ->whereHas('assignment', fn ($q) => $q->where('course_id', $case->course_id))
            ->latest('updated_at')
            ->first();

        return [
            'course' => [
                'code' => $case->course->code,
                'title' => $case->course->title,
            ],
            'student' => [
                'name' => $case->student->name,
                'student_id' => $case->student->student_id,
            ],
            'reasons' => $case->reasons ?? [],
            'baseline_metrics' => $case->baseline_metrics ?? [],
            'latest_metrics' => $metrics,
            'materials' => $materials->map(fn (CourseMaterial $m) => [
                'title' => $m->title,
                'description' => $m->description,
                'category' => $m->category?->value ?? $m->category,
            ])->all(),
            'recent_submission' => $recentSubmission ? [
                'assignment' => $recentSubmission->assignment?->title,
                'status' => $recentSubmission->status?->value ?? $recentSubmission->status,
                'score' => $recentSubmission->score,
                'feedback' => $recentSubmission->feedback,
            ] : null,
        ];
    }
}
