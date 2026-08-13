<?php

namespace Tests\Feature;

use App\Enums\ActionPlanStatus;
use App\Enums\ImprovementAreaStatus;
use App\Enums\MentoringStatus;
use App\Enums\UserRole;
use App\Models\Course;
use App\Models\MentoringRelationship;
use App\Models\User;
use App\Notifications\MentoringAssignedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class MentoringModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_lecturer_can_create_mentoring_relationship_and_log_session(): void
    {
        Notification::fake();

        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $this->actingAs($lecturer)
            ->post(route('mentoring.store'), [
                'mentor_id' => $lecturer->id,
                'mentee_id' => $student->id,
                'course_id' => $course->id,
                'goals' => 'Improve assignment quality',
            ])
            ->assertRedirect();

        $relationship = MentoringRelationship::query()->firstOrFail();
        $this->assertSame(MentoringStatus::Active, $relationship->status);
        Notification::assertSentTo($student, MentoringAssignedNotification::class);

        $this->actingAs($lecturer)
            ->post(route('mentoring.sessions.store', $relationship), [
                'conducted_at' => now()->format('Y-m-d H:i:s'),
                'duration_minutes' => 30,
                'mode' => 'online',
                'topic' => 'Week 1 check-in',
                'remarks' => 'Student understands feedback process.',
                'student_progress_notes' => 'Submitted draft on time.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertSame(1, $relationship->sessions()->count());

        $this->actingAs($lecturer)
            ->post(route('mentoring.areas.store', $relationship), [
                'title' => 'Time management',
                'description' => 'Submit work earlier',
                'priority' => 'high',
                'status' => ImprovementAreaStatus::Open->value,
            ])
            ->assertRedirect();

        $this->actingAs($lecturer)
            ->post(route('mentoring.plans.store', $relationship), [
                'title' => 'Weekly study plan',
                'objectives' => 'Complete two practice sets',
                'status' => ActionPlanStatus::InProgress->value,
                'progress_percent' => 40,
            ])
            ->assertRedirect();

        $this->actingAs($lecturer)
            ->get(route('mentoring.show', $relationship))
            ->assertOk()
            ->assertSee('Time management')
            ->assertSee('Weekly study plan')
            ->assertSee('Student understands feedback process.');
    }

    public function test_student_can_view_own_mentoring_but_not_manage(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $other = $this->makeStudent('Other Student');

        $relationship = MentoringRelationship::query()->create([
            'mentor_id' => $lecturer->id,
            'mentee_id' => $student->id,
            'assigned_by' => $lecturer->id,
            'status' => MentoringStatus::Active,
            'started_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('mentoring.show', $relationship))
            ->assertOk()
            ->assertDontSee('Record a session');

        $this->actingAs($other)
            ->get(route('mentoring.show', $relationship))
            ->assertForbidden();

        $this->actingAs($student)
            ->post(route('mentoring.sessions.store', $relationship), [
                'conducted_at' => now()->format('Y-m-d H:i:s'),
                'mode' => 'in_person',
            ])
            ->assertForbidden();
    }

    public function test_cross_lecturer_cannot_view_another_mentors_relationship(): void
    {
        $mentor = $this->makeLecturer();
        $otherLecturer = $this->makeLecturer();
        $student = $this->makeStudent();

        $relationship = MentoringRelationship::query()->create([
            'mentor_id' => $mentor->id,
            'mentee_id' => $student->id,
            'assigned_by' => $mentor->id,
            'status' => MentoringStatus::Active,
            'started_at' => now(),
        ]);

        $this->actingAs($otherLecturer)
            ->get(route('mentoring.show', $relationship))
            ->assertForbidden();
    }

    public function test_staff_can_open_mentoring_report_and_export_csv(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();

        MentoringRelationship::query()->create([
            'mentor_id' => $lecturer->id,
            'mentee_id' => $student->id,
            'assigned_by' => $lecturer->id,
            'status' => MentoringStatus::Active,
            'started_at' => now(),
        ]);

        $this->actingAs($lecturer)
            ->get(route('mentoring.report'))
            ->assertOk()
            ->assertSee('Mentoring roster');

        $this->actingAs($lecturer)
            ->get(route('mentoring.report.export', ['format' => 'csv']))
            ->assertOk();
    }

    private function makeLecturer(): User
    {
        return User::factory()->create(['role' => UserRole::Lecturer]);
    }

    private function makeStudent(string $name = 'Mentee Student'): User
    {
        return User::factory()->create([
            'name' => $name,
            'role' => UserRole::Student,
            'student_id' => 'DS'.random_int(100000, 999999),
        ]);
    }

    private function makeCourse(?User $lecturer = null): Course
    {
        $lecturer ??= $this->makeLecturer();

        return Course::query()->create([
            'title' => 'Mentoring Course',
            'code' => 'MEN'.random_int(100, 999),
            'lecturer_id' => $lecturer->id,
            'is_active' => true,
        ]);
    }
}
