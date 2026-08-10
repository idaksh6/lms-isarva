<?php

namespace App\Services\Ai;

use App\Enums\AiGenerationFeature;
use App\Enums\AiGenerationStatus;
use App\Enums\AssessmentType;
use App\Enums\SupportActionType;
use App\Models\AiGeneration;
use App\Models\Assessment;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\StudentSupportCase;
use App\Models\Submission;
use App\Models\User;
use App\Services\Ai\ContextBuilders\AtRiskContextBuilder;
use App\Services\Ai\ContextBuilders\MaterialsContextBuilder;
use App\Services\Ai\ContextBuilders\SubmissionContextBuilder;
use App\Services\Ai\Generators\FeedbackDrafter;
use App\Services\Ai\Generators\MaterialSummarizer;
use App\Services\Ai\Generators\McqGenerator;
use App\Services\Ai\Generators\RemediationPackGenerator;
use App\Services\Ai\Generators\StudentDoubtAssistant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class AiGenerationService
{
    public function __construct(
        private AtRiskContextBuilder $atRiskContext,
        private MaterialsContextBuilder $materialsContext,
        private SubmissionContextBuilder $submissionContext,
        private RemediationPackGenerator $remediationPackGenerator,
        private McqGenerator $mcqGenerator,
        private FeedbackDrafter $feedbackDrafter,
        private MaterialSummarizer $materialSummarizer,
        private StudentDoubtAssistant $studentDoubtAssistant,
    ) {}

    public function assertEnabled(AiGenerationFeature $feature): void
    {
        if (! config('ai.enabled')) {
            throw ValidationException::withMessages([
                'ai' => 'AI Teaching Copilot is disabled.',
            ]);
        }

        if (! $feature->isEnabled()) {
            throw ValidationException::withMessages([
                'ai' => $feature->label().' is disabled.',
            ]);
        }
    }

    public function assertRateLimit(User $user): void
    {
        $key = 'ai-generate:'.$user->id;
        $max = max(1, (int) config('ai.rate_limit_per_hour', 30));

        if (RateLimiter::tooManyAttempts($key, $max)) {
            throw ValidationException::withMessages([
                'ai' => 'AI rate limit reached. Try again later.',
            ]);
        }

        RateLimiter::hit($key, 3600);
    }

    public function createPending(
        User $user,
        AiGenerationFeature $feature,
        array $inputSnapshot,
        ?Course $course = null,
        ?Model $subject = null,
    ): AiGeneration {
        $this->assertEnabled($feature);
        $this->assertRateLimit($user);

        return AiGeneration::query()->create([
            'course_id' => $course?->id,
            'user_id' => $user->id,
            'feature' => $feature,
            'status' => AiGenerationStatus::Pending,
            'prompt_hash' => hash('sha256', json_encode($inputSnapshot)),
            'input_snapshot' => $inputSnapshot,
            'subject_type' => $subject ? $subject::class : null,
            'subject_id' => $subject?->getKey(),
        ]);
    }

    public function run(AiGeneration $generation): AiGeneration
    {
        try {
            $result = match ($generation->feature) {
                AiGenerationFeature::RemediationPack => $this->runRemediationPack($generation),
                AiGenerationFeature::QuizFromMaterials => $this->runQuizFromMaterials($generation),
                AiGenerationFeature::FeedbackDraft => $this->runFeedbackDraft($generation),
                AiGenerationFeature::MaterialSummary => $this->runMaterialSummary($generation),
                AiGenerationFeature::StudentDoubt => $this->runStudentDoubt($generation),
            };

            $generation->update([
                'status' => AiGenerationStatus::Ready,
                'output' => $result['data'],
                'prompt_tokens' => $result['prompt_tokens'],
                'completion_tokens' => $result['completion_tokens'],
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            $generation->update([
                'status' => AiGenerationStatus::Failed,
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
            ]);
        }

        return $generation->refresh();
    }

    /**
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    private function runRemediationPack(AiGeneration $generation): array
    {
        $case = StudentSupportCase::query()->findOrFail($generation->subject_id);
        $context = $this->atRiskContext->build($case);

        return $this->remediationPackGenerator->generate($context);
    }

    /**
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    private function runQuizFromMaterials(AiGeneration $generation): array
    {
        $course = Course::query()->findOrFail($generation->course_id);
        $materialIds = $generation->input_snapshot['material_ids'] ?? [];
        $count = (int) ($generation->input_snapshot['count'] ?? $generation->subject?->question_count ?? 5);

        $materials = CourseMaterial::query()
            ->where('course_id', $course->id)
            ->whereIn('id', $materialIds)
            ->get();

        if ($materials->isEmpty()) {
            throw new RuntimeException('Select at least one course material.');
        }

        $context = $this->materialsContext->build($course, $materials);

        return $this->mcqGenerator->generate($context, $count);
    }

    /**
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    private function runFeedbackDraft(AiGeneration $generation): array
    {
        $submission = Submission::query()->findOrFail($generation->subject_id);
        $context = $this->submissionContext->build($submission);

        return $this->feedbackDrafter->generate($context);
    }

    /**
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    private function runMaterialSummary(AiGeneration $generation): array
    {
        $material = CourseMaterial::query()->findOrFail($generation->subject_id);
        $context = $this->materialsContext->build($material->course, collect([$material]));

        return $this->materialSummarizer->generate($context);
    }

    /**
     * @return array{data: array<string, mixed>, prompt_tokens: int|null, completion_tokens: int|null}
     */
    private function runStudentDoubt(AiGeneration $generation): array
    {
        $course = Course::query()->findOrFail($generation->course_id);
        $question = (string) ($generation->input_snapshot['question'] ?? '');
        $materials = $course->materials()->latest()->limit(8)->get();
        $context = $this->materialsContext->build($course, $materials);

        return $this->studentDoubtAssistant->generate($context, $question);
    }

    public function acceptRemediationAgenda(AiGeneration $generation, StudentSupportCase $case, User $actor): int
    {
        $this->ensureReadyOwned($generation, $actor, AiGenerationFeature::RemediationPack);
        $agenda = $generation->output['agenda'] ?? [];
        $created = 0;

        foreach ($agenda as $item) {
            $typeValue = $item['type'] ?? 'strategy';
            $type = SupportActionType::tryFrom($typeValue) ?? SupportActionType::Strategy;

            $case->actions()->create([
                'created_by' => $actor->id,
                'type' => $type,
                'title' => mb_substr((string) ($item['title'] ?? 'AI suggested action'), 0, 255),
                'notes' => $item['notes'] ?? null,
                'conducted_at' => now(),
            ]);
            $created++;
        }

        if ($created > 0) {
            $generation->markAccepted();
        }

        return $created;
    }

    public function acceptRemediationQuiz(AiGeneration $generation, StudentSupportCase $case, User $actor): Assessment
    {
        $this->ensureReadyOwned($generation, $actor, AiGenerationFeature::RemediationPack);
        $quiz = $generation->output['quiz'] ?? [];

        if (count($quiz) < 1) {
            throw ValidationException::withMessages(['ai' => 'No quiz questions to accept.']);
        }

        $count = count($quiz);

        return DB::transaction(function () use ($case, $actor, $quiz, $count, $generation) {
            $assessment = $case->course->assessments()->create([
                'created_by' => $actor->id,
                'title' => 'Remediation quiz — '.$case->student->name,
                'instructions' => $generation->output['study_brief'] ?? 'AI-generated remediation practice quiz. Review before publishing.',
                'type' => AssessmentType::Manual,
                'question_count' => $count,
                'marks_per_question' => 1,
                'is_published' => false,
            ]);

            foreach ($quiz as $index => $questionData) {
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

            $generation->markAccepted();

            return $assessment;
        });
    }

    public function acceptQuizIntoAssessment(AiGeneration $generation, Assessment $assessment, User $actor): void
    {
        $this->ensureReadyOwned($generation, $actor, AiGenerationFeature::QuizFromMaterials);

        $questions = $generation->output['questions'] ?? [];
        if (count($questions) !== $assessment->question_count) {
            throw ValidationException::withMessages([
                'ai' => 'Generated question count must match assessment question count ('.$assessment->question_count.').',
            ]);
        }

        DB::transaction(function () use ($assessment, $questions, $generation) {
            $assessment->questions()->each(fn ($q) => $q->options()->delete());
            $assessment->questions()->delete();

            foreach ($questions as $index => $questionData) {
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

            $generation->markAccepted();
        });
    }

    private function ensureReadyOwned(AiGeneration $generation, User $actor, AiGenerationFeature $feature): void
    {
        if ($generation->feature !== $feature) {
            abort(404);
        }

        if ($generation->user_id !== $actor->id && ! $actor->isAdmin()) {
            abort(403);
        }

        if (! $generation->isReady() && $generation->status !== AiGenerationStatus::Accepted) {
            throw ValidationException::withMessages(['ai' => 'Generation is not ready.']);
        }
    }
}
