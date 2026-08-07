<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Support\DashboardMetrics;
use App\Support\IndividualAssignmentReport;
use App\Support\LmsQuery;
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

    public function assignments(Request $request): View
    {
        $user = $this->staffUser($request);

        $courses = LmsQuery::coursesFor($user)->orderBy('code')->get();
        $filters = $this->assignmentFilters($request);

        $selectedCourse = null;
        $selectedAssignment = null;
        $assignments = collect();
        $rows = collect();
        $kpis = null;

        if ($filters['course']) {
            $selectedCourse = $courses->firstWhere('id', $filters['course'])
                ?? Course::query()->find($filters['course']);

            if ($selectedCourse) {
                $this->authorize('view', $selectedCourse);

                $assignments = $selectedCourse->assignments()
                    ->where('is_published', true)
                    ->orderBy('due_at')
                    ->orderBy('title')
                    ->get();

                if ($filters['assignment']) {
                    $selectedAssignment = $assignments->firstWhere('id', $filters['assignment'])
                        ?? Assignment::query()
                            ->where('course_id', $selectedCourse->id)
                            ->where('id', $filters['assignment'])
                            ->first();

                    if ($selectedAssignment) {
                        $report = IndividualAssignmentReport::build($selectedAssignment, $filters);
                        $rows = $report['rows'];
                        $kpis = $report['kpis'];
                    }
                }
            }
        }

        return view('hubs.reports-assignments', compact(
            'courses',
            'assignments',
            'selectedCourse',
            'selectedAssignment',
            'filters',
            'rows',
            'kpis',
        ));
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $user = $this->staffUser($request);

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

    public function exportAssignments(Request $request): StreamedResponse|Response
    {
        $user = $this->staffUser($request);
        $filters = $this->assignmentFilters($request);

        $course = Course::query()->findOrFail($filters['course']);
        $this->authorize('view', $course);

        if ($user->isLecturer() && $course->lecturer_id !== $user->id) {
            abort(403);
        }

        $assignment = Assignment::query()
            ->where('course_id', $course->id)
            ->where('id', $filters['assignment'])
            ->firstOrFail();

        $report = IndividualAssignmentReport::build($assignment, $filters);
        $format = strtolower($request->string('format')->trim()->toString() ?: 'csv');
        $baseName = 'assignment-report-'.$course->code.'-'.$assignment->id.'-'.now()->format('Y-m-d');

        return match ($format) {
            'xlsx', 'excel' => $this->exportAssignmentsExcel($assignment, $report, $baseName),
            'pdf' => $this->exportAssignmentsPdf($assignment, $report, $baseName),
            default => $this->exportAssignmentsCsv($assignment, $report, $baseName),
        };
    }

    /**
     * @param  array{rows: \Illuminate\Support\Collection, kpis: array<string, mixed>}  $report
     */
    private function exportAssignmentsCsv(Assignment $assignment, array $report, string $baseName): StreamedResponse
    {
        return response()->streamDownload(function () use ($assignment, $report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, IndividualAssignmentReport::csvHeaders());

            foreach ($report['rows'] as $row) {
                fputcsv($handle, IndividualAssignmentReport::csvRow($assignment, $row));
            }

            fclose($handle);
        }, $baseName.'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array{rows: \Illuminate\Support\Collection, kpis: array<string, mixed>}  $report
     */
    private function exportAssignmentsExcel(Assignment $assignment, array $report, string $baseName): StreamedResponse
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Assignment report');

        $headers = IndividualAssignmentReport::csvHeaders();
        foreach ($headers as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle('A1:'.$lastColumn.'1')->getFont()->setBold(true);

        $rowNumber = 2;
        foreach ($report['rows'] as $row) {
            foreach (IndividualAssignmentReport::csvRow($assignment, $row) as $col => $value) {
                $sheet->setCellValue([$col + 1, $rowNumber], $value);
            }
            $rowNumber++;
        }

        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $baseName.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  array{rows: \Illuminate\Support\Collection, kpis: array<string, mixed>}  $report
     */
    private function exportAssignmentsPdf(Assignment $assignment, array $report, string $baseName): Response
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('hubs.reports-assignments-pdf', [
            'assignment' => $assignment->loadMissing('course.lecturer'),
            'rows' => $report['rows'],
            'kpis' => $report['kpis'],
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($baseName.'.pdf');
    }

    private function staffUser(Request $request): User
    {
        $user = $request->user();

        if ($user->isStudent()) {
            abort(403);
        }

        return $user;
    }

    /**
     * @return array{
     *     course: int|null,
     *     assignment: int|null,
     *     status: string|null,
     *     graded: string|null,
     *     q: string|null,
     *     submitted_from: string|null,
     *     submitted_to: string|null,
     *     score_min: string|null,
     *     score_max: string|null,
     * }
     */
    private function assignmentFilters(Request $request): array
    {
        return [
            'course' => $request->integer('course') ?: null,
            'assignment' => $request->integer('assignment') ?: null,
            'status' => $request->string('status')->trim()->toString() ?: null,
            'graded' => $request->string('graded')->trim()->toString() ?: null,
            'q' => $request->string('q')->trim()->toString() ?: null,
            'submitted_from' => $request->string('submitted_from')->trim()->toString() ?: null,
            'submitted_to' => $request->string('submitted_to')->trim()->toString() ?: null,
            'score_min' => $request->string('score_min')->trim()->toString() ?: null,
            'score_max' => $request->string('score_max')->trim()->toString() ?: null,
        ];
    }

    private function scopedSubmissions(User $user)
    {
        return Submission::query()
            ->when($user->isLecturer(), fn ($q) => $q->whereHas('assignment.course', fn ($c) => $c->where('lecturer_id', $user->id)));
    }
}
