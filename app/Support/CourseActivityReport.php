<?php

namespace App\Support;

use App\Enums\AssessmentType;
use App\Enums\SessionDeliveryMode;
use App\Enums\SubmissionStatus;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;

class CourseActivityReport
{
    /**
     * @return array{
     *     kpis: array<string, mixed>,
     *     sessions: Collection,
     *     assignments: Collection,
     *     assessments: Collection,
     *     participation: Collection,
     *     notes: list<string>
     * }
     */
    public static function build(Course $course): array
    {
        $course->loadMissing('lecturer');

        $students = $course->students()->orderBy('name')->get();
        $enrolled = $students->count();

        $sessions = $course->classSessions()->orderBy('starts_at')->get();
        $now = now();
        $sessionsPast = $sessions->filter(fn ($s) => $s->starts_at->lte($now));
        $sessionsUpcoming = $sessions->filter(fn ($s) => $s->starts_at->gt($now));
        $sessionsOnline = $sessions->filter(fn ($s) => $s->mode === SessionDeliveryMode::Online)->count();
        $sessionsOffline = $sessions->filter(fn ($s) => $s->mode === SessionDeliveryMode::Offline)->count();

        $assignments = $course->assignments()
            ->where('is_published', true)
            ->orderBy('due_at')
            ->orderBy('title')
            ->get();

        $assignmentIds = $assignments->pluck('id');
        $submissions = Submission::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->whereIn('user_id', $students->pluck('id'))
            ->get();

        $assignmentSummaries = $assignments->map(function (Assignment $assignment) use ($submissions, $enrolled) {
            $rows = $submissions->where('assignment_id', $assignment->id);
            $submitted = $rows->count();
            $graded = $rows->whereNotNull('score');
            $late = $rows->where('status', SubmissionStatus::Late)->count();
            $reviewed = $rows->where('status', SubmissionStatus::Reviewed)->count();

            return [
                'assignment' => $assignment,
                'submitted' => $submitted,
                'not_submitted' => max(0, $enrolled - $submitted),
                'late' => $late,
                'reviewed' => $reviewed,
                'avg_score' => $graded->isNotEmpty() ? round((float) $graded->avg('score'), 1) : null,
                'submission_rate' => $enrolled > 0 ? round(($submitted / $enrolled) * 100, 1) : null,
            ];
        });

        $assessments = $course->assessments()
            ->where('is_published', true)
            ->orderBy('due_at')
            ->orderBy('title')
            ->get();

        $assessmentIds = $assessments->pluck('id');
        $attempts = AssessmentAttempt::query()
            ->whereIn('assessment_id', $assessmentIds)
            ->whereIn('user_id', $students->pluck('id'))
            ->whereNotNull('submitted_at')
            ->get();

        $assessmentSummaries = $assessments->map(function (Assessment $assessment) use ($attempts, $enrolled) {
            $rows = $attempts->where('assessment_id', $assessment->id);
            $completed = $rows->count();
            $avgScore = null;

            if ($rows->isNotEmpty()) {
                $pct = $rows->map(function (AssessmentAttempt $attempt) {
                    if (! $attempt->max_score) {
                        return null;
                    }

                    return ($attempt->score / $attempt->max_score) * 100;
                })->filter(fn ($v) => $v !== null);

                $avgScore = $pct->isNotEmpty() ? round($pct->avg(), 1) : null;
            }

            return [
                'assessment' => $assessment,
                'type_label' => ($assessment->type ?? AssessmentType::Manual)->label(),
                'completed' => $completed,
                'not_completed' => max(0, $enrolled - $completed),
                'completion_rate' => $enrolled > 0 ? round(($completed / $enrolled) * 100, 1) : null,
                'avg_score' => $avgScore,
            ];
        });

        $questions = Question::query()
            ->where('course_id', $course->id)
            ->with('answers')
            ->get();

        $questionsByUser = $questions->groupBy('user_id');
        $answersByUser = $questions->flatMap(fn ($q) => $q->answers)->groupBy('user_id');

        $submissionsByUser = $submissions->groupBy('user_id');
        $attemptsByUser = $attempts->groupBy('user_id');

        $publishedAssignmentCount = $assignments->count();
        $publishedAssessmentCount = $assessments->count();

        $participation = $students->map(function (User $student) use (
            $submissionsByUser,
            $attemptsByUser,
            $questionsByUser,
            $answersByUser,
            $publishedAssignmentCount,
            $publishedAssessmentCount
        ) {
            $studentSubs = $submissionsByUser->get($student->id, collect());
            $studentAttempts = $attemptsByUser->get($student->id, collect());
            $studentQuestions = $questionsByUser->get($student->id, collect());
            $studentAnswers = $answersByUser->get($student->id, collect());

            $assignmentSubmitted = $studentSubs->count();
            $quizCompleted = $studentAttempts->count();
            $qaPosts = $studentQuestions->count() + $studentAnswers->count();

            $expected = $publishedAssignmentCount + $publishedAssessmentCount;
            $done = $assignmentSubmitted + $quizCompleted;
            $participationRate = $expected > 0 ? round(($done / $expected) * 100, 1) : null;

            return [
                'student' => $student,
                'assignments_submitted' => $assignmentSubmitted,
                'assignments_total' => $publishedAssignmentCount,
                'quizzes_completed' => $quizCompleted,
                'quizzes_total' => $publishedAssessmentCount,
                'questions_asked' => $studentQuestions->count(),
                'answers_posted' => $studentAnswers->count(),
                'participation_rate' => $participationRate,
            ];
        })->sortByDesc('participation_rate')->values();

        $materialsCount = $course->materials()->count();
        $announcementsCount = $course->announcements()->count();

        $submissionRate = ($enrolled > 0 && $publishedAssignmentCount > 0)
            ? round(($submissions->count() / ($enrolled * $publishedAssignmentCount)) * 100, 1)
            : null;

        $quizCompletionRate = ($enrolled > 0 && $publishedAssessmentCount > 0)
            ? round(($attempts->count() / ($enrolled * $publishedAssessmentCount)) * 100, 1)
            : null;

        $gradedScores = $submissions->whereNotNull('score')->pluck('score');
        $activeParticipants = $participation->filter(fn ($row) => ($row['assignments_submitted'] + $row['quizzes_completed'] + $row['questions_asked'] + $row['answers_posted']) > 0)->count();

        $kpis = [
            'enrolled' => $enrolled,
            'sessions_total' => $sessions->count(),
            'sessions_past' => $sessionsPast->count(),
            'sessions_upcoming' => $sessionsUpcoming->count(),
            'sessions_online' => $sessionsOnline,
            'sessions_offline' => $sessionsOffline,
            'assignments_published' => $publishedAssignmentCount,
            'submissions' => $submissions->count(),
            'submission_rate' => $submissionRate,
            'avg_assignment_score' => $gradedScores->isNotEmpty() ? round((float) $gradedScores->avg(), 1) : null,
            'assessments_published' => $publishedAssessmentCount,
            'quiz_attempts' => $attempts->count(),
            'quiz_completion_rate' => $quizCompletionRate,
            'questions' => $questions->count(),
            'questions_resolved' => $questions->where('is_resolved', true)->count(),
            'materials' => $materialsCount,
            'announcements' => $announcementsCount,
            'active_participants' => $activeParticipants,
            'participation_rate' => $enrolled > 0 ? round(($activeParticipants / $enrolled) * 100, 1) : null,
        ];

        $notes = [];

        return [
            'kpis' => $kpis,
            'sessions' => $sessions,
            'assignments' => $assignmentSummaries,
            'assessments' => $assessmentSummaries,
            'participation' => $participation,
            'notes' => $notes,
        ];
    }
}
