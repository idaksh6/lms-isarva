<?php

namespace App\Http\Controllers;

use App\Enums\SupportActionType;
use App\Enums\SupportCaseStatus;
use App\Models\AiGeneration;
use App\Models\Course;
use App\Models\StudentSupportAction;
use App\Models\StudentSupportCase;
use App\Models\User;
use App\Support\LmsQuery;
use App\Support\WeakStudentReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AtRiskController extends Controller
{
    public function index(Request $request): View
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
                $report = WeakStudentReport::build($selectedCourse);
            }
        }

        return view('hubs.reports-at-risk', compact('courses', 'selectedCourse', 'report'));
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $user = $this->staffUser($request);
        $course = Course::query()->findOrFail($request->integer('course'));
        $this->authorize('view', $course);

        if ($user->isLecturer() && $course->lecturer_id !== $user->id) {
            abort(403);
        }

        $report = WeakStudentReport::build($course);
        $format = strtolower($request->string('format')->trim()->toString() ?: 'csv');
        $baseName = 'at-risk-'.$course->code.'-'.now()->format('Y-m-d');

        return match ($format) {
            'xlsx', 'excel' => $this->exportExcel($course, $report, $baseName),
            'pdf' => $this->exportPdf($course, $report, $baseName),
            default => $this->exportCsv($course, $report, $baseName),
        };
    }

    public function storeCase(Request $request): RedirectResponse
    {
        $user = $this->staffUser($request);
        $this->authorize('create', StudentSupportCase::class);

        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $course = Course::query()->findOrFail($validated['course_id']);
        $this->authorize('update', $course);

        $student = User::query()->findOrFail($validated['user_id']);
        if (! $course->students()->where('users.id', $student->id)->exists()) {
            return back()->withErrors(['user_id' => 'Student is not enrolled on this course.']);
        }

        $existing = StudentSupportCase::query()
            ->where('course_id', $course->id)
            ->where('user_id', $student->id)
            ->whereIn('status', [SupportCaseStatus::Open->value, SupportCaseStatus::InProgress->value])
            ->first();

        if ($existing) {
            return redirect()
                ->route('reports.at-risk.cases.show', $existing)
                ->with('success', 'An open support case already exists for this student.');
        }

        $report = WeakStudentReport::build($course);
        $flagged = $report['flagged']->firstWhere(fn ($row) => $row['student']->id === $student->id);
        $metrics = WeakStudentReport::metricsForStudent($course, $student);
        $reasons = $flagged['reasons'] ?? ['Manually opened support case'];
        $reasonKeys = $flagged['reason_keys'] ?? ['manual'];

        $reasonSnapshot = collect($reasons)->values()->map(fn ($label, $i) => [
            'key' => $reasonKeys[$i] ?? 'manual',
            'label' => $label,
        ])->all();

        $case = StudentSupportCase::query()->create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'created_by' => $user->id,
            'status' => SupportCaseStatus::Open,
            'reasons' => $reasonSnapshot,
            'baseline_metrics' => $metrics,
            'latest_metrics' => $metrics,
            'identified_at' => now(),
        ]);

        return redirect()
            ->route('reports.at-risk.cases.show', $case)
            ->with('success', 'Support case opened.');
    }

    public function showCase(Request $request, StudentSupportCase $case): View
    {
        $this->authorize('view', $case);

        $case->load(['course.lecturer', 'student', 'creator', 'actions.creator']);
        $latest = WeakStudentReport::metricsForStudent($case->course, $case->student);
        $baseline = $case->baseline_metrics ?? [];
        $delta = $this->metricDelta($baseline, $latest);

        $aiGeneration = null;
        if ($request->integer('ai')) {
            $aiGeneration = AiGeneration::query()
                ->where('id', $request->integer('ai'))
                ->where('user_id', $request->user()->id)
                ->first();
        }

        return view('hubs.reports-at-risk-case', [
            'case' => $case,
            'latest' => $latest,
            'delta' => $delta,
            'actionTypes' => SupportActionType::cases(),
            'statuses' => SupportCaseStatus::cases(),
            'aiGeneration' => $aiGeneration,
            'aiEnabled' => (bool) config('ai.enabled') && (bool) config('ai.features.remediation_pack'),
        ]);
    }

    public function updateCase(Request $request, StudentSupportCase $case): RedirectResponse
    {
        $this->authorize('update', $case);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(SupportCaseStatus::class)],
        ]);

        $status = $validated['status'] instanceof SupportCaseStatus
            ? $validated['status']
            : SupportCaseStatus::from($validated['status']);

        $latest = WeakStudentReport::metricsForStudent($case->course, $case->student);

        $case->update([
            'status' => $status,
            'latest_metrics' => $latest,
            'resolved_at' => in_array($status, [SupportCaseStatus::Resolved, SupportCaseStatus::Dismissed], true)
                ? now()
                : null,
        ]);

        return back()->with('success', 'Support case updated.');
    }

    public function refreshMetrics(StudentSupportCase $case): RedirectResponse
    {
        $this->authorize('update', $case);

        $case->update([
            'latest_metrics' => WeakStudentReport::metricsForStudent($case->course, $case->student),
        ]);

        return back()->with('success', 'Latest metrics refreshed.');
    }

    public function storeAction(Request $request, StudentSupportCase $case): RedirectResponse
    {
        $this->authorize('addAction', $case);

        $validated = $request->validate([
            'type' => ['required', Rule::enum(SupportActionType::class)],
            'title' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
            'conducted_at' => ['nullable', 'date'],
        ]);

        $type = $validated['type'] instanceof SupportActionType
            ? $validated['type']
            : SupportActionType::from($validated['type']);

        $case->actions()->create([
            'created_by' => $request->user()->id,
            'type' => $type,
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'conducted_at' => $validated['conducted_at'] ?? now(),
        ]);

        if ($case->status === SupportCaseStatus::Open) {
            $case->update(['status' => SupportCaseStatus::InProgress]);
        }

        return back()->with('success', 'Intervention logged.');
    }

    public function destroyAction(StudentSupportAction $action): RedirectResponse
    {
        $this->authorize('delete', $action);
        $case = $action->supportCase;
        $action->delete();

        return redirect()
            ->route('reports.at-risk.cases.show', $case)
            ->with('success', 'Intervention entry removed.');
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function exportCsv(Course $course, array $report, string $baseName): StreamedResponse
    {
        return response()->streamDownload(function () use ($course, $report) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Weak student report']);
            fputcsv($handle, ['Course', $course->code.' — '.$course->title]);
            fputcsv($handle, []);
            fputcsv($handle, ['Student', 'Student ID', 'Email', 'Risk score', 'Reasons', 'Assignment avg', 'Course avg', 'Delta', 'Missing overdue', 'Late', 'Quiz avg', 'Participation %', 'Active case']);

            foreach ($report['flagged'] as $row) {
                $m = $row['metrics'];
                fputcsv($handle, [
                    $row['student']->name,
                    $row['student']->student_id ?? '',
                    $row['student']->email,
                    $row['risk_score'],
                    implode('; ', $row['reasons']),
                    $m['assignment_avg'] ?? '',
                    $m['course_avg'] ?? '',
                    $m['avg_delta'] ?? '',
                    $m['missing_overdue'] ?? '',
                    $m['late_count'] ?? '',
                    $m['quiz_avg'] ?? '',
                    $m['participation_rate'] ?? '',
                    $row['active_case'] ? 'Yes' : 'No',
                ]);
            }

            fclose($handle);
        }, $baseName.'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function exportExcel(Course $course, array $report, string $baseName): StreamedResponse
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr($course->code.' at-risk', 0, 31));

        $sheet->setCellValue([1, 1], $course->code.' — '.$course->title.' · Weak students');
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A1')->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('B45309');

        $headers = ['Student', 'Student ID', 'Email', 'Risk score', 'Reasons', 'Assignment avg', 'Course avg', 'Delta', 'Missing overdue', 'Late', 'Quiz avg', 'Participation %'];
        foreach ($headers as $i => $header) {
            $sheet->setCellValue([$i + 1, 3], $header);
        }
        $sheet->getStyle('A3:L3')->getFont()->setBold(true);

        $r = 4;
        foreach ($report['flagged'] as $row) {
            $m = $row['metrics'];
            $sheet->setCellValue([1, $r], $row['student']->name);
            $sheet->setCellValue([2, $r], $row['student']->student_id ?? '');
            $sheet->setCellValue([3, $r], $row['student']->email);
            $sheet->setCellValue([4, $r], $row['risk_score']);
            $sheet->setCellValue([5, $r], implode('; ', $row['reasons']));
            $sheet->setCellValue([6, $r], $m['assignment_avg'] ?? '');
            $sheet->setCellValue([7, $r], $m['course_avg'] ?? '');
            $sheet->setCellValue([8, $r], $m['avg_delta'] ?? '');
            $sheet->setCellValue([9, $r], $m['missing_overdue'] ?? '');
            $sheet->setCellValue([10, $r], $m['late_count'] ?? '');
            $sheet->setCellValue([11, $r], $m['quiz_avg'] ?? '');
            $sheet->setCellValue([12, $r], $m['participation_rate'] ?? '');
            $r++;
        }

        foreach (range(1, 12) as $col) {
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
    private function exportPdf(Course $course, array $report, string $baseName): Response
    {
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('hubs.reports-at-risk-pdf', [
            'course' => $course->loadMissing('lecturer'),
            'report' => $report,
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($baseName.'.pdf');
    }

    /**
     * @param  array<string, mixed>  $baseline
     * @param  array<string, mixed>  $latest
     * @return array<string, mixed>
     */
    private function metricDelta(array $baseline, array $latest): array
    {
        $keys = ['assignment_avg', 'missing_overdue', 'late_count', 'quiz_avg', 'participation_rate'];
        $delta = [];
        foreach ($keys as $key) {
            $before = $baseline[$key] ?? null;
            $after = $latest[$key] ?? null;
            $delta[$key] = ($before !== null && $after !== null)
                ? round((float) $after - (float) $before, 1)
                : null;
        }

        return $delta;
    }

    private function staffUser(Request $request): User
    {
        $user = $request->user();
        if ($user->isStudent()) {
            abort(403);
        }

        return $user;
    }
}
