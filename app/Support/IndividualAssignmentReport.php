<?php

namespace App\Support;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;

class IndividualAssignmentReport
{
    /**
     * @param  array{
     *     status?: string|null,
     *     graded?: string|null,
     *     q?: string|null,
     *     submitted_from?: string|null,
     *     submitted_to?: string|null,
     *     score_min?: float|int|string|null,
     *     score_max?: float|int|string|null,
     * }  $filters
     * @return array{rows: Collection<int, array<string, mixed>>, kpis: array<string, mixed>, all_rows: Collection<int, array<string, mixed>>}
     */
    public static function build(Assignment $assignment, array $filters = []): array
    {
        $assignment->loadMissing(['course.lecturer']);

        $students = $assignment->course->students()
            ->orderBy('name')
            ->get();

        $submissions = Submission::query()
            ->where('assignment_id', $assignment->id)
            ->whereIn('user_id', $students->pluck('id'))
            ->with('reviewer')
            ->get()
            ->keyBy('user_id');

        $allRows = $students->map(fn (User $student) => self::row(
            $assignment,
            $student,
            $submissions->get($student->id)
        ));

        $kpis = self::kpis($allRows);
        $rows = self::applyFilters($allRows, $filters);

        return [
            'rows' => $rows,
            'kpis' => $kpis,
            'all_rows' => $allRows,
        ];
    }

    /**
     * @param  array{
     *     status?: string|null,
     *     graded?: string|null,
     *     q?: string|null,
     *     submitted_from?: string|null,
     *     submitted_to?: string|null,
     *     score_min?: float|int|string|null,
     *     score_max?: float|int|string|null,
     * }  $filters
     * @return array{
     *     sections: Collection<int, array{assignment: Assignment, rows: Collection, kpis: array<string, mixed>}>,
     *     kpis: array<string, mixed>
     * }
     */
    public static function buildForCourse(\App\Models\Course $course, array $filters = []): array
    {
        $course->loadMissing('lecturer');

        $assignments = $course->assignments()
            ->where('is_published', true)
            ->orderBy('due_at')
            ->orderBy('title')
            ->get();

        $allRows = collect();
        $sections = $assignments->map(function (Assignment $assignment) use ($filters, &$allRows) {
            $report = self::build($assignment, $filters);
            $allRows = $allRows->concat($report['all_rows']);

            return [
                'assignment' => $assignment,
                'rows' => $report['rows'],
                'kpis' => $report['kpis'],
            ];
        });

        return [
            'sections' => $sections,
            'kpis' => self::kpis($allRows, uniqueEnrolled: true),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function row(Assignment $assignment, User $student, ?Submission $submission): array
    {
        $statusKey = $submission?->status?->value ?? 'not_submitted';
        $statusLabel = $submission?->status?->label() ?? 'Not submitted';
        $score = $submission?->score !== null ? (float) $submission->score : null;
        $letter = $submission?->letter_grade ?? GradeHelper::letterFromScore($score);

        return [
            'assignment' => $assignment,
            'student' => $student,
            'submission' => $submission,
            'status_key' => $statusKey,
            'status_label' => $statusLabel,
            'submitted_at' => $submission?->submitted_at,
            'days_late' => self::daysLate($assignment, $submission),
            'score' => $score,
            'letter' => $letter,
            'feedback' => $submission?->feedback,
            'reviewed_at' => $submission?->reviewed_at,
            'reviewer_name' => $submission?->reviewer?->name,
            'source_label' => $submission?->source?->label(),
            'file_or_link' => $submission?->displayName(),
            'notes' => $submission?->notes,
            'is_graded' => $score !== null,
            'is_submitted' => $submission !== null,
        ];
    }

    public static function daysLate(Assignment $assignment, ?Submission $submission): ?int
    {
        if (! $submission?->submitted_at || ! $assignment->due_at) {
            return null;
        }

        $due = $assignment->due_at;
        $submitted = $submission->submitted_at;

        if ($submitted->equalTo($due)) {
            return 0;
        }

        $days = (int) $due->diffInDays($submitted);

        return $submitted->greaterThan($due) ? $days : -$days;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array<string, mixed>
     */
    public static function kpis(Collection $rows, bool $uniqueEnrolled = false): array
    {
        $enrolled = $uniqueEnrolled
            ? $rows->pluck('student.id')->unique()->count()
            : $rows->count();
        $submittedRows = $rows->where('is_submitted', true);
        $submitted = $submittedRows->count();
        $notSubmitted = $rows->where('is_submitted', false)->count();

        $late = $rows->where('status_key', SubmissionStatus::Late->value)->count();
        $needsRevision = $rows->where('status_key', SubmissionStatus::NeedsRevision->value)->count();
        $reviewed = $rows->where('status_key', SubmissionStatus::Reviewed->value)->count();

        $scores = $rows->whereNotNull('score')->pluck('score')->map(fn ($s) => (float) $s)->sort()->values();
        $graded = $scores->count();

        $onTime = $submittedRows->filter(function (array $row) {
            /** @var Assignment|null $assignment */
            $assignment = $row['assignment'] ?? null;
            if (! $row['submitted_at'] || ! $assignment?->due_at) {
                return true;
            }

            return $row['submitted_at']->lte($assignment->due_at);
        })->count();

        $totalSlots = $rows->count();

        return [
            'enrolled' => $enrolled,
            'submitted' => $submitted,
            'not_submitted' => $notSubmitted,
            'late' => $late,
            'needs_revision' => $needsRevision,
            'reviewed' => $reviewed,
            'graded' => $graded,
            'submission_rate' => $totalSlots > 0 ? round(($submitted / $totalSlots) * 100, 1) : null,
            'on_time_rate' => $submitted > 0 ? round(($onTime / $submitted) * 100, 1) : null,
            'graded_rate' => $submitted > 0 ? round(($graded / $submitted) * 100, 1) : null,
            'avg_score' => $graded > 0 ? round($scores->avg(), 1) : null,
            'median_score' => $graded > 0 ? self::median($scores) : null,
            'min_score' => $graded > 0 ? round($scores->min(), 1) : null,
            'max_score' => $graded > 0 ? round($scores->max(), 1) : null,
        ];
    }

    /**
     * @param  Collection<int, float>  $sortedScores
     */
    private static function median(Collection $sortedScores): float
    {
        $count = $sortedScores->count();
        $mid = intdiv($count, 2);

        if ($count % 2 === 0) {
            return round(($sortedScores[$mid - 1] + $sortedScores[$mid]) / 2, 1);
        }

        return round((float) $sortedScores[$mid], 1);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array<string, mixed>  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public static function applyFilters(Collection $rows, array $filters): Collection
    {
        $status = filled($filters['status'] ?? null) ? (string) $filters['status'] : null;
        $graded = filled($filters['graded'] ?? null) ? (string) $filters['graded'] : null;
        $q = filled($filters['q'] ?? null) ? mb_strtolower(trim((string) $filters['q'])) : null;
        $from = filled($filters['submitted_from'] ?? null) ? (string) $filters['submitted_from'] : null;
        $to = filled($filters['submitted_to'] ?? null) ? (string) $filters['submitted_to'] : null;
        $scoreMin = is_numeric($filters['score_min'] ?? null) ? (float) $filters['score_min'] : null;
        $scoreMax = is_numeric($filters['score_max'] ?? null) ? (float) $filters['score_max'] : null;

        return $rows
            ->when($status === 'not_submitted', fn ($c) => $c->where('is_submitted', false))
            ->when($status && $status !== 'not_submitted', fn ($c) => $c->where('status_key', $status))
            ->when($graded === 'graded', fn ($c) => $c->where('is_graded', true))
            ->when($graded === 'ungraded', fn ($c) => $c->filter(fn ($row) => $row['is_submitted'] && ! $row['is_graded']))
            ->when($q, function (Collection $c) use ($q) {
                return $c->filter(function (array $row) use ($q) {
                    /** @var User $student */
                    $student = $row['student'];

                    return str_contains(mb_strtolower($student->name), $q)
                        || str_contains(mb_strtolower((string) $student->student_id), $q)
                        || str_contains(mb_strtolower($student->email), $q);
                });
            })
            ->when($from, fn ($c) => $c->filter(fn ($row) => $row['submitted_at'] && $row['submitted_at']->toDateString() >= $from))
            ->when($to, fn ($c) => $c->filter(fn ($row) => $row['submitted_at'] && $row['submitted_at']->toDateString() <= $to))
            ->when($scoreMin !== null, fn ($c) => $c->filter(fn ($row) => $row['score'] !== null && $row['score'] >= $scoreMin))
            ->when($scoreMax !== null, fn ($c) => $c->filter(fn ($row) => $row['score'] !== null && $row['score'] <= $scoreMax))
            ->values();
    }

    /**
     * @return list<string>
     */
    public static function studentExportHeaders(): array
    {
        return [
            'Student Name',
            'Student ID',
            'Email',
            'Status',
            'Submitted At',
            'Days Late',
            'Score %',
            'Letter',
            'Feedback',
            'Reviewed At',
            'Reviewer',
            'Source',
            'File/Link',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string|int|float|null>
     */
    public static function studentExportRow(array $row): array
    {
        $student = $row['student'];

        return [
            $student->name,
            $student->student_id ?? '',
            $student->email,
            $row['status_label'],
            $row['submitted_at']?->format('Y-m-d H:i') ?? '',
            $row['days_late'] ?? '',
            $row['score'] ?? '',
            $row['letter'] ?? '',
            $row['feedback'] ?? '',
            $row['reviewed_at']?->format('Y-m-d H:i') ?? '',
            $row['reviewer_name'] ?? '',
            $row['source_label'] ?? '',
            $row['file_or_link'] ?? '',
        ];
    }

    /**
     * @param  array<string, mixed>  $row
     * @return list<string|int|float|null>
     */
    public static function csvRow(Assignment $assignment, array $row): array
    {
        return array_merge([
            $assignment->course->code,
            $assignment->course->title,
            $assignment->title,
            $assignment->due_at?->format('Y-m-d H:i') ?? '',
        ], self::studentExportRow($row));
    }

    /**
     * @return list<string>
     */
    public static function csvHeaders(): array
    {
        return array_merge([
            'Course Code',
            'Course Title',
            'Assignment',
            'Due At',
        ], self::studentExportHeaders());
    }
}
