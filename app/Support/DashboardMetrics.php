<?php

namespace App\Support;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Collection;

class DashboardMetrics
{
    public static function percent(int $part, int $total): int
    {
        if ($total <= 0) {
            return 0;
        }

        return (int) round(min(100, ($part / $total) * 100));
    }

    public static function adminStats(): array
    {
        $students = User::query()->where('role', 'student')->count();
        $lecturers = User::query()->where('role', 'lecturer')->count();
        $totalUsers = $students + $lecturers;
        $courses = Course::query()->count();
        $activeCourses = Course::query()->where('is_active', true)->count();
        $assignments = Assignment::query()->count();
        $publishedAssignments = Assignment::query()->where('is_published', true)->count();
        $pendingReviews = Submission::query()->where('status', 'submitted')->count();
        $enrollments = (int) \App\Models\CourseEnrollment::query()->count();

        return [
            'students' => $students,
            'lecturers' => $lecturers,
            'total_users' => $totalUsers,
            'courses' => $courses,
            'active_courses' => $activeCourses,
            'assignments' => $assignments,
            'published_assignments' => $publishedAssignments,
            'pending_reviews' => $pendingReviews,
            'enrollments' => $enrollments,
            'students_pct' => self::percent($students, $totalUsers),
            'lecturers_pct' => self::percent($lecturers, $totalUsers),
            'active_courses_pct' => self::percent($activeCourses, $courses),
            'published_pct' => self::percent($publishedAssignments, $assignments),
        ];
    }

    public static function adminCourseProgress(Course $course): int
    {
        $course->loadCount(['students', 'assignments']);

        $published = Assignment::query()
            ->where('course_id', $course->id)
            ->where('is_published', true)
            ->count();

        if ($published === 0) {
            return $course->students_count > 0 ? 50 : 0;
        }

        $assignmentIds = Assignment::query()
            ->where('course_id', $course->id)
            ->where('is_published', true)
            ->pluck('id');

        $submissions = Submission::query()->whereIn('assignment_id', $assignmentIds)->count();
        $expected = max(1, $published * max(1, $course->students_count));

        return self::percent($submissions, $expected);
    }

    public static function lecturerStats(User $user): array
    {
        $courses = $user->taughtCourses()->count();
        $activeCourses = $user->taughtCourses()->where('is_active', true)->count();
        $pending = Submission::query()
            ->where('status', 'submitted')
            ->whereHas('assignment.course', fn ($q) => $q->where('lecturer_id', $user->id))
            ->count();

        $assignmentIds = Assignment::query()
            ->whereHas('course', fn ($q) => $q->where('lecturer_id', $user->id))
            ->pluck('id');

        $totalSubmissions = Submission::query()->whereIn('assignment_id', $assignmentIds)->count();
        $reviewed = Submission::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->where('status', 'reviewed')
            ->count();

        return [
            'courses' => $courses,
            'active_courses' => $activeCourses,
            'pending_reviews' => $pending,
            'total_submissions' => $totalSubmissions,
            'reviewed' => $reviewed,
            'active_courses_pct' => self::percent($activeCourses, $courses),
            'reviewed_pct' => self::percent($reviewed, $totalSubmissions),
        ];
    }

    public static function lecturerCourseProgress(Course $course): int
    {
        $assignmentIds = $course->assignments()->where('is_published', true)->pluck('id');

        if ($assignmentIds->isEmpty()) {
            return 0;
        }

        $total = Submission::query()->whereIn('assignment_id', $assignmentIds)->count();
        $reviewed = Submission::query()
            ->whereIn('assignment_id', $assignmentIds)
            ->where('status', 'reviewed')
            ->count();

        return self::percent($reviewed, $total);
    }

    public static function studentStats(User $user, Collection $courses, Collection $openAssignments): array
    {
        $assignmentIds = $courses->flatMap(function ($course) {
            return $course->assignments->pluck('id');
        });

        $totalPublished = $assignmentIds->count();
        $submitted = $user->submissions()->whereIn('assignment_id', $assignmentIds)->count();
        $pending = $openAssignments->count();

        return [
            'courses' => $courses->count(),
            'total_assignments' => $totalPublished,
            'submitted' => $submitted,
            'pending' => $pending,
            'completion_pct' => self::percent($submitted, $totalPublished),
            'pending_pct' => self::percent($pending, $totalPublished),
        ];
    }

    public static function studentCourseProgress(User $user, Course $course): int
    {
        $publishedIds = $course->assignments()
            ->where('is_published', true)
            ->pluck('id');

        if ($publishedIds->isEmpty()) {
            return 0;
        }

        $submitted = $user->submissions()
            ->whereIn('assignment_id', $publishedIds)
            ->count();

        return self::percent($submitted, $publishedIds->count());
    }

    public static function studentFeaturedCourse(Collection $courses, User $user): ?Course
    {
        if ($courses->isEmpty()) {
            return null;
        }

        return $courses->sortBy(function (Course $course) use ($user) {
            $progress = self::studentCourseProgress($user, $course);

            return [$progress < 100 ? 0 : 1, $progress];
        })->first();
    }
}
