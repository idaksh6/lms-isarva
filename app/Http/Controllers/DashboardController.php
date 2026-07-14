<?php

namespace App\Http\Controllers;

use App\Enums\SessionDeliveryMode;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Support\DashboardAnalytics;
use App\Support\DashboardMetrics;
use App\Support\LmsQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $upcoming = $this->upcomingAssignments($user);
        $calendar = $this->monthCalendarSnapshot($user);

        if ($user->isAdmin()) {
            $recentCourses = Course::query()->with('lecturer')->withCount(['students', 'assignments'])->latest()->take(6)->get();
            $stats = DashboardMetrics::adminStats();

            return view('dashboard.admin', [
                'stats' => $stats,
                'analytics' => DashboardAnalytics::forUser($user),
                'recentCourses' => $recentCourses,
                'featuredCourse' => $recentCourses->first(),
                'featuredProgress' => $recentCourses->first()
                    ? DashboardMetrics::adminCourseProgress($recentCourses->first())
                    : 0,
                'upcoming' => $upcoming,
                ...$calendar,
            ]);
        }

        if ($user->isLecturer()) {
            $courses = $user->taughtCourses()->with('lecturer')->withCount(['students', 'assignments'])->get();
            $stats = DashboardMetrics::lecturerStats($user);

            return view('dashboard.lecturer', [
                'courses' => $courses,
                'stats' => $stats,
                'analytics' => DashboardAnalytics::forUser($user),
                'featuredCourse' => $courses->first(),
                'featuredProgress' => $courses->first()
                    ? DashboardMetrics::lecturerCourseProgress($courses->first())
                    : 0,
                'upcoming' => $upcoming,
                ...$calendar,
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
            'analytics' => DashboardAnalytics::forUser($user, $courses),
            'featuredCourse' => $featured,
            'featuredProgress' => $featured ? DashboardMetrics::studentCourseProgress($user, $featured) : 0,
            'openAssignments' => $openAssignments,
            'upcoming' => $upcoming,
            ...$calendar,
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

    /**
     * @return array{
     *     sessionEventsByDate: array<string, array{online: bool, offline: bool}>,
     *     dueEventsByDate: array<string, array{due: bool}>,
     *     upcomingSessions: Collection
     * }
     */
    private function monthCalendarSnapshot(User $user): array
    {
        $month = now();
        $rangeStart = $month->copy()->startOfMonth()->startOfWeek()->startOfDay();
        $rangeEnd = $month->copy()->endOfMonth()->endOfWeek()->endOfDay();

        $assignments = LmsQuery::assignmentsFor($user)
            ->where('is_published', true)
            ->whereNotNull('due_at')
            ->whereBetween('due_at', [$rangeStart, $rangeEnd])
            ->get();

        $sessions = LmsQuery::classSessionsFor($user)
            ->whereBetween('starts_at', [$rangeStart, $rangeEnd])
            ->with('course')
            ->orderBy('starts_at')
            ->get();

        $sessionEventsByDate = [];
        foreach ($sessions->groupBy(fn ($s) => $s->starts_at->format('Y-m-d')) as $day => $daySessions) {
            $sessionEventsByDate[$day] = [
                'online' => $daySessions->contains(fn ($s) => $s->mode === SessionDeliveryMode::Online),
                'offline' => $daySessions->contains(fn ($s) => $s->mode === SessionDeliveryMode::Offline),
            ];
        }

        $dueEventsByDate = [];
        foreach ($assignments->groupBy(fn ($a) => $a->due_at->format('Y-m-d'))->keys() as $day) {
            $dueEventsByDate[$day] = ['due' => true];
        }

        $upcomingSessions = LmsQuery::classSessionsFor($user)
            ->where('starts_at', '>=', now()->startOfDay())
            ->with('course')
            ->orderBy('starts_at')
            ->take(4)
            ->get();

        return [
            'sessionEventsByDate' => $sessionEventsByDate,
            'dueEventsByDate' => $dueEventsByDate,
            'upcomingSessions' => $upcomingSessions,
        ];
    }
}
