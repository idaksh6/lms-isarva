<?php

namespace Tests\Feature;

use App\Enums\AssessmentType;
use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Notifications\AssignmentPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class BulkContentImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_download_quiz_template(): void
    {
        $lecturer = $this->makeLecturer();

        $response = $this->actingAs($lecturer)
            ->get(route('imports.templates.download', ['kind' => 'quiz', 'format' => 'txt']));

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('LMS_IMPORT: QUIZ', $response->streamedContent());
    }

    public function test_lecturer_can_import_quiz_questions_from_txt_template(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);
        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Imported quiz',
            'type' => AssessmentType::Manual,
            'question_count' => 5,
            'marks_per_question' => 1,
            'is_published' => false,
        ]);

        $contents = file_get_contents(base_path('samples/bulk-import/quiz-template.txt'));
        $file = UploadedFile::fake()->createWithContent('quiz.txt', $contents);

        $this->actingAs($lecturer)
            ->post(route('assessments.questions.import', $assessment), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('assessments.edit', $assessment))
            ->assertSessionHas('success');

        $assessment->refresh();
        $this->assertSame(2, $assessment->question_count);
        $this->assertSame(2, $assessment->questions()->count());
        $this->assertTrue($assessment->questions()->first()->options()->where('is_correct', true)->exists());
    }

    public function test_lecturer_can_import_assignments_from_txt_template(): void
    {
        Notification::fake();

        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $contents = file_get_contents(base_path('samples/bulk-import/assignments-template.txt'));
        $file = UploadedFile::fake()->createWithContent('assignments.txt', $contents);

        $this->actingAs($lecturer)
            ->post(route('courses.assignments.import', $course), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('courses.show', $course))
            ->assertSessionHas('success');

        $this->assertSame(2, Assignment::query()->where('course_id', $course->id)->count());
        $this->assertSame(1, Assignment::query()->where('course_id', $course->id)->where('is_published', true)->count());
        Notification::assertSentTo($student, AssignmentPublishedNotification::class);
    }

    public function test_student_cannot_access_import_templates(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->get(route('imports.templates'))
            ->assertForbidden();
    }

    public function test_wrong_template_type_is_rejected_for_quiz_import(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);
        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Quiz',
            'type' => AssessmentType::Manual,
            'question_count' => 2,
            'marks_per_question' => 1,
            'is_published' => false,
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'assignments.txt',
            file_get_contents(base_path('samples/bulk-import/assignments-template.txt'))
        );

        $this->actingAs($lecturer)
            ->from(route('assessments.edit', $assessment))
            ->post(route('assessments.questions.import', $assessment), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('assessments.edit', $assessment))
            ->assertSessionHasErrors('import_file');
    }

    public function test_lecturer_can_download_quiz_excel_template(): void
    {
        $lecturer = $this->makeLecturer();

        $response = $this->actingAs($lecturer)
            ->get(route('imports.templates.download', ['kind' => 'quiz', 'format' => 'xlsx']));

        $response->assertOk();
        $this->assertStringContainsString('attachment', (string) $response->headers->get('content-disposition'));
        $this->assertStringContainsString('.xlsx', (string) $response->headers->get('content-disposition'));
        $this->assertNotSame('', $response->streamedContent());
    }

    public function test_lecturer_can_import_quiz_questions_from_excel_template(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);
        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Excel quiz',
            'type' => AssessmentType::Manual,
            'question_count' => 5,
            'marks_per_question' => 1,
            'is_published' => false,
        ]);

        $path = $this->makeQuizXlsx();
        $file = new UploadedFile($path, 'quiz.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($lecturer)
            ->post(route('assessments.questions.import', $assessment), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('assessments.edit', $assessment))
            ->assertSessionHas('success');

        $assessment->refresh();
        $this->assertSame(2, $assessment->question_count);
        $this->assertSame(2, $assessment->questions()->count());
        @unlink($path);
    }

    public function test_lecturer_can_import_assignments_from_excel_template(): void
    {
        Notification::fake();

        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $path = $this->makeAssignmentsXlsx();
        $file = new UploadedFile($path, 'assignments.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->actingAs($lecturer)
            ->post(route('courses.assignments.import', $course), [
                'import_file' => $file,
            ])
            ->assertRedirect(route('courses.show', $course))
            ->assertSessionHas('success');

        $this->assertSame(2, Assignment::query()->where('course_id', $course->id)->count());
        @unlink($path);
    }

    private function makeQuizXlsx(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['prompt', 'option_a', 'option_b', 'option_c', 'option_d', 'answer'], null, 'A1');
        $sheet->fromArray([
            ['Capital of France?', 'Berlin', 'Paris', 'Rome', 'Madrid', 'B'],
            ['2 + 2?', '3', '4', '5', '6', 'B'],
        ], null, 'A2');

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lms-quiz-'.uniqid('', true).'.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);

        return $path;
    }

    private function makeAssignmentsXlsx(): string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['title', 'instructions', 'delivery', 'drop_folder_url', 'due', 'publish'], null, 'A1');
        $sheet->fromArray([
            ['Lab A', 'Do the lab', 'file', '', '2026-10-01 23:59', 'no'],
            ['Essay B', 'Write essay', 'file', '', '2026-10-08 23:59', 'no'],
        ], null, 'A2');

        $path = sys_get_temp_dir().DIRECTORY_SEPARATOR.'lms-asn-'.uniqid('', true).'.xlsx';
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save($path);

        return $path;
    }

    private function makeLecturer(): User
    {
        return User::factory()->create(['role' => UserRole::Lecturer]);
    }

    private function makeStudent(): User
    {
        return User::factory()->create(['role' => UserRole::Student]);
    }

    private function makeCourse(?User $lecturer = null): Course
    {
        $lecturer ??= $this->makeLecturer();

        return Course::query()->create([
            'title' => 'Import Course',
            'code' => 'IMP'.random_int(100, 999),
            'lecturer_id' => $lecturer->id,
        ]);
    }
}
