<?php

namespace Tests\Feature;

use App\Enums\AssessmentType;
use App\Enums\SubmissionStatus;
use App\Enums\SupportActionType;
use App\Enums\SupportCaseStatus;
use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\AssessmentAttempt;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\StudentSupportAction;
use App\Models\StudentSupportCase;
use App\Models\Submission;
use App\Models\User;
use App\Support\WeakStudentReport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeakStudentAtRiskTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_cannot_access_at_risk_report(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->get(route('reports.at-risk'))
            ->assertForbidden();
    }

    public function test_critical_low_average_flags_student(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent('Weak Avg');
        $course = $this->makeCourse($lecturer, 'ATR101');
        $course->students()->attach($student->id);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Lab 1',
            'is_published' => true,
            'due_at' => now()->addDay(),
        ]);

        Submission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/a.pdf',
            'file_name' => 'a.pdf',
            'status' => SubmissionStatus::Reviewed,
            'score' => 45,
            'submitted_at' => now(),
            'reviewed_at' => now(),
            'reviewed_by' => $lecturer->id,
        ]);

        $report = WeakStudentReport::build($course);

        $this->assertSame(1, $report['kpis']['flagged']);
        $this->assertTrue($report['flagged']->contains(fn ($row) => $row['student']->id === $student->id));
        $this->assertContains('low_avg', $report['flagged']->first()['reason_keys']);
    }

    public function test_critical_missing_work_flags_student(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent('Missing Work');
        $course = $this->makeCourse($lecturer, 'ATR102');
        $course->students()->attach($student->id);

        foreach (['A1', 'A2'] as $title) {
            Assignment::query()->create([
                'course_id' => $course->id,
                'created_by' => $lecturer->id,
                'title' => $title,
                'is_published' => true,
                'due_at' => now()->subDays(2),
            ]);
        }

        $report = WeakStudentReport::build($course);
        $row = $report['flagged']->firstWhere(fn ($r) => $r['student']->id === $student->id);

        $this->assertNotNull($row);
        $this->assertContains('missing_work', $row['reason_keys']);
    }

    public function test_single_non_critical_rule_does_not_flag(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent('One Late');
        $course = $this->makeCourse($lecturer, 'ATR103');
        $course->students()->attach($student->id);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Lab',
            'is_published' => true,
            'due_at' => now()->addDay(),
        ]);

        // Only one late — chronic late needs ≥2
        Submission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/late.pdf',
            'file_name' => 'late.pdf',
            'status' => SubmissionStatus::Late,
            'score' => 80,
            'submitted_at' => now(),
        ]);

        $report = WeakStudentReport::build($course);

        $this->assertFalse($report['flagged']->contains(fn ($row) => $row['student']->id === $student->id));
    }

    public function test_two_non_critical_rules_flag_student(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent('Two Rules');
        $course = $this->makeCourse($lecturer, 'ATR104');
        $course->students()->attach($student->id);

        $a1 = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Lab 1',
            'is_published' => true,
            'due_at' => now()->subDay(),
        ]);
        $a2 = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Lab 2',
            'is_published' => true,
            'due_at' => now()->addDay(),
        ]);

        Submission::query()->create([
            'assignment_id' => $a1->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/l1.pdf',
            'file_name' => 'l1.pdf',
            'status' => SubmissionStatus::Late,
            'score' => 75,
            'submitted_at' => now(),
        ]);
        Submission::query()->create([
            'assignment_id' => $a2->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/l2.pdf',
            'file_name' => 'l2.pdf',
            'status' => SubmissionStatus::Late,
            'score' => 78,
            'submitted_at' => now(),
        ]);

        $assessment = Assessment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Quiz',
            'type' => AssessmentType::Manual,
            'question_count' => 10,
            'marks_per_question' => 1,
            'is_published' => true,
        ]);

        AssessmentAttempt::query()->create([
            'assessment_id' => $assessment->id,
            'user_id' => $student->id,
            'score' => 4,
            'max_score' => 10,
            'submitted_at' => now(),
        ]);

        $report = WeakStudentReport::build($course);
        $row = $report['flagged']->firstWhere(fn ($r) => $r['student']->id === $student->id);

        $this->assertNotNull($row);
        $this->assertContains('chronic_late', $row['reason_keys']);
        $this->assertContains('low_quiz', $row['reason_keys']);
        $this->assertGreaterThanOrEqual(2, $row['risk_score']);
    }

    public function test_at_risk_report_page_and_exports(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent('Export Me');
        $course = $this->makeCourse($lecturer, 'ATR105');
        $course->students()->attach($student->id);

        Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Overdue 1',
            'is_published' => true,
            'due_at' => now()->subDays(3),
        ]);
        Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Overdue 2',
            'is_published' => true,
            'due_at' => now()->subDays(2),
        ]);

        $this->actingAs($lecturer)
            ->get(route('reports.at-risk', ['course' => $course->id]))
            ->assertOk()
            ->assertSee('Course at-risk')
            ->assertSee($student->name)
            ->assertSee('Missing');

        $csv = $this->actingAs($lecturer)
            ->get(route('reports.at-risk.export', ['course' => $course->id, 'format' => 'csv']));
        $csv->assertOk();
        $this->assertStringContainsString($student->name, $csv->streamedContent());

        $this->actingAs($lecturer)
            ->get(route('reports.at-risk.export', ['course' => $course->id, 'format' => 'xlsx']))
            ->assertOk();

        $this->actingAs($lecturer)
            ->get(route('reports.at-risk.export', ['course' => $course->id, 'format' => 'pdf']))
            ->assertOk();
    }

    public function test_lecturer_can_open_case_log_action_and_resolve(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent('Case Student');
        $course = $this->makeCourse($lecturer, 'ATR106');
        $course->students()->attach($student->id);

        Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Missed A',
            'is_published' => true,
            'due_at' => now()->subDays(4),
        ]);
        Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Missed B',
            'is_published' => true,
            'due_at' => now()->subDays(3),
        ]);

        $this->actingAs($lecturer)
            ->post(route('reports.at-risk.cases.store'), [
                'course_id' => $course->id,
                'user_id' => $student->id,
            ])
            ->assertRedirect();

        $case = StudentSupportCase::query()->firstOrFail();
        $this->assertSame(SupportCaseStatus::Open, $case->status);
        $this->assertNotNull($case->baseline_metrics);

        $this->actingAs($lecturer)
            ->post(route('reports.at-risk.cases.actions.store', $case), [
                'type' => SupportActionType::Mentoring->value,
                'title' => 'Catch-up mentoring',
                'notes' => 'Reviewed missing work plan',
                'conducted_at' => now()->toDateTimeString(),
            ])
            ->assertRedirect();

        $case->refresh();
        $this->assertSame(SupportCaseStatus::InProgress, $case->status);
        $this->assertSame(1, $case->actions()->count());

        $this->actingAs($lecturer)
            ->patch(route('reports.at-risk.cases.update', $case), [
                'status' => SupportCaseStatus::Resolved->value,
            ])
            ->assertRedirect();

        $case->refresh();
        $this->assertSame(SupportCaseStatus::Resolved, $case->status);
        $this->assertNotNull($case->resolved_at);
        $this->assertNotNull($case->latest_metrics);
    }

    public function test_other_lecturer_cannot_view_case(): void
    {
        $owner = $this->makeLecturer();
        $other = $this->makeLecturer();
        $student = $this->makeStudent('Scoped');
        $course = $this->makeCourse($owner, 'ATR107');
        $course->students()->attach($student->id);

        $case = StudentSupportCase::query()->create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'created_by' => $owner->id,
            'status' => SupportCaseStatus::Open,
            'reasons' => [['key' => 'manual', 'label' => 'Manual']],
            'baseline_metrics' => [],
            'identified_at' => now(),
        ]);

        $this->actingAs($other)
            ->get(route('reports.at-risk.cases.show', $case))
            ->assertForbidden();

        $this->actingAs($other)
            ->post(route('reports.at-risk.cases.actions.store', $case), [
                'type' => SupportActionType::Support->value,
                'title' => 'Nope',
            ])
            ->assertForbidden();
    }

    public function test_action_can_be_deleted_by_course_lecturer(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent('Delete Action');
        $course = $this->makeCourse($lecturer, 'ATR108');
        $course->students()->attach($student->id);

        $case = StudentSupportCase::query()->create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'created_by' => $lecturer->id,
            'status' => SupportCaseStatus::InProgress,
            'reasons' => [['key' => 'manual', 'label' => 'Manual']],
            'baseline_metrics' => [],
            'identified_at' => now(),
        ]);

        $action = StudentSupportAction::query()->create([
            'student_support_case_id' => $case->id,
            'created_by' => $lecturer->id,
            'type' => SupportActionType::Strategy,
            'title' => 'Study plan',
            'conducted_at' => now(),
        ]);

        $this->actingAs($lecturer)
            ->delete(route('reports.at-risk.actions.destroy', $action))
            ->assertRedirect(route('reports.at-risk.cases.show', $case));

        $this->assertDatabaseMissing('student_support_actions', ['id' => $action->id]);
    }

    public function test_manual_case_allowed_for_non_flagged_student(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent('Strong Student');
        $course = $this->makeCourse($lecturer, 'ATR109');
        $course->students()->attach($student->id);

        Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Only one',
            'is_published' => true,
            'due_at' => now()->addWeek(),
        ]);

        $this->actingAs($lecturer)
            ->post(route('reports.at-risk.cases.store'), [
                'course_id' => $course->id,
                'user_id' => $student->id,
            ])
            ->assertRedirect();

        $case = StudentSupportCase::query()->firstOrFail();
        $this->assertSame('manual', $case->reasons[0]['key'] ?? null);
    }

    private function makeLecturer(): User
    {
        return User::factory()->create(['role' => UserRole::Lecturer]);
    }

    private function makeStudent(string $name = 'Student'): User
    {
        return User::factory()->create([
            'role' => UserRole::Student,
            'name' => $name,
            'student_id' => 'DS'.fake()->unique()->numerify('######'),
        ]);
    }

    private function makeCourse(?User $lecturer = null, string $code = 'ATR100'): Course
    {
        $lecturer ??= $this->makeLecturer();

        return Course::query()->create([
            'code' => $code,
            'title' => 'At Risk '.$code,
            'lecturer_id' => $lecturer->id,
            'is_active' => true,
        ]);
    }
}
