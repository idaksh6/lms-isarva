<?php

namespace Tests\Feature;

use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\AnnouncementPublishedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class LmsCoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_access_assignments_hub(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse();
        $course->students()->attach($student->id);
        Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $course->lecturer_id,
            'title' => 'Lab 1',
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->get(route('assignments.index'))
            ->assertOk()
            ->assertSee('Lab 1');
    }

    public function test_lecturer_can_grade_submission(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);
        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Essay',
            'is_published' => true,
        ]);
        $submission = Submission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/test.pdf',
            'file_name' => 'test.pdf',
            'status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->actingAs($lecturer)
            ->patch(route('submissions.review', $submission), [
                'action' => 'grade',
                'score' => 88,
                'feedback' => 'Strong work.',
            ])
            ->assertRedirect();

        $submission->refresh();
        $this->assertSame(SubmissionStatus::Reviewed, $submission->status);
        $this->assertEquals(88.0, (float) $submission->score);
        $this->assertSame('B', $submission->letter_grade);
        $this->assertSame('Strong work.', $submission->feedback);
    }

    public function test_inactive_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Student,
            'is_active' => false,
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_deactivate_user(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $student = User::factory()->create(['role' => UserRole::Student, 'is_active' => true]);

        $this->actingAs($admin)
            ->patch(route('admin.users.deactivate', $student))
            ->assertRedirect();

        $this->assertFalse($student->fresh()->is_active);
    }

    public function test_publishing_course_announcement_notifies_enrolled_students(): void
    {
        Notification::fake();

        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $this->actingAs($lecturer)
            ->post(route('announcements.store'), [
                'course_id' => $course->id,
                'title' => 'Due date extended',
                'body' => 'Submit by next Friday.',
                'is_pinned' => '1',
            ])
            ->assertRedirect();

        Notification::assertSentTo($student, AnnouncementPublishedNotification::class);
        Notification::assertNotSentTo($lecturer, AnnouncementPublishedNotification::class);
        $this->assertDatabaseHas('announcements', [
            'course_id' => $course->id,
            'title' => 'Due date extended',
            'is_pinned' => true,
        ]);
    }

    public function test_publishing_global_announcement_notifies_other_users(): void
    {
        Notification::fake();

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $student = $this->makeStudent();

        $this->actingAs($admin)
            ->post(route('announcements.store'), [
                'title' => 'System maintenance',
                'body' => 'Portal will be down tonight.',
            ])
            ->assertRedirect();

        Notification::assertSentTo($student, AnnouncementPublishedNotification::class);
        Notification::assertNotSentTo($admin, AnnouncementPublishedNotification::class);
    }

    public function test_gradebook_requires_staff_role(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->get(route('gradebook.index'))
            ->assertForbidden();
    }

    private function makeLecturer(): User
    {
        return User::factory()->create(['role' => UserRole::Lecturer]);
    }

    private function makeStudent(): User
    {
        return User::factory()->create([
            'role' => UserRole::Student,
            'student_id' => 'DS2024999',
        ]);
    }

    private function makeCourse(?User $lecturer = null): Course
    {
        $lecturer ??= $this->makeLecturer();

        return Course::query()->create([
            'code' => 'TST101',
            'title' => 'Test Course',
            'lecturer_id' => $lecturer->id,
            'is_active' => true,
        ]);
    }
}
