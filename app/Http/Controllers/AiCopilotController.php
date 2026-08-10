<?php

namespace App\Http\Controllers;

use App\Enums\AiGenerationFeature;
use App\Enums\AiGenerationStatus;
use App\Jobs\ProcessAiGeneration;
use App\Models\AiGeneration;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\StudentSupportCase;
use App\Models\Submission;
use App\Services\Ai\AiGenerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiCopilotController extends Controller
{
    public function __construct(private AiGenerationService $ai) {}

    public function show(AiGeneration $generation): JsonResponse|View|RedirectResponse
    {
        $this->authorize('view', $generation);

        if (request()->wantsJson()) {
            return response()->json([
                'id' => $generation->id,
                'status' => $generation->status->value,
                'feature' => $generation->feature->value,
                'output' => $generation->output,
                'error_message' => $generation->error_message,
            ]);
        }

        return back();
    }

    public function discard(AiGeneration $generation): RedirectResponse
    {
        $this->authorize('view', $generation);
        $generation->markDiscarded();

        return back()->with('success', 'AI draft discarded.');
    }

    public function generateRemediationPack(Request $request, StudentSupportCase $case): RedirectResponse
    {
        $this->authorize('update', $case);
        $this->ai->assertEnabled(AiGenerationFeature::RemediationPack);

        $generation = $this->ai->createPending(
            $request->user(),
            AiGenerationFeature::RemediationPack,
            ['case_id' => $case->id],
            $case->course,
            $case,
        );

        $this->dispatchOrRun($generation);

        return redirect()
            ->route('reports.at-risk.cases.show', ['case' => $case, 'ai' => $generation->id])
            ->with('success', 'Remediation pack generation started.');
    }

    public function acceptRemediationAgenda(Request $request, AiGeneration $generation): RedirectResponse
    {
        $this->authorize('view', $generation);
        $case = StudentSupportCase::query()->findOrFail($generation->subject_id);
        $this->authorize('update', $case);

        $count = $this->ai->acceptRemediationAgenda($generation, $case, $request->user());

        return redirect()
            ->route('reports.at-risk.cases.show', $case)
            ->with('success', $count.' mentoring actions added from AI agenda.');
    }

    public function acceptRemediationQuiz(Request $request, AiGeneration $generation): RedirectResponse
    {
        $this->authorize('view', $generation);
        $case = StudentSupportCase::query()->findOrFail($generation->subject_id);
        $this->authorize('update', $case);

        $assessment = $this->ai->acceptRemediationQuiz($generation, $case, $request->user());

        return redirect()
            ->route('assessments.edit', $assessment)
            ->with('success', 'Draft remediation quiz created. Review questions before publishing.');
    }

    public function generateQuizFromMaterials(Request $request, Assessment $assessment): RedirectResponse
    {
        $this->authorize('update', $assessment);

        if (! $assessment->isManual()) {
            return back()->withErrors(['ai' => 'AI quiz generation is only available for manual assessments.']);
        }

        $validated = $request->validate([
            'material_ids' => ['required', 'array', 'min:1'],
            'material_ids.*' => ['integer', 'exists:course_materials,id'],
        ]);

        $ownedIds = CourseMaterial::query()
            ->where('course_id', $assessment->course_id)
            ->whereIn('id', $validated['material_ids'])
            ->pluck('id')
            ->all();

        if ($ownedIds === []) {
            return back()->withErrors(['material_ids' => 'Select materials from this course.']);
        }

        $generation = $this->ai->createPending(
            $request->user(),
            AiGenerationFeature::QuizFromMaterials,
            [
                'assessment_id' => $assessment->id,
                'material_ids' => $ownedIds,
                'count' => $assessment->question_count,
            ],
            $assessment->course,
            $assessment,
        );

        $this->dispatchOrRun($generation);

        return redirect()
            ->route('assessments.edit', ['assessment' => $assessment, 'ai' => $generation->id])
            ->with('success', 'Quiz draft generation started.');
    }

    public function acceptQuizFromMaterials(Request $request, AiGeneration $generation): RedirectResponse
    {
        $this->authorize('view', $generation);
        $assessment = Assessment::query()->findOrFail($generation->subject_id);
        $this->authorize('update', $assessment);

        $this->ai->acceptQuizIntoAssessment($generation, $assessment, $request->user());

        return redirect()
            ->route('assessments.edit', $assessment)
            ->with('success', 'AI questions applied. Review and save if needed.');
    }

    public function generateFeedbackDraft(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorize('review', $submission);

        $generation = $this->ai->createPending(
            $request->user(),
            AiGenerationFeature::FeedbackDraft,
            ['submission_id' => $submission->id],
            $submission->assignment->course,
            $submission,
        );

        $this->dispatchOrRun($generation);

        return redirect()
            ->route('submissions.show', ['submission' => $submission, 'ai' => $generation->id])
            ->with('success', 'Feedback draft generation started.');
    }

    public function generateMaterialSummary(Request $request, CourseMaterial $material): RedirectResponse
    {
        $this->authorize('update', $material);

        $generation = $this->ai->createPending(
            $request->user(),
            AiGenerationFeature::MaterialSummary,
            ['material_id' => $material->id],
            $material->course,
            $material,
        );

        $this->dispatchOrRun($generation);

        return redirect()
            ->route('courses.materials.index', ['course' => $material->course_id, 'ai' => $generation->id, 'material' => $material->id])
            ->with('success', 'Material summary generation started.');
    }

    public function generateStudentDoubt(Request $request, Course $course): RedirectResponse
    {
        $user = $request->user();

        if ($user->isStudent()) {
            abort_unless(
                $course->is_active && $course->students()->where('users.id', $user->id)->exists(),
                403
            );
        } else {
            $this->authorize('view', $course);
        }

        $validated = $request->validate([
            'question' => ['required', 'string', 'max:2000'],
        ]);

        $generation = $this->ai->createPending(
            $user,
            AiGenerationFeature::StudentDoubt,
            [
                'course_id' => $course->id,
                'question' => $validated['question'],
            ],
            $course,
            $course,
        );

        $this->dispatchOrRun($generation);

        return redirect()
            ->route('questions.create', ['course' => $course->id, 'ai' => $generation->id])
            ->with('success', 'Doubt assist ready — review the draft before posting.');
    }

    private function dispatchOrRun(AiGeneration $generation): void
    {
        if (config('ai.sync') || config('ai.driver') === 'fake') {
            $this->ai->run($generation);

            return;
        }

        ProcessAiGeneration::dispatch($generation->id);
    }
}
