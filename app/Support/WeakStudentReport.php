<?php

namespace App\Support;

use App\Enums\SubmissionStatus;
use App\Enums\SupportCaseStatus;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\StudentSupportCase;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;

class WeakStudentReport
{
    public const LOW_AVG_THRESHOLD = 60;

    public const MISSING_CRITICAL = 2;

    public const LATE_THRESHOLD = 2;

    public const REVISION_DAYS = 7;

    public const LOW_QUIZ_THRESHOLD = 60;

    public const LOW_PARTICIPATION_THRESHOLD = 40;

    /**
     * @return array{
     *     flagged: Collection<int, array<string, mixed>>,
     *     kpis: array<string, mixed>,
     *     course_avg: float|null,
     *     notes: list<string>
     * }
     */
    public static function build(Course $course): array
    {
        $course->loadMissing('lecturer');

        $students = $course->students()->orderBy('name')->get();
        $profiles = self::profiles($course, $students);
        $courseAvg = self::courseAverage($profiles);

        $activeCases = StudentSupportCase::query()
            ->where('course_id', $course->id)
            ->whereIn('status', [SupportCaseStatus::Open->value, SupportCaseStatus::InProgress->value])
            ->get()
            ->keyBy('user_id');

        $resolvedCases = StudentSupportCase::query()
            ->where('course_id', $course->id)
            ->where('status', SupportCaseStatus::Resolved->value)
            ->count();

        $openCases = $activeCases->count();

        $flagged = $profiles
            ->map(function (array $profile) use ($courseAvg, $activeCases) {
                $evaluation = self::evaluate($profile);
                if (! $evaluation['is_weak']) {
                    return null;
                }

                $case = $activeCases->get($profile['student']->id);

                return [
                    'student' => $profile['student'],
                    'risk_score' => $evaluation['risk_score'],
                    'reasons' => $evaluation['reasons'],
                    'reason_keys' => $evaluation['reason_keys'],
                    'metrics' => self::snapshot($profile, $courseAvg),
                    'active_case' => $case,
                ];
            })
            ->filter()
            ->sortByDesc('risk_score')
            ->values();

        $kpis = [
            'flagged' => $flagged->count(),
            'open_cases' => $openCases,
            'resolved_cases' => $resolvedCases,
            'avg_risk_score' => $flagged->isNotEmpty()
                ? round($flagged->avg('risk_score'), 1)
                : null,
            'enrolled' => $students->count(),
        ];

        return [
            'flagged' => $flagged,
            'kpis' => $kpis,
            'course_avg' => $courseAvg,
            'notes' => [
                'Students are flagged when any critical rule fires, or when two or more non-critical rules fire.',
                'Performance “trends” compare the student to the current course average — historical score history is not stored yet.',
                'Attendance is not tracked in the LMS.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function metricsForStudent(Course $course, User $student): array
    {
        $profiles = self::profiles($course, collect([$student]));
        $profile = $profiles->first();
        $courseProfiles = self::profiles($course, $course->students()->get());
        $courseAvg = self::courseAverage($courseProfiles);

        if (! $profile) {
            return self::snapshot([
                'student' => $student,
                'assignment_avg' => null,
                'graded_count' => 0,
                'published_assignments' => 0,
                'submitted_count' => 0,
                'missing_overdue' => 0,
                'late_count' => 0,
                'stuck_revision' => 0,
                'quiz_avg' => null,
                'low_quiz' => false,
                'participation_rate' => null,
                'has_publishable_work' => false,
            ], $courseAvg);
        }

        return self::snapshot($profile, $courseAvg);
    }

    /**
     * @param  Collection<int, User>  $students
     * @return Collection<int, array<string, mixed>>
     */
    public static function profiles(Course $course, Collection $students): Collection
    {
        if ($students->isEmpty()) {
            return collect();
        }

        $assignments = $course->assignments()
            ->where('is_published', true)
            ->get();

        $assessments = $course->assessments()
            ->where('is_published', true)
            ->get();

        $manualAssessments = $assessments->filter(fn (Assessment $a) => $a->isManual());

        $submissions = Submission::query()
            ->whereIn('assignment_id', $assignments->pluck('id'))
            ->whereIn('user_id', $students->pluck('id'))
            ->get();

        $attempts = AssessmentAttempt::query()
            ->whereIn('assessment_id', $manualAssessments->pluck('id'))
            ->whereIn('user_id', $students->pluck('id'))
            ->whereNotNull('submitted_at')
            ->get();

        $activity = CourseActivityReport::build($course);
        $participationByUser = $activity['participation']->keyBy(fn ($row) => $row['student']->id);

        $now = now();
        $overdueAssignments = $assignments->filter(
            fn (Assignment $a) => $a->due_at !== null && $a->due_at->lt($now)
        );

        $hasPublishableWork = $assignments->isNotEmpty() || $assessments->isNotEmpty();

        return $students->map(function (User $student) use (
            $assignments,
            $submissions,
            $attempts,
            $overdueAssignments,
            $participationByUser,
            $hasPublishableWork,
            $manualAssessments
        ) {
            $studentSubs = $submissions->where('user_id', $student->id);
            $graded = $studentSubs->whereNotNull('score');
            $assignmentAvg = $graded->isNotEmpty() ? round((float) $graded->avg('score'), 1) : null;

            $submittedAssignmentIds = $studentSubs->pluck('assignment_id')->unique();
            $missingOverdue = $overdueAssignments
                ->filter(fn (Assignment $a) => ! $submittedAssignmentIds->contains($a->id))
                ->count();

            $lateCount = $studentSubs->where('status', SubmissionStatus::Late)->count();
            $stuckRevision = $studentSubs
                ->filter(function (Submission $s) {
                    return $s->status === SubmissionStatus::NeedsRevision
                        && $s->reviewed_at
                        && $s->reviewed_at->lte(now()->subDays(self::REVISION_DAYS));
                })
                ->count();

            $studentAttempts = $attempts->where('user_id', $student->id);
            $quizPcts = $studentAttempts->map(function (AssessmentAttempt $attempt) {
                if (! $attempt->max_score) {
                    return null;
                }

                return ($attempt->score / $attempt->max_score) * 100;
            })->filter(fn ($v) => $v !== null);

            $quizAvg = $quizPcts->isNotEmpty() ? round($quizPcts->avg(), 1) : null;
            $lowQuiz = $quizPcts->contains(fn ($pct) => $pct < self::LOW_QUIZ_THRESHOLD);

            $participation = $participationByUser->get($student->id);
            $participationRate = $participation['participation_rate'] ?? null;

            return [
                'student' => $student,
                'assignment_avg' => $assignmentAvg,
                'graded_count' => $graded->count(),
                'published_assignments' => $assignments->count(),
                'submitted_count' => $studentSubs->count(),
                'missing_overdue' => $missingOverdue,
                'late_count' => $lateCount,
                'stuck_revision' => $stuckRevision,
                'quiz_avg' => $quizAvg,
                'low_quiz' => $lowQuiz,
                'manual_quiz_count' => $manualAssessments->count(),
                'participation_rate' => $participationRate,
                'has_publishable_work' => $hasPublishableWork,
            ];
        });
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array{is_weak: bool, risk_score: int, reasons: list<string>, reason_keys: list<string>, critical: bool}
     */
    public static function evaluate(array $profile): array
    {
        $reasons = [];
        $keys = [];
        $critical = false;

        if ($profile['graded_count'] >= 1 && $profile['assignment_avg'] !== null && $profile['assignment_avg'] < self::LOW_AVG_THRESHOLD) {
            $keys[] = 'low_avg';
            $reasons[] = 'Low assignment average '.$profile['assignment_avg'].'% (below '.self::LOW_AVG_THRESHOLD.'%)';
            $critical = true;
        }

        if ($profile['missing_overdue'] >= self::MISSING_CRITICAL) {
            $keys[] = 'missing_work';
            $reasons[] = 'Missing '.$profile['missing_overdue'].' overdue assignments';
            $critical = true;
        }

        if ($profile['late_count'] >= self::LATE_THRESHOLD) {
            $keys[] = 'chronic_late';
            $reasons[] = 'Chronic late submissions ('.$profile['late_count'].')';
        }

        if ($profile['stuck_revision'] >= 1) {
            $keys[] = 'stuck_revision';
            $reasons[] = 'Stuck in needs-revision for over '.self::REVISION_DAYS.' days';
        }

        if ($profile['low_quiz']) {
            $keys[] = 'low_quiz';
            $reasons[] = 'Low quiz score (below '.self::LOW_QUIZ_THRESHOLD.'%)';
        }

        if (
            $profile['has_publishable_work']
            && $profile['participation_rate'] !== null
            && $profile['participation_rate'] < self::LOW_PARTICIPATION_THRESHOLD
        ) {
            $keys[] = 'low_participation';
            $reasons[] = 'Low participation '.$profile['participation_rate'].'% (below '.self::LOW_PARTICIPATION_THRESHOLD.'%)';
        }

        $riskScore = count($keys);
        $isWeak = $critical || $riskScore >= 2;

        return [
            'is_weak' => $isWeak,
            'risk_score' => $riskScore,
            'reasons' => $reasons,
            'reason_keys' => $keys,
            'critical' => $critical,
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    public static function snapshot(array $profile, ?float $courseAvg): array
    {
        return [
            'assignment_avg' => $profile['assignment_avg'],
            'course_avg' => $courseAvg,
            'avg_delta' => ($profile['assignment_avg'] !== null && $courseAvg !== null)
                ? round($profile['assignment_avg'] - $courseAvg, 1)
                : null,
            'submitted' => $profile['submitted_count'],
            'published_assignments' => $profile['published_assignments'],
            'missing_overdue' => $profile['missing_overdue'],
            'late_count' => $profile['late_count'],
            'stuck_revision' => $profile['stuck_revision'],
            'quiz_avg' => $profile['quiz_avg'],
            'participation_rate' => $profile['participation_rate'],
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $profiles
     */
    private static function courseAverage(Collection $profiles): ?float
    {
        $avgs = $profiles->pluck('assignment_avg')->filter(fn ($v) => $v !== null);
        if ($avgs->isEmpty()) {
            return null;
        }

        return round((float) $avgs->avg(), 1);
    }
}
