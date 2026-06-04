<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Support\DashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $upcoming = $this->upcomingAssignments($user);
        $highlightDates = $upcoming->pluck('due_at')->filter()->map(fn ($d) => $d->format('Y-m-d'))->unique()->values();

        if ($user->isAdmin()) {
            $recentCourses = Course::query()->with('lecturer')->withCount(['students', 'assignments'])->latest()->take(6)->get();
            $stats = DashboardMetrics::adminStats();

            return view('dashboard.admin', [
                'stats' => $stats,
                'recentCourses' => $recentCourses,
                'featuredCourse' => $recentCourses->first(),
                'featuredProgress' => $recentCourses->first()
                    ? DashboardMetrics::adminCourseProgress($recentCourses->first())
                    : 0,
                'upcoming' => $upcoming,
                'highlightDates' => $highlightDates,
            ]);
        }

        if ($user->isLecturer()) {
            $courses = $user->taughtCourses()->with('lecturer')->withCount(['students', 'assignments'])->get();
            $stats = DashboardMetrics::lecturerStats($user);

            return view('dashboard.lecturer', [
                'courses' => $courses,
                'stats' => $stats,
                'featuredCourse' => $courses->first(),
                'featuredProgress' => $courses->first()
                    ? DashboardMetrics::lecturerCourseProgress($courses->first())
                    : 0,
                'upcoming' => $upcoming,
                'highlightDates' => $highlightDates,
            ]);
        }

        $courses = $user->enrolledCourses()
            ->with('lecturer')
            ->withCount(['assignments' => fn ($q) => $q->where('is_published', true)])
            ->with(['assignments' => fn ($q) => $q->where('is_published', true)->orderBy('due_at')])
            ->get();

        $assignmentIds = $courses->flatMap(fn ($c) => $c->assignments->pluck('id'));
        $submittedIds = $user->submissions()
            ->whereIn('assignment_id', $assignmentIds)
            ->pluck('assignment_id');

        $openAssignments = Assignment::query()
            ->whereIn('id', $assignmentIds)
            ->whereNotIn('id', $submittedIds)
            ->where('is_published', true)
            ->with('course')
            ->orderBy('due_at')
            ->take(6)
            ->get();

        $featured = DashboardMetrics::studentFeaturedCourse($courses, $user);

        return view('dashboard.student', [
            'courses' => $courses,
            'stats' => DashboardMetrics::studentStats($user, $courses, $openAssignments),
            'featuredCourse' => $featured,
            'featuredProgress' => $featured ? DashboardMetrics::studentCourseProgress($user, $featured) : 0,
            'openAssignments' => $openAssignments,
            'upcoming' => $upcoming,
            'highlightDates' => $highlightDates,
        ]);
    }

    private function upcomingAssignments(User $user): Collection
    {
        $query = Assignment::query()
            ->where('is_published', true)
            ->whereNotNull('due_at')
            ->where('due_at', '>=', now()->startOfDay())
            ->with('course')
            ->orderBy('due_at');

        if ($user->isLecturer()) {
            $query->whereHas('course', fn ($q) => $q->where('lecturer_id', $user->id));
        } elseif ($user->isStudent()) {
            $courseIds = $user->enrolledCourses()->pluck('courses.id');
            $query->whereIn('course_id', $courseIds);
        }

        return $query->take(6)->get();
    }
}
