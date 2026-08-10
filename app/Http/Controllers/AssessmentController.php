<?php

namespace App\Http\Controllers;

use App\Enums\AssessmentType;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Course;
use App\Models\User;
use App\Notifications\AssessmentPublishedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AssessmentController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorize('view', $course);

        $assessments = $course->assessments()
            ->when(request()->user()->isStudent(), fn ($q) => $q->where('is_published', true))
            ->when(! request()->user()->isStudent(), fn ($q) => $q->withCount([
                'attempts as submitted_count' => fn ($query) => $query->whereNotNull('submitted_at'),
            ]))
            ->latest()
            ->get();

        $attemptsByAssessment = collect();
        if (request()->user()->isStudent()) {
            $attemptsByAssessment = \App\Models\AssessmentAttempt::query()
                ->where('user_id', request()->user()->id)
                ->whereIn('assessment_id', $assessments->pluck('id'))
                ->get()
                ->keyBy('assessment_id');
        }

        $course->loadCount('students');

        return view('assessments.index', [
            'course' => $course->load('lecturer')->loadCount(['students', 'assignments', 'assessments']),
            'assessments' => $assessments,
            'attemptsByAssessment' => $attemptsByAssessment,
            'enrolledCount' => $course->students_count,
        ]);
    }

    public function create(Course $course): View
    {
        $this->authorize('create', Assessment::class);
        $this->authorize('update', $course);

        return view('assessments.create', [
            'course' => $course->load('lecturer')->loadCount(['students', 'assignments', 'assessments']),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('create', Assessment::class);
        $this->authorize('update', $course);

        $type = $request->input('type', AssessmentType::Manual->value);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'type' => ['required', Rule::enum(AssessmentType::class)],
            'external_url' => [
                Rule::requiredIf($type === AssessmentType::GoogleForm->value),
                'nullable',
                'url',
                'max:2048',
                'regex:/^https:\/\//i',
            ],
            'question_count' => [
                Rule::requiredIf($type === AssessmentType::Manual->value),
                'nullable',
                'integer',
                'min:1',
                'max:50',
            ],
            'marks_per_question' => [
                Rule::requiredIf($type === AssessmentType::Manual->value),
                'nullable',
                'integer',
                'min:1',
                'max:10',
            ],
            'max_score' => [
                Rule::requiredIf($type === AssessmentType::GoogleForm->value),
                'nullable',
                'integer',
                'min:1',
                'max:1000',
            ],
        ]);

        $assessmentType = $validated['type'] instanceof AssessmentType
            ? $validated['type']
            : AssessmentType::from($validated['type']);

        $isGoogleForm = $assessmentType === AssessmentType::GoogleForm;

        $assessment = $course->assessments()->create([
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'type' => $assessmentType,
            'external_url' => $isGoogleForm ? ($validated['external_url'] ?? null) : null,
            'due_at' => $validated['due_at'] ?? null,
            'question_count' => $isGoogleForm ? 0 : (int) $validated['question_count'],
            'marks_per_question' => $isGoogleForm ? 0 : (int) $validated['marks_per_question'],
            'max_score' => $isGoogleForm ? (int) $validated['max_score'] : null,
            'is_published' => false,
        ]);

        if ($assessment->isGoogleForm()) {
            return redirect()
                ->route('assessments.show', $assessment)
                ->with('success', 'Google Form assessment created. Publish when you are ready for students.');
        }

        return redirect()
            ->route('assessments.edit', $assessment)
            ->with('success', 'Assessment created. Add all quiz questions below, then publish.');
    }

    public function show(Assessment $assessment): View
    {
        $this->authorize('view', $assessment);

        $assessment->load(['course.lecturer', 'questions.options']);

        $user = request()->user();
        $attempt = null;
        $studentResults = collect();
        $resultsSummary = null;

        if ($user->isStudent()) {
            $attempt = $assessment->attempts()
                ->where('user_id', $user->id)
                ->first();
        } elseif ($user->can('viewResults', $assessment)) {
            $assessment->load(['course.students']);

            $attemptsByUser = $assessment->attempts()
                ->whereIn('user_id', $assessment->course->students->pluck('id'))
                ->get()
                ->keyBy('user_id');

            $submittedAttempts = $attemptsByUser->filter(fn ($item) => $item->isSubmitted());

            $studentResults = $assessment->course->students
                ->sortBy('name')
                ->values()
                ->map(fn ($student) => [
                    'student' => $student,
                    'attempt' => $attemptsByUser->get($student->id),
                ]);

            $resultsSummary = [
                'enrolled' => $studentResults->count(),
                'submitted' => $submittedAttempts->count(),
                'average' => $submittedAttempts->isNotEmpty()
                    ? round($submittedAttempts->avg('score'), 1)
                    : null,
            ];
        }

        return view('assessments.show', compact('assessment', 'attempt', 'studentResults', 'resultsSummary'));
    }

    public function updateScore(Request $request, Assessment $assessment, User $user): RedirectResponse
    {
        $this->authorize('viewResults', $assessment);

        if (! $assessment->isGoogleForm()) {
            abort(404);
        }

        if (! $assessment->is_published) {
            return back()->withErrors(['score' => 'Publish the assessment before recording scores.']);
        }

        if ($assessment->maxScore() < 1) {
            return back()->withErrors(['score' => 'Set total marks on the assessment before recording scores.']);
        }

        if (! $assessment->course->students()->where('users.id', $user->id)->exists()) {
            return back()->withErrors(['score' => 'Student is not enrolled on this course.']);
        }

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:'.$assessment->maxScore()],
        ]);

        AssessmentAttempt::query()->updateOrCreate(
            [
                'assessment_id' => $assessment->id,
                'user_id' => $user->id,
            ],
            [
                'score' => (int) round((float) $validated['score']),
                'max_score' => $assessment->maxScore(),
                'submitted_at' => now(),
            ]
        );

        return back()->with('success', 'Score saved for '.$user->name.'.');
    }

    public function bulkUpdateScores(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('viewResults', $assessment);

        if (! $assessment->isGoogleForm()) {
            abort(404);
        }

        if (! $assessment->is_published) {
            return back()->withErrors(['scores' => 'Publish the assessment before recording scores.']);
        }

        if ($assessment->maxScore() < 1) {
            return back()->withErrors(['scores' => 'Set total marks on the assessment before recording scores.']);
        }

        $max = $assessment->maxScore();

        $validated = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0', 'max:'.$max],
        ]);

        $enrolledIds = $assessment->course->students()->pluck('users.id')->map(fn ($id) => (int) $id)->all();
        $saved = 0;

        foreach ($validated['scores'] as $userId => $score) {
            if ($score === null || $score === '') {
                continue;
            }

            $userId = (int) $userId;
            if (! in_array($userId, $enrolledIds, true)) {
                continue;
            }

            AssessmentAttempt::query()->updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'user_id' => $userId,
                ],
                [
                    'score' => (int) round((float) $score),
                    'max_score' => $max,
                    'submitted_at' => now(),
                ]
            );
            $saved++;
        }

        if ($saved === 0) {
            return back()->withErrors(['scores' => 'Enter at least one score to save.']);
        }

        return back()->with('success', $saved === 1
            ? '1 score saved.'
            : $saved.' scores saved.');
    }

    public function clearScore(Assessment $assessment, User $user): RedirectResponse
    {
        $this->authorize('viewResults', $assessment);

        if (! $assessment->isGoogleForm()) {
            abort(404);
        }

        $attempt = $assessment->attempts()->where('user_id', $user->id)->first();
        if ($attempt) {
            $attempt->delete();
        }

        return back()->with('success', 'Score cleared for '.$user->name.'.');
    }

    public function edit(Request $request, Assessment $assessment): View
    {
        $this->authorize('update', $assessment);

        $assessment->load(['course.materials', 'questions.options']);

        $aiGeneration = null;
        if ($request->integer('ai')) {
            $aiGeneration = \App\Models\AiGeneration::query()
                ->where('id', $request->integer('ai'))
                ->where('user_id', $request->user()->id)
                ->first();
        }

        return view('assessments.edit', [
            'assessment' => $assessment,
            'aiGeneration' => $aiGeneration,
            'aiEnabled' => (bool) config('ai.enabled') && (bool) config('ai.features.quiz_from_materials'),
        ]);
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        if ($assessment->isGoogleForm()) {
            $validated = $request->validate([
                'title' => ['required', 'string', 'max:255'],
                'instructions' => ['nullable', 'string'],
                'due_at' => ['nullable', 'date'],
                'external_url' => ['required', 'url', 'max:2048', 'regex:/^https:\/\//i'],
                'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
            ]);

            $assessment->update([
                'title' => $validated['title'],
                'instructions' => $validated['instructions'] ?? null,
                'due_at' => $validated['due_at'] ?? null,
                'external_url' => $validated['external_url'],
                'max_score' => (int) $validated['max_score'],
            ]);

            return redirect()
                ->route('assessments.edit', $assessment)
                ->with('success', 'Google Form assessment updated.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'questions' => ['required', 'array', 'size:'.$assessment->question_count],
            'questions.*.prompt' => ['required', 'string'],
            'questions.*.options' => ['required', 'array', 'min:2', 'max:6'],
            'questions.*.options.*.label' => ['required', 'string'],
            'questions.*.correct' => ['required', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($assessment, $validated): void {
            $assessment->update([
                'title' => $validated['title'],
                'instructions' => $validated['instructions'] ?? null,
                'due_at' => $validated['due_at'] ?? null,
            ]);

            $assessment->questions()->each(fn ($q) => $q->options()->delete());
            $assessment->questions()->delete();

            foreach ($validated['questions'] as $index => $questionData) {
                $question = $assessment->questions()->create([
                    'position' => $index + 1,
                    'prompt' => $questionData['prompt'],
                ]);

                foreach ($questionData['options'] as $optIndex => $optionData) {
                    $question->options()->create([
                        'position' => $optIndex + 1,
                        'label' => $optionData['label'],
                        'is_correct' => ((int) $questionData['correct']) === ($optIndex + 1),
                    ]);
                }
            }
        });

        return redirect()
            ->route('assessments.edit', $assessment)
            ->with('success', 'Assessment questions saved.');
    }

    public function publish(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        if (! $assessment->isReadyToPublish()) {
            $message = $assessment->isGoogleForm()
                ? 'Add a Google Form URL and total marks before publishing.'
                : 'Add all '.$assessment->question_count.' questions with a correct answer before publishing.';

            return back()->withErrors(['publish' => $message]);
        }

        $wasPublished = $assessment->is_published;

        $assessment->update(['is_published' => true]);

        if (! $wasPublished) {
            $this->notifyStudents($assessment);
        }

        return redirect()
            ->route('assessments.show', $assessment)
            ->with('success', 'Assessment published to enrolled students.');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $this->authorize('delete', $assessment);

        $course = $assessment->course;
        $assessment->delete();

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Assessment removed.');
    }

    private function notifyStudents(Assessment $assessment): void
    {
        $assessment->load('course.students');

        foreach ($assessment->course->students as $student) {
            $student->notify(new AssessmentPublishedNotification($assessment));
        }
    }
}
