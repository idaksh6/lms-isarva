<?php

namespace App\Http\Controllers;

use App\Enums\ActionPlanStatus;
use App\Enums\ImprovementAreaPriority;
use App\Enums\ImprovementAreaStatus;
use App\Enums\MentoringSessionMode;
use App\Enums\MentoringStatus;
use App\Enums\UserRole;
use App\Models\MentoringActionPlan;
use App\Models\MentoringImprovementArea;
use App\Models\MentoringRelationship;
use App\Models\MentoringSession;
use App\Models\User;
use App\Notifications\MentoringAssignedNotification;
use App\Support\LmsQuery;
use App\Support\MentoringReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MentoringController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', MentoringRelationship::class);

        $user = $request->user();
        $status = $request->string('status')->toString();
        $courseId = $request->integer('course') ?: null;

        $query = MentoringRelationship::query()
            ->with(['mentor', 'mentee', 'course'])
            ->withCount(['sessions', 'improvementAreas', 'actionPlans'])
            ->latest();

        if ($user->isLecturer()) {
            $query->where('mentor_id', $user->id);
        } elseif ($user->isStudent()) {
            $query->where('mentee_id', $user->id);
        }

        if ($status !== '' && MentoringStatus::tryFrom($status)) {
            $query->where('status', $status);
        }

        if ($courseId) {
            $query->where('course_id', $courseId);
        }

        $relationships = $query->paginate(12)->withQueryString();
        $courses = LmsQuery::coursesFor($user)->orderBy('code')->get();
        $report = MentoringReport::build($user, $courseId);

        return view('hubs.mentoring-index', [
            'relationships' => $relationships,
            'courses' => $courses,
            'summary' => $report['summary'],
            'effectiveness' => $report['effectiveness'],
            'filters' => [
                'status' => $status,
                'course' => $courseId,
            ],
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', MentoringRelationship::class);

        $user = $request->user();
        $courses = LmsQuery::coursesFor($user)->orderBy('code')->get();
        $mentors = $user->isAdmin()
            ? User::query()->where('role', UserRole::Lecturer)->where('is_active', true)->orderBy('name')->get()
            : collect([$user]);
        $students = $user->isAdmin()
            ? User::query()
                ->where('role', UserRole::Student)
                ->where('is_active', true)
                ->orderBy('name')
                ->get()
            : User::query()
                ->where('role', UserRole::Student)
                ->where('is_active', true)
                ->whereHas('enrolledCourses', fn ($q) => $q->where('lecturer_id', $user->id))
                ->orderBy('name')
                ->get();

        return view('hubs.mentoring-create', compact('courses', 'mentors', 'students'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', MentoringRelationship::class);

        $user = $request->user();
        $validated = $request->validate([
            'mentor_id' => ['required', 'exists:users,id'],
            'mentee_id' => ['required', 'exists:users,id', 'different:mentor_id'],
            'course_id' => ['nullable', 'exists:courses,id'],
            'goals' => ['nullable', 'string'],
            'started_at' => ['nullable', 'date'],
        ]);

        $mentor = User::query()->findOrFail($validated['mentor_id']);
        $mentee = User::query()->findOrFail($validated['mentee_id']);

        if (! $mentor->isLecturer() && ! $mentor->isAdmin()) {
            return back()->withErrors(['mentor_id' => 'Mentor must be a lecturer.'])->withInput();
        }

        if (! $mentee->isStudent()) {
            return back()->withErrors(['mentee_id' => 'Mentee must be a student.'])->withInput();
        }

        if ($user->isLecturer() && (int) $validated['mentor_id'] !== $user->id) {
            abort(403);
        }

        if (! empty($validated['course_id'])) {
            $course = LmsQuery::coursesFor($user)->whereKey($validated['course_id'])->first();
            if (! $course) {
                abort(403);
            }
            if (! $course->students()->where('users.id', $mentee->id)->exists()) {
                return back()->withErrors(['mentee_id' => 'Student is not enrolled on the selected course.'])->withInput();
            }
        }

        $exists = MentoringRelationship::query()
            ->where('mentor_id', $mentor->id)
            ->where('mentee_id', $mentee->id)
            ->where('status', MentoringStatus::Active)
            ->when($validated['course_id'] ?? null, fn ($q, $courseId) => $q->where('course_id', $courseId))
            ->exists();

        if ($exists) {
            return back()->withErrors(['mentee_id' => 'An active mentoring relationship already exists for this pair.'])->withInput();
        }

        $relationship = MentoringRelationship::query()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $mentee->id,
            'course_id' => $validated['course_id'] ?? null,
            'assigned_by' => $user->id,
            'status' => MentoringStatus::Active,
            'goals' => $validated['goals'] ?? null,
            'started_at' => $validated['started_at'] ?? now(),
        ]);

        if ($mentee->isActive()) {
            $mentee->notify(new MentoringAssignedNotification($relationship));
        }

        return redirect()
            ->route('mentoring.show', $relationship)
            ->with('success', 'Mentoring relationship created.');
    }

    public function show(MentoringRelationship $mentoring): View
    {
        $this->authorize('view', $mentoring);

        $mentoring->load([
            'mentor',
            'mentee',
            'course',
            'improvementAreas.creator',
            'sessions.creator',
            'actionPlans.creator',
        ]);

        return view('hubs.mentoring-show', [
            'relationship' => $mentoring,
            'canManage' => request()->user()->can('manageRecords', $mentoring),
        ]);
    }

    public function update(Request $request, MentoringRelationship $mentoring): RedirectResponse
    {
        $this->authorize('update', $mentoring);

        $validated = $request->validate([
            'status' => ['required', Rule::enum(MentoringStatus::class)],
            'goals' => ['nullable', 'string'],
        ]);

        $status = MentoringStatus::from($validated['status']);

        $mentoring->update([
            'status' => $status,
            'goals' => $validated['goals'] ?? null,
            'ended_at' => $status === MentoringStatus::Closed ? ($mentoring->ended_at ?? now()) : null,
        ]);

        return back()->with('success', 'Mentoring relationship updated.');
    }

    public function storeArea(Request $request, MentoringRelationship $mentoring): RedirectResponse
    {
        $this->authorize('manageRecords', $mentoring);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::enum(ImprovementAreaPriority::class)],
            'status' => ['required', Rule::enum(ImprovementAreaStatus::class)],
        ]);

        $mentoring->improvementAreas()->create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Improvement area added.');
    }

    public function updateArea(Request $request, MentoringImprovementArea $area): RedirectResponse
    {
        $this->authorize('manageRecords', $area->relationship);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'priority' => ['required', Rule::enum(ImprovementAreaPriority::class)],
            'status' => ['required', Rule::enum(ImprovementAreaStatus::class)],
        ]);

        $area->update($validated);

        return back()->with('success', 'Improvement area updated.');
    }

    public function destroyArea(MentoringImprovementArea $area): RedirectResponse
    {
        $this->authorize('manageRecords', $area->relationship);
        $area->delete();

        return back()->with('success', 'Improvement area removed.');
    }

    public function storeSession(Request $request, MentoringRelationship $mentoring): RedirectResponse
    {
        $this->authorize('manageRecords', $mentoring);

        $validated = $request->validate([
            'conducted_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:5', 'max:480'],
            'mode' => ['required', Rule::enum(MentoringSessionMode::class)],
            'topic' => ['nullable', 'string', 'max:255'],
            'remarks' => ['nullable', 'string'],
            'student_progress_notes' => ['nullable', 'string'],
        ]);

        $mentoring->sessions()->create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        if ($mentoring->status === MentoringStatus::Paused) {
            $mentoring->update(['status' => MentoringStatus::Active]);
        }

        return back()->with('success', 'Mentoring session recorded.');
    }

    public function destroySession(MentoringSession $session): RedirectResponse
    {
        $this->authorize('manageRecords', $session->relationship);
        $session->delete();

        return back()->with('success', 'Session removed.');
    }

    public function storePlan(Request $request, MentoringRelationship $mentoring): RedirectResponse
    {
        $this->authorize('manageRecords', $mentoring);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'objectives' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(ActionPlanStatus::class)],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'progress_notes' => ['nullable', 'string'],
        ]);

        $mentoring->actionPlans()->create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Action plan added.');
    }

    public function updatePlan(Request $request, MentoringActionPlan $plan): RedirectResponse
    {
        $this->authorize('manageRecords', $plan->relationship);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'objectives' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'status' => ['required', Rule::enum(ActionPlanStatus::class)],
            'progress_percent' => ['required', 'integer', 'min:0', 'max:100'],
            'progress_notes' => ['nullable', 'string'],
        ]);

        if (ActionPlanStatus::from($validated['status']) === ActionPlanStatus::Completed) {
            $validated['progress_percent'] = 100;
        }

        $plan->update($validated);

        return back()->with('success', 'Action plan updated.');
    }

    public function destroyPlan(MentoringActionPlan $plan): RedirectResponse
    {
        $this->authorize('manageRecords', $plan->relationship);
        $plan->delete();

        return back()->with('success', 'Action plan removed.');
    }

    public function report(Request $request): View
    {
        $this->staffOnly($request);
        $user = $request->user();
        $courseId = $request->integer('course') ?: null;
        $mentorId = $request->integer('mentor') ?: null;

        $courses = LmsQuery::coursesFor($user)->orderBy('code')->get();
        $mentors = $user->isAdmin()
            ? User::query()->where('role', UserRole::Lecturer)->where('is_active', true)->orderBy('name')->get()
            : collect();

        $report = MentoringReport::build($user, $courseId, $mentorId);

        return view('hubs.mentoring-report', [
            'courses' => $courses,
            'mentors' => $mentors,
            'relationships' => $report['relationships'],
            'summary' => $report['summary'],
            'effectiveness' => $report['effectiveness'],
            'filters' => [
                'course' => $courseId,
                'mentor' => $mentorId,
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $this->staffOnly($request);
        $user = $request->user();
        $courseId = $request->integer('course') ?: null;
        $mentorId = $request->integer('mentor') ?: null;
        $format = strtolower($request->string('format')->toString() ?: 'csv');
        $report = MentoringReport::build($user, $courseId, $mentorId);
        $baseName = 'mentoring-report-'.now()->format('Y-m-d');

        return match ($format) {
            'xlsx', 'excel' => $this->exportExcel($report, $baseName),
            'pdf' => $this->exportPdf($report, $baseName),
            default => $this->exportCsv($report, $baseName),
        };
    }

    /**
     * @param  array{relationships: \Illuminate\Support\Collection, summary: array, effectiveness: array}  $report
     */
    private function exportCsv(array $report, string $baseName): StreamedResponse
    {
        return response()->streamDownload(function () use ($report): void {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Mentor', 'Mentee', 'Course', 'Status', 'Sessions', 'Areas', 'Areas achieved', 'Plans', 'Plans completed', 'Avg plan progress %']);

            foreach ($report['relationships'] as $row) {
                fputcsv($out, [
                    $row->mentor->name,
                    $row->mentee->name,
                    $row->course?->code ?? '—',
                    $row->status->label(),
                    $row->sessions->count(),
                    $row->improvementAreas->count(),
                    $row->improvementAreas->where('status', ImprovementAreaStatus::Achieved)->count(),
                    $row->actionPlans->count(),
                    $row->actionPlans->where('status', ActionPlanStatus::Completed)->count(),
                    $row->actionPlans->avg('progress_percent') !== null
                        ? round((float) $row->actionPlans->avg('progress_percent'), 1)
                        : '',
                ]);
            }

            fclose($out);
        }, $baseName.'.csv', ['Content-Type' => 'text/csv']);
    }

    /**
     * @param  array{relationships: \Illuminate\Support\Collection, summary: array, effectiveness: array}  $report
     */
    private function exportExcel(array $report, string $baseName): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Mentoring');
        $sheet->fromArray([
            ['Mentor', 'Mentee', 'Course', 'Status', 'Sessions', 'Areas', 'Areas achieved', 'Plans', 'Plans completed', 'Avg plan progress %'],
        ], null, 'A1');

        $rowNum = 2;
        foreach ($report['relationships'] as $row) {
            $sheet->fromArray([[
                $row->mentor->name,
                $row->mentee->name,
                $row->course?->code ?? '—',
                $row->status->label(),
                $row->sessions->count(),
                $row->improvementAreas->count(),
                $row->improvementAreas->where('status', ImprovementAreaStatus::Achieved)->count(),
                $row->actionPlans->count(),
                $row->actionPlans->where('status', ActionPlanStatus::Completed)->count(),
                $row->actionPlans->avg('progress_percent') !== null
                    ? round((float) $row->actionPlans->avg('progress_percent'), 1)
                    : '',
            ]], null, 'A'.$rowNum);
            $rowNum++;
        }

        $temp = tempnam(sys_get_temp_dir(), 'mentoring-');
        $path = $temp.'.xlsx';
        @unlink($temp);
        (new Xlsx($spreadsheet))->save($path);

        return response()->streamDownload(function () use ($path): void {
            readfile($path);
            @unlink($path);
        }, $baseName.'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * @param  array{relationships: \Illuminate\Support\Collection, summary: array, effectiveness: array}  $report
     */
    private function exportPdf(array $report, string $baseName): Response
    {
        $pdf = Pdf::loadView('hubs.mentoring-report-pdf', [
            'relationships' => $report['relationships'],
            'summary' => $report['summary'],
            'effectiveness' => $report['effectiveness'],
            'generatedAt' => now(),
        ])->setPaper('a4', 'landscape');

        return $pdf->download($baseName.'.pdf');
    }

    private function staffOnly(Request $request): void
    {
        $user = $request->user();
        if (! $user || (! $user->isAdmin() && ! $user->isLecturer())) {
            abort(403);
        }
    }
}
