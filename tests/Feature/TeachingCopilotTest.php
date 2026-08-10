<?php

namespace Tests\Feature;

use App\Enums\AiGenerationFeature;
use App\Enums\AiGenerationStatus;
use App\Enums\AssessmentType;
use App\Enums\MaterialCategory;
use App\Enums\SubmissionStatus;
use App\Enums\SupportCaseStatus;
use App\Enums\UserRole;
use App\Models\AiGeneration;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\CourseMaterial;
use App\Models\StudentSupportCase;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeachingCopilotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'ai.enabled' => true,
            'ai.driver' => 'fake',
            'ai.sync' => true,
            'ai.api_key' => null,
            'ai.features.remediation_pack' => true,
            'ai.features.quiz_from_materials' => true,
            'ai.features.feedback_draft' => true,
            'ai.features.material_summary' => true,
            'ai.features.student_doubt' => true,
        ]);
    }

    public function test_student_cannot_generate_remediation_pack(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $case = StudentSupportCase::query()->create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'created_by' => $lecturer->id,
            'status' => SupportCaseStatus::Open,
            'reasons' => [['key' => 'manual', 'label' => 'Manual']],
            'baseline_metrics' => [],
            'identified_at' => now(),
        ]);

        $this->actingAs($student)
            ->post(route('ai.cases.remediation', $case))
            ->assertForbidden();
    }

    public function test_lecturer_can_generate_and_accept_remediation_pack(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        CourseMaterial::query()->create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'category' => MaterialCategory::Notes,
            'title' => 'Pipeline notes',
            'description' => 'ETL basics',
        ]);

        $case = StudentSupportCase::query()->create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'created_by' => $lecturer->id,
            'status' => SupportCaseStatus::Open,
            'reasons' => [['key' => 'low_avg', 'label' => 'Low average']],
            'baseline_metrics' => ['assignment_avg' => 40],
            'identified_at' => now(),
        ]);

        $this->actingAs($lecturer)
            ->post(route('ai.cases.remediation', $case))
            ->assertRedirect();

        $generation = AiGeneration::query()->firstOrFail();
        $this->assertSame(AiGenerationFeature::RemediationPack, $generation->feature);
        $this->assertSame(AiGenerationStatus::Ready, $generation->status);
        $this->assertNotEmpty($generation->output['agenda'] ?? []);

        $this->actingAs($lecturer)
            ->get(route('reports.at-risk.cases.show', ['case' => $case, 'ai' => $generation->id]))
            ->assertOk()
            ->assertSee('AI Teaching Copilot')
            ->assertSee('Mentoring agenda');

        $this->actingAs($lecturer)
            ->post(route('ai.generations.accept-agenda', $generation))
            ->assertRedirect();

        $this->assertGreaterThan(0, $case->actions()->count());

        $generation->update(['status' => AiGenerationStatus::Ready]);

        $this->actingAs($lecturer)
            ->post(route('ai.generations.accept-quiz', $generation))
            ->assertRedirect();

        $assessment = Assessment::query()->where('course_id', $course->id)->first();
        $this->assertNotNull($assessment);
        $this->assertSame($assessment->question_count, $assessment->questions()->count());
    }

    public function test_quiz_from_materials_and_feedback_draft(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $material = CourseMaterial::query()->create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'category' => MaterialCategory::Notes,
            'title' => 'Week 1 notes',
            'description' => 'Intro concepts',
        ]);

        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Practice quiz',
            'type' => AssessmentType::Manual,
            'question_count' => 3,
            'marks_per_question' => 1,
            'is_published' => false,
        ]);

        $this->actingAs($lecturer)
            ->post(route('ai.assessments.quiz', $assessment), [
                'material_ids' => [$material->id],
            ])
            ->assertRedirect();

        $quizGen = AiGeneration::query()
            ->where('feature', AiGenerationFeature::QuizFromMaterials)
            ->firstOrFail();

        $this->assertSame(AiGenerationStatus::Ready, $quizGen->status);

        $this->actingAs($lecturer)
            ->post(route('ai.generations.accept-assessment-quiz', $quizGen))
            ->assertRedirect();

        $this->assertSame(3, $assessment->fresh()->questions()->count());

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Lab',
            'is_published' => true,
        ]);

        $submission = Submission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/a.pdf',
            'file_name' => 'a.pdf',
            'status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
            'notes' => 'First attempt',
        ]);

        $this->actingAs($lecturer)
            ->post(route('ai.submissions.feedback', $submission))
            ->assertRedirect();

        $feedbackGen = AiGeneration::query()
            ->where('feature', AiGenerationFeature::FeedbackDraft)
            ->firstOrFail();

        $this->actingAs($lecturer)
            ->get(route('submissions.show', ['submission' => $submission, 'ai' => $feedbackGen->id]))
            ->assertOk()
            ->assertSee('AI draft feedback');
    }

    public function test_material_summary_and_student_doubt_assist(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $material = CourseMaterial::query()->create([
            'course_id' => $course->id,
            'uploaded_by' => $lecturer->id,
            'category' => MaterialCategory::Notes,
            'title' => 'Syllabus overview',
            'description' => 'Course expectations and topics',
        ]);

        $this->actingAs($lecturer)
            ->post(route('ai.materials.summary', $material))
            ->assertRedirect();

        $summary = AiGeneration::query()
            ->where('feature', AiGenerationFeature::MaterialSummary)
            ->firstOrFail();
        $this->assertSame(AiGenerationStatus::Ready, $summary->status);

        $this->actingAs($student)
            ->post(route('ai.courses.doubt', $course), [
                'question' => 'What should I read first?',
            ])
            ->assertRedirect();

        $doubt = AiGeneration::query()
            ->where('feature', AiGenerationFeature::StudentDoubt)
            ->firstOrFail();

        $this->assertSame(AiGenerationStatus::Ready, $doubt->status);
        $this->assertNotEmpty($doubt->output['answer'] ?? null);

        $this->actingAs($student)
            ->get(route('questions.create', ['course' => $course->id, 'ai' => $doubt->id]))
            ->assertOk()
            ->assertSee('AI doubt assist')
            ->assertSee('Suggested answer');
    }

    private function makeLecturer(): User
    {
        return User::factory()->create(['role' => UserRole::Lecturer]);
    }

    private function makeStudent(): User
    {
        return User::factory()->create([
            'role' => UserRole::Student,
            'student_id' => 'DS'.fake()->unique()->numerify('######'),
        ]);
    }

    private function makeCourse(?User $lecturer = null, string $code = 'AI101'): Course
    {
        $lecturer ??= $this->makeLecturer();

        return Course::query()->create([
            'code' => $code,
            'title' => 'AI Course '.$code,
            'lecturer_id' => $lecturer->id,
            'is_active' => true,
        ]);
    }
}
