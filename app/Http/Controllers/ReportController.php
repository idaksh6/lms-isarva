<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Support\CourseActivityReport;
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
        $sections = collect();
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
                        $sections = collect([[
                            'assignment' => $selectedAssignment,
                            'rows' => $rows,
                            'kpis' => $kpis,
                        ]]);
                    }
                } else {
                    $report = IndividualAssignmentReport::buildForCourse($selectedCourse, $filters);
                    $sections = $report['sections'];
                    $kpis = $report['kpis'];
                    $rows = $sections->flatMap(fn (array $section) => $section['rows']);
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
            'sections',
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

        if ($filters['assignment']) {
            $assignment = Assignment::query()
                ->where('course_id', $course->id)
                ->where('id', $filters['assignment'])
                ->firstOrFail();

            $report = IndividualAssignmentReport::build($assignment, $filters);
            $sections = collect([[
                'assignment' => $assignment,
                'rows' => $report['rows'],
                'kpis' => $report['kpis'],
            ]]);
            $kpis = $report['kpis'];
            $baseName = 'assignment-report-'.$course->code.'-'.$assignment->id.'-'.now()->format('Y-m-d');
        } else {
            $report = IndividualAssignmentReport::buildForCourse($course, $filters);
            $sections = $report['sections'];
            $kpis = $report['kpis'];
            $baseName = 'assignment-report-'.$course->code.'-all-'.now()->format('Y-m-d');
        }

        $format = strtolower($request->string('format')->trim()->toString() ?: 'csv');

        return match ($format) {
            'xlsx', 'excel' => $this->exportAssignmentsExcel($course, $sections, $baseName),
            'pdf' => $this->exportAssignmentsPdf($course, $sections, $kpis, $baseName),
            default => $this->exportAssignmentsCsv($sections, $baseName),
        };
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{assignment: Assignment, rows: \Illuminate\Support\Collection}>  $sections
     */
    private function exportAssignmentsCsv($sections, string $baseName): StreamedResponse
    {
        return response()->streamDownload(function () use ($sections) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, IndividualAssignmentReport::csvHeaders());

            foreach ($sections as $section) {
                foreach ($section['rows'] as $row) {
                    fputcsv($handle, IndividualAssignmentReport::csvRow($section['assignment'], $row));
                }
            }

            fclose($handle);
        }, $baseName.'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, array{assignment: Assignment, rows: \Illuminate\Support\Collection, kpis?: array}>  $sections
     */
    private function exportAssignmentsExcel(Course $course, $sections, string $baseName): StreamedResponse
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($course->code.' report', 0, 31));

        $headers = IndividualAssignmentReport::studentExportHeaders();
        $lastColIndex = count($headers);
        $lastColumn = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($lastColIndex);

        $rowNumber = 1;
        $sheet->setCellValue([1, $rowNumber], $course->code.' — '.$course->title);
        $sheet->mergeCells('A'.$rowNumber.':'.$lastColumn.$rowNumber);
        $sheet->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A'.$rowNumber)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('1D4ED8');
        $sheet->getStyle('A'.$rowNumber)->getFont()->getColor()->setRGB('FFFFFF');
        $rowNumber++;

        $sheet->setCellValue([1, $rowNumber], 'Individual assignment report · Generated '.now()->format('Y-m-d H:i'));
        $sheet->mergeCells('A'.$rowNumber.':'.$lastColumn.$rowNumber);
        $sheet->getStyle('A'.$rowNumber)->getFont()->setItalic(true)->setSize(10);
        $rowNumber += 2;

        foreach ($sections as $section) {
            /** @var Assignment $assignment */
            $assignment = $section['assignment'];
            $sectionRows = $section['rows'];
            $sectionKpis = $section['kpis'] ?? null;

            $dueLabel = $assignment->due_at
                ? 'Due '.$assignment->due_at->format('M j, Y g:i A')
                : 'No due date';
            $avgLabel = ($sectionKpis['avg_score'] ?? null) !== null
                ? 'Avg '.$sectionKpis['avg_score'].'%'
                : 'Avg —';
            $headerText = $assignment->title.'  ·  '.$dueLabel.'  ·  '.$sectionRows->count().' students  ·  '.$avgLabel;

            $sheet->setCellValue([1, $rowNumber], $headerText);
            $sheet->mergeCells('A'.$rowNumber.':'.$lastColumn.$rowNumber);
            $sheet->getStyle('A'.$rowNumber)->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle('A'.$rowNumber)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('F59E0B');
            $sheet->getStyle('A'.$rowNumber)->getFont()->getColor()->setRGB('1C1917');
            $rowNumber++;

            foreach ($headers as $index => $header) {
                $sheet->setCellValue([$index + 1, $rowNumber], $header);
            }
            $sheet->getStyle('A'.$rowNumber.':'.$lastColumn.$rowNumber)->getFont()->setBold(true);
            $sheet->getStyle('A'.$rowNumber.':'.$lastColumn.$rowNumber)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
            $rowNumber++;

            if ($sectionRows->isEmpty()) {
                $sheet->setCellValue([1, $rowNumber], 'No students match the current filters for this assignment.');
                $sheet->mergeCells('A'.$rowNumber.':'.$lastColumn.$rowNumber);
                $sheet->getStyle('A'.$rowNumber)->getFont()->setItalic(true);
                $rowNumber += 2;

                continue;
            }

            foreach ($sectionRows as $row) {
                foreach (IndividualAssignmentReport::studentExportRow($row) as $col => $value) {
                    $sheet->setCellValue([$col + 1, $rowNumber], $value);
                }
                $rowNumber++;
            }

            $rowNumber++;
        }

        foreach (range(1, $lastColIndex) as $col) {
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
     * @param  \Illuminate\Support\Collection<int, array{assignment: Assignment, rows: \Illuminate\Support\Collection, kpis?: array}>  $sections
     * @param  array<string, mixed>|null  $kpis
     */
    private function exportAssignmentsPdf(Course $course, $sections, ?array $kpis, string $baseName): Response
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('hubs.reports-assignments-pdf', [
            'course' => $course->loadMissing('lecturer'),
            'sections' => $sections,
            'kpis' => $kpis,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($baseName.'.pdf');
    }

    public function activity(Request $request): View
    {
        $user = $this->staffUser($request);

        $courses = LmsQuery::coursesFor($user)->orderBy('code')->get();
        $courseId = $request->integer('course') ?: null;
        $selectedCourse = null;
        $report = null;

        if ($courseId) {
            $selectedCourse = $courses->firstWhere('id', $courseId)
                ?? Course::query()->find($courseId);

            if ($selectedCourse) {
                $this->authorize('view', $selectedCourse);
                $report = CourseActivityReport::build($selectedCourse);
            }
        }

        return view('hubs.reports-activity', compact('courses', 'selectedCourse', 'report'));
    }

    public function exportActivity(Request $request): StreamedResponse|Response
    {
        $user = $this->staffUser($request);
        $course = Course::query()->findOrFail($request->integer('course'));
        $this->authorize('view', $course);

        if ($user->isLecturer() && $course->lecturer_id !== $user->id) {
            abort(403);
        }

        $report = CourseActivityReport::build($course);
        $format = strtolower($request->string('format')->trim()->toString() ?: 'csv');
        $baseName = 'course-activity-'.$course->code.'-'.now()->format('Y-m-d');

        return match ($format) {
            'xlsx', 'excel' => $this->exportActivityExcel($course, $report, $baseName),
            'pdf' => $this->exportActivityPdf($course, $report, $baseName),
            default => $this->exportActivityCsv($course, $report, $baseName),
        };
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function exportActivityCsv(Course $course, array $report, string $baseName): StreamedResponse
    {
        return response()->streamDownload(function () use ($course, $report) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Course Activity Report']);
            fputcsv($handle, ['Course', $course->code.' — '.$course->title]);
            fputcsv($handle, []);
            fputcsv($handle, ['Overall statistics']);
            foreach ($report['kpis'] as $key => $value) {
                fputcsv($handle, [str_replace('_', ' ', ucfirst($key)), $value ?? '']);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Class sessions / activities']);
            fputcsv($handle, ['Title', 'Starts at', 'Ends at', 'Mode', 'Location / link', 'Status']);
            foreach ($report['sessions'] as $session) {
                fputcsv($handle, [
                    $session->displayTitle(),
                    $session->starts_at->format('Y-m-d H:i'),
                    $session->ends_at?->format('Y-m-d H:i') ?? '',
                    $session->mode?->label() ?? '',
                    $session->mode?->value === 'online' ? ($session->meeting_link ?? '') : ($session->location ?? ''),
                    $session->starts_at->isPast() ? 'Past' : 'Upcoming',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Assignments']);
            fputcsv($handle, ['Assignment', 'Due at', 'Submitted', 'Not submitted', 'Late', 'Reviewed', 'Avg score %', 'Submission rate %']);
            foreach ($report['assignments'] as $row) {
                $assignment = $row['assignment'];
                fputcsv($handle, [
                    $assignment->title,
                    $assignment->due_at?->format('Y-m-d H:i') ?? '',
                    $row['submitted'],
                    $row['not_submitted'],
                    $row['late'],
                    $row['reviewed'],
                    $row['avg_score'] ?? '',
                    $row['submission_rate'] ?? '',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Quizzes / assessments']);
            fputcsv($handle, ['Assessment', 'Type', 'Due at', 'Completed', 'Not completed', 'Completion rate %', 'Avg score %']);
            foreach ($report['assessments'] as $row) {
                $assessment = $row['assessment'];
                fputcsv($handle, [
                    $assessment->title,
                    $row['type_label'],
                    $assessment->due_at?->format('Y-m-d H:i') ?? '',
                    $row['completed'],
                    $row['not_completed'],
                    $row['completion_rate'] ?? '',
                    $row['avg_score'] ?? '',
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Student participation']);
            fputcsv($handle, ['Student', 'Student ID', 'Email', 'Assignments submitted', 'Assignments total', 'Quizzes completed', 'Quizzes total', 'Questions asked', 'Answers posted', 'Participation rate %']);
            foreach ($report['participation'] as $row) {
                $student = $row['student'];
                fputcsv($handle, [
                    $student->name,
                    $student->student_id ?? '',
                    $student->email,
                    $row['assignments_submitted'],
                    $row['assignments_total'],
                    $row['quizzes_completed'],
                    $row['quizzes_total'],
                    $row['questions_asked'],
                    $row['answers_posted'],
                    $row['participation_rate'] ?? '',
                ]);
            }

            fclose($handle);
        }, $baseName.'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function exportActivityExcel(Course $course, array $report, string $baseName): StreamedResponse
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($course->code.' activity', 0, 31));

        $row = 1;
        $writeHeader = function (string $text, string $color = '1D4ED8') use ($sheet, &$row) {
            $sheet->setCellValue([1, $row], $text);
            $sheet->mergeCells('A'.$row.':J'.$row);
            $sheet->getStyle('A'.$row)->getFont()->setBold(true)->setSize(12)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle('A'.$row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($color);
            $row++;
        };

        $writeCols = function (array $cols) use ($sheet, &$row) {
            foreach ($cols as $i => $col) {
                $sheet->setCellValue([$i + 1, $row], $col);
            }
            $sheet->getStyle('A'.$row.':J'.$row)->getFont()->setBold(true);
            $sheet->getStyle('A'.$row.':J'.$row)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
            $row++;
        };

        $writeHeader($course->code.' — '.$course->title.' · Course Activity Report');
        $row++;

        $writeHeader('Overall statistics', '0F766E');
        $writeCols(['Metric', 'Value']);
        foreach ($report['kpis'] as $key => $value) {
            $sheet->setCellValue([1, $row], str_replace('_', ' ', ucfirst($key)));
            $sheet->setCellValue([2, $row], $value ?? '—');
            $row++;
        }
        $row++;

        $writeHeader('Class sessions / activities', 'F59E0B');
        $writeCols(['Title', 'Starts at', 'Ends at', 'Mode', 'Location / link', 'Status']);
        foreach ($report['sessions'] as $session) {
            $sheet->setCellValue([1, $row], $session->displayTitle());
            $sheet->setCellValue([2, $row], $session->starts_at->format('Y-m-d H:i'));
            $sheet->setCellValue([3, $row], $session->ends_at?->format('Y-m-d H:i') ?? '');
            $sheet->setCellValue([4, $row], $session->mode?->label() ?? '');
            $sheet->setCellValue([5, $row], $session->mode?->value === 'online' ? ($session->meeting_link ?? '') : ($session->location ?? ''));
            $sheet->setCellValue([6, $row], $session->starts_at->isPast() ? 'Past' : 'Upcoming');
            $row++;
        }
        if ($report['sessions']->isEmpty()) {
            $sheet->setCellValue([1, $row], 'No class sessions scheduled.');
            $row++;
        }
        $row++;

        $writeHeader('Assignments', 'F59E0B');
        $writeCols(['Assignment', 'Due at', 'Submitted', 'Not submitted', 'Late', 'Reviewed', 'Avg score %', 'Submission rate %']);
        foreach ($report['assignments'] as $item) {
            $assignment = $item['assignment'];
            $sheet->setCellValue([1, $row], $assignment->title);
            $sheet->setCellValue([2, $row], $assignment->due_at?->format('Y-m-d H:i') ?? '');
            $sheet->setCellValue([3, $row], $item['submitted']);
            $sheet->setCellValue([4, $row], $item['not_submitted']);
            $sheet->setCellValue([5, $row], $item['late']);
            $sheet->setCellValue([6, $row], $item['reviewed']);
            $sheet->setCellValue([7, $row], $item['avg_score'] ?? '');
            $sheet->setCellValue([8, $row], $item['submission_rate'] ?? '');
            $row++;
        }
        $row++;

        $writeHeader('Quizzes / assessments', 'F59E0B');
        $writeCols(['Assessment', 'Type', 'Due at', 'Completed', 'Not completed', 'Completion rate %', 'Avg score %']);
        foreach ($report['assessments'] as $item) {
            $assessment = $item['assessment'];
            $sheet->setCellValue([1, $row], $assessment->title);
            $sheet->setCellValue([2, $row], $item['type_label']);
            $sheet->setCellValue([3, $row], $assessment->due_at?->format('Y-m-d H:i') ?? '');
            $sheet->setCellValue([4, $row], $item['completed']);
            $sheet->setCellValue([5, $row], $item['not_completed']);
            $sheet->setCellValue([6, $row], $item['completion_rate'] ?? '');
            $sheet->setCellValue([7, $row], $item['avg_score'] ?? '');
            $row++;
        }
        $row++;

        $writeHeader('Student participation', 'F59E0B');
        $writeCols(['Student', 'Student ID', 'Email', 'Assignments submitted', 'Assignments total', 'Quizzes completed', 'Quizzes total', 'Questions asked', 'Answers posted', 'Participation %']);
        foreach ($report['participation'] as $item) {
            $student = $item['student'];
            $sheet->setCellValue([1, $row], $student->name);
            $sheet->setCellValue([2, $row], $student->student_id ?? '');
            $sheet->setCellValue([3, $row], $student->email);
            $sheet->setCellValue([4, $row], $item['assignments_submitted']);
            $sheet->setCellValue([5, $row], $item['assignments_total']);
            $sheet->setCellValue([6, $row], $item['quizzes_completed']);
            $sheet->setCellValue([7, $row], $item['quizzes_total']);
            $sheet->setCellValue([8, $row], $item['questions_asked']);
            $sheet->setCellValue([9, $row], $item['answers_posted']);
            $sheet->setCellValue([10, $row], $item['participation_rate'] ?? '');
            $row++;
        }

        foreach (range(1, 10) as $col) {
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
     * @param  array<string, mixed>  $report
     */
    private function exportActivityPdf(Course $course, array $report, string $baseName): Response
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('hubs.reports-activity-pdf', [
            'course' => $course->loadMissing('lecturer'),
            'report' => $report,
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
