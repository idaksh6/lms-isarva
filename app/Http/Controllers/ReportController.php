<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Support\DashboardMetrics;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isStudent()) {
            abort(403);
        }

        $stats = $user->isAdmin()
            ? DashboardMetrics::adminStats()
            : DashboardMetrics::lecturerStats($user);

        $courseBreakdown = $user->isAdmin()
            ? Course::query()->withCount(['students', 'assignments'])->orderBy('code')->get()
            : $user->taughtCourses()->withCount(['students', 'assignments'])->orderBy('code')->get();

        $recentSubmissions = Submission::query()
            ->when($user->isLecturer(), fn ($q) => $q->whereHas('assignment.course', fn ($c) => $c->where('lecturer_id', $user->id)))
            ->with(['student', 'assignment.course'])
            ->latest('submitted_at')
            ->limit(8)
            ->get();

        $statusBreakdown = [
            'submitted' => $this->scopedSubmissions($user)->where('status', SubmissionStatus::Submitted)->count(),
            'late' => $this->scopedSubmissions($user)->where('status', SubmissionStatus::Late)->count(),
            'needs_revision' => $this->scopedSubmissions($user)->where('status', SubmissionStatus::NeedsRevision)->count(),
            'reviewed' => $this->scopedSubmissions($user)->where('status', SubmissionStatus::Reviewed)->count(),
        ];

        return view('hubs.reports', compact('stats', 'courseBreakdown', 'recentSubmissions', 'statusBreakdown'));
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $user = $request->user();

        if ($user->isStudent()) {
            abort(403);
        }

        $filename = 'lms-report-'.now()->format('Y-m-d').'.csv';

        return response()->streamDownload(function () use ($user) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Course', 'Code', 'Students', 'Assignments', 'Submissions', 'Reviewed', 'Avg score']);

            $courses = $user->isAdmin()
                ? Course::query()->withCount(['students', 'assignments'])->orderBy('code')->get()
                : $user->taughtCourses()->withCount(['students', 'assignments'])->orderBy('code')->get();

            foreach ($courses as $course) {
                $submissions = Submission::query()
                    ->whereHas('assignment', fn ($q) => $q->where('course_id', $course->id))
                    ->get();
                $reviewed = $submissions->where('status', SubmissionStatus::Reviewed)->count();
                $avg = $submissions->whereNotNull('score')->avg('score');

                fputcsv($handle, [
                    $course->title,
                    $course->code,
                    $course->students_count,
                    $course->assignments_count,
                    $submissions->count(),
                    $reviewed,
                    $avg !== null ? round($avg, 1) : '',
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function scopedSubmissions(User $user)
    {
        return Submission::query()
            ->when($user->isLecturer(), fn ($q) => $q->whereHas('assignment.course', fn ($c) => $c->where('lecturer_id', $user->id)));
    }
}
