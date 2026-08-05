<?php

namespace Tests\Feature;

use App\Enums\SessionDeliveryMode;
use App\Enums\SubmissionStatus;
use App\Enums\UserRole;
use App\Models\Announcement;
use App\Models\Answer;
use App\Models\Assignment;
use App\Models\ClassSession;
use App\Models\Course;
use App\Models\Question;
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

    public function test_student_dashboard_loads_with_analytics(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse();
        $course->students()->attach($student->id);
        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $course->lecturer_id,
            'title' => 'Lab report',
            'is_published' => true,
            'due_at' => now()->addWeek(),
        ]);
        Submission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'file_path' => 'submissions/lab.pdf',
            'file_name' => 'lab.pdf',
            'status' => SubmissionStatus::Submitted,
            'submitted_at' => now(),
        ]);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Weekly activity')
            ->assertSee('Review snapshot')
            ->assertSee('Lab report');
    }

    public function test_student_can_access_core_hubs(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse();
        $course->students()->attach($student->id);

        $this->actingAs($student)->get(route('courses.index'))->assertOk();
        $this->actingAs($student)->get(route('calendar.index'))->assertOk();
        $this->actingAs($student)->get(route('help.index'))->assertOk()->assertSee('Back to portal');
        $this->actingAs($student)->get(route('settings.index'))->assertOk();
        $this->actingAs($student)->get(route('announcements.index'))->assertOk();
        $this->actingAs($student)->get(route('questions.index'))->assertOk();
        $this->actingAs($student)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($student)->get(route('gradebook.index'))->assertForbidden();
    }

    public function test_lecturer_can_schedule_class_session_and_student_sees_it_on_calendar_day(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $startsAt = now()->addDays(4)->setTime(10, 0);

        $this->actingAs($lecturer)
            ->post(route('courses.sessions.store', $course), [
                'title' => 'Week 4 lecture',
                'starts_at' => $startsAt->format('Y-m-d\TH:i'),
                'ends_at' => $startsAt->copy()->addHours(2)->format('Y-m-d\TH:i'),
                'mode' => SessionDeliveryMode::Online->value,
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
            ])
            ->assertRedirect(route('courses.sessions.index', $course));

        $session = ClassSession::query()->first();
        $this->assertNotNull($session);
        $this->assertSame(SessionDeliveryMode::Online, $session->mode);

        $date = $startsAt->format('Y-m-d');

        $this->actingAs($student)
            ->get(route('calendar.index', [
                'month' => $startsAt->month,
                'year' => $startsAt->year,
                'date' => $date,
            ]))
            ->assertOk()
            ->assertSee('Week 4 lecture')
            ->assertSee('Online')
            ->assertSee('Join online class');
    }

    public function test_offline_class_requires_location(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($lecturer)
            ->post(route('courses.sessions.store', $course), [
                'starts_at' => now()->addDay()->format('Y-m-d\TH:i'),
                'mode' => SessionDeliveryMode::Offline->value,
            ])
            ->assertSessionHasErrors('location');
    }

    public function test_lecturer_cannot_edit_another_courses_class_session(): void
    {
        $lecturer = $this->makeLecturer();
        $otherLecturer = $this->makeLecturer();
        $course = $this->makeCourse($otherLecturer);

        $session = ClassSession::query()->create([
            'course_id' => $course->id,
            'created_by' => $otherLecturer->id,
            'starts_at' => now()->addDay()->setTime(9, 0),
            'mode' => SessionDeliveryMode::Online,
            'meeting_link' => 'https://meet.google.com/other-class',
        ]);

        $this->actingAs($lecturer)
            ->get(route('class-sessions.edit', $session))
            ->assertForbidden();
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

    public function test_admin_cannot_create_student_without_student_id(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->from(route('admin.users.create'))
            ->post(route('admin.users.store'), [
                'name' => 'Student 1',
                'email' => 'student1@lms.test',
                'role' => UserRole::Student->value,
                'password' => 'password',
                'password_confirmation' => 'password',
            ])
            ->assertRedirect(route('admin.users.create'))
            ->assertSessionHasErrors('student_id');

        $this->assertDatabaseMissing('users', [
            'email' => 'student1@lms.test',
        ]);
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

        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();

        $this->actingAs($lecturer)
            ->post(route('announcements.store'), [
                'title' => 'System maintenance',
                'body' => 'Portal will be down tonight.',
            ])
            ->assertRedirect();

        Notification::assertSentTo($student, AnnouncementPublishedNotification::class);
        Notification::assertNotSentTo($lecturer, AnnouncementPublishedNotification::class);
    }

    public function test_admin_cannot_create_announcements(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post(route('announcements.store'), [
                'title' => 'Admin update',
                'body' => 'Should not be allowed.',
            ])
            ->assertForbidden();
    }

    public function test_admin_can_bulk_import_students_from_emails(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->post(route('admin.users.bulk-import.store'), [
                'emails' => "sai.kiran@university.edu\ninvalid-email\nsai.kiran@university.edu",
            ])
            ->assertOk()
            ->assertSee('Sai Kiran')
            ->assertSee('sai.kiran@university.edu');

        $this->assertDatabaseHas('users', [
            'email' => 'sai.kiran@university.edu',
            'name' => 'Sai Kiran',
            'role' => UserRole::Student->value,
        ]);

        $this->assertDatabaseCount('users', 2);
    }

    public function test_gradebook_requires_staff_role(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->get(route('gradebook.index'))
            ->assertForbidden();
    }

    public function test_any_user_can_post_question_and_answer(): void
    {
        $student = $this->makeStudent();
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($student)
            ->post(route('questions.store'), [
                'course_id' => $course->id,
                'title' => 'How do I access lab materials?',
                'body' => 'I cannot find the dataset link on the course page.',
            ])
            ->assertRedirect();

        $question = Question::query()->where('title', 'How do I access lab materials?')->first();
        $this->assertNotNull($question);
        $this->assertSame($student->id, $question->user_id);

        $this->actingAs($lecturer)
            ->post(route('questions.answers.store', $question), [
                'body' => 'Check the Assignments tab — the dataset link is in the lab brief.',
            ])
            ->assertRedirect();

        $answer = Answer::query()->where('question_id', $question->id)->first();
        $this->assertNotNull($answer);
        $this->assertSame($lecturer->id, $answer->user_id);
        $this->assertNull($answer->parent_id);

        $this->actingAs($student)
            ->patch(route('questions.answers.accept', [$question, $answer]))
            ->assertRedirect();

        $question->refresh();
        $answer->refresh();
        $this->assertTrue($question->is_resolved);
        $this->assertTrue($answer->is_accepted);
    }

    public function test_user_can_post_nested_reply_via_ajax(): void
    {
        $student = $this->makeStudent();
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $question = Question::query()->create([
            'user_id' => $student->id,
            'course_id' => $course->id,
            'title' => 'Nested reply thread',
            'body' => 'Parent question body',
        ]);

        $root = Answer::query()->create([
            'question_id' => $question->id,
            'user_id' => $lecturer->id,
            'body' => 'Top-level answer',
        ]);

        $response = $this->actingAs($student)
            ->postJson(route('questions.answers.store', $question), [
                'body' => 'Thanks — that helped.',
                'parent_id' => $root->id,
            ]);

        $response->assertOk()
            ->assertJsonPath('answer.parent_id', $root->id)
            ->assertJsonStructure(['html', 'total_answers', 'answer' => ['id', 'parent_id', 'depth']]);

        $this->assertSame(1, $response->json('answer.depth'));
        $this->assertDatabaseHas('answers', [
            'question_id' => $question->id,
            'parent_id' => $root->id,
            'body' => 'Thanks — that helped.',
            'user_id' => $student->id,
        ]);

        $this->actingAs($student)
            ->get(route('questions.show', $question))
            ->assertOk()
            ->assertSee('Reply', false)
            ->assertSee('Thanks — that helped.', false);
    }

    public function test_nested_reply_cannot_exceed_max_depth(): void
    {
        $student = $this->makeStudent();
        $question = Question::query()->create([
            'user_id' => $student->id,
            'title' => 'Deep thread',
            'body' => 'Body',
        ]);

        $parent = Answer::query()->create([
            'question_id' => $question->id,
            'user_id' => $student->id,
            'body' => 'Root',
        ]);

        for ($depth = 1; $depth <= Answer::MAX_DEPTH; $depth++) {
            $parent = Answer::query()->create([
                'question_id' => $question->id,
                'parent_id' => $parent->id,
                'user_id' => $student->id,
                'body' => "Level {$depth}",
            ]);
        }

        $this->actingAs($student)
            ->postJson(route('questions.answers.store', $question), [
                'body' => 'Too deep',
                'parent_id' => $parent->id,
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_user_can_update_portal_theme_from_settings(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Portal theme')
            ->assertSee('Original ISARVA');

        $this->actingAs($student)
            ->patch(route('settings.update'), [
                'email_notifications' => '1',
                'theme' => 'violet',
            ])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas('success');

        $student->refresh();
        $this->assertSame('violet', $student->theme);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('--brand-600: 124 58 237', false)
            ->assertSee('--sidebar-bg: 24 18 42', false);
    }

    public function test_user_can_update_theme_from_header_endpoint(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->patchJson(route('settings.theme'), ['theme' => 'forest'])
            ->assertOk()
            ->assertJsonPath('theme.key', 'forest');

        $this->assertSame('forest', $student->fresh()->theme);

        $this->actingAs($student)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('lms-theme-picker', false)
            ->assertSee('--brand-600: 5 150 105', false)
            ->assertSee('--sidebar-bg: 13 28 24', false);
    }

    public function test_invalid_theme_is_rejected(): void
    {
        $student = $this->makeStudent();

        $this->actingAs($student)
            ->patch(route('settings.update'), [
                'email_notifications' => '1',
                'theme' => 'invalid-theme',
            ])
            ->assertSessionHasErrors('theme');

        $this->assertSame('classic', $student->fresh()->theme);
    }

    public function test_lecturer_can_save_assignment_as_draft(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($lecturer)
            ->post(route('courses.assignments.store', $course), [
                'title' => 'Draft assignment',
                'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File->value,
            ])
            ->assertRedirect();

        $assignment = Assignment::query()->where('title', 'Draft assignment')->first();
        $this->assertNotNull($assignment);
        $this->assertFalse($assignment->is_published);
    }

    public function test_student_cannot_see_draft_assignment_on_course_page(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Hidden draft',
            'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File,
            'is_published' => false,
        ]);

        Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Published task',
            'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File,
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->get(route('courses.show', $course))
            ->assertOk()
            ->assertSee('Published task')
            ->assertDontSee('Hidden draft');
    }

    public function test_student_cannot_open_draft_assignment(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Hidden draft',
            'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File,
            'is_published' => false,
        ]);

        $this->actingAs($student)
            ->get(route('assignments.show', $assignment))
            ->assertForbidden();
    }

    public function test_lecturer_can_publish_draft_assignment_via_edit(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Draft assignment',
            'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File,
            'is_published' => false,
        ]);

        $this->actingAs($lecturer)
            ->patch(route('assignments.update', $assignment), [
                'title' => 'Draft assignment',
                'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File->value,
                'is_published' => '1',
            ])
            ->assertRedirect(route('assignments.show', $assignment));

        $fresh = $assignment->fresh();
        $this->assertTrue($fresh->is_published);
        $this->assertSame('Draft assignment', $fresh->title);
    }

    public function test_lecturer_can_create_assignment_with_multiple_attachments(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($lecturer)
            ->post(route('courses.assignments.store', $course), [
                'title' => 'Lab resources',
                'instructions' => 'Read the brief and dataset.',
                'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File->value,
                'is_published' => '1',
                'attachments' => [
                    \Illuminate\Http\UploadedFile::fake()->create('brief.pdf', 100, 'application/pdf'),
                    \Illuminate\Http\UploadedFile::fake()->create('dataset.csv', 50, 'text/csv'),
                ],
            ])
            ->assertRedirect();

        $assignment = Assignment::query()->where('title', 'Lab resources')->first();
        $this->assertNotNull($assignment);
        $this->assertCount(2, $assignment->attachments);
        $this->assertEqualsCanonicalizing(
            ['brief.pdf', 'dataset.csv'],
            $assignment->attachments->pluck('name')->all()
        );
    }

    public function test_admin_can_update_course_without_deleting_it(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($admin)
            ->patch(route('courses.update', $course), [
                'code' => $course->code,
                'title' => 'Updated course title',
                'lecturer_id' => $lecturer->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('courses.show', $course));

        $fresh = $course->fresh();
        $this->assertSame('Updated course title', $fresh->title);
        $this->assertDatabaseHas('courses', ['id' => $course->id]);
    }

    public function test_admin_must_assign_lecturer_when_creating_course(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $lecturer = $this->makeLecturer();

        $this->actingAs($admin)
            ->post(route('courses.store'), [
                'code' => 'ADM101',
                'title' => 'Admin created module',
                'lecturer_id' => $lecturer->id,
            ])
            ->assertRedirect();

        $course = Course::query()->where('code', 'ADM101')->first();
        $this->assertNotNull($course);
        $this->assertSame($lecturer->id, $course->lecturer_id);

        $this->actingAs($lecturer)
            ->get(route('courses.index'))
            ->assertOk()
            ->assertSee('Admin created module');
    }

    public function test_lecturer_enrollment_page_includes_student_search(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        User::factory()->create([
            'role' => UserRole::Student,
            'name' => 'Amala Jothi Alpart',
            'email' => 'amala@university.edu',
            'student_id' => '2547031',
        ]);

        $this->actingAs($lecturer)
            ->get(route('courses.enrollments.edit', $course))
            ->assertOk()
            ->assertSee('Search by name, email, or student ID', false)
            ->assertSee('Amala Jothi Alpart', false)
            ->assertSee('2547031', false)
            ->assertSee('amala@university.edu', false);
    }

    public function test_lecturer_can_enroll_students_on_course(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);
        $student = $this->makeStudent();

        $this->actingAs($lecturer)
            ->post(route('courses.enrollments.store', $course), [
                'student_ids' => [$student->id],
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertTrue($course->fresh()->students->contains($student));
    }

    public function test_lecturer_cannot_upload_assignment_attachment_over_two_megabytes(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($lecturer)
            ->from(route('courses.assignments.create', $course))
            ->post(route('courses.assignments.store', $course), [
                'title' => 'Large files',
                'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File->value,
                'is_published' => '1',
                'attachments' => [
                    \Illuminate\Http\UploadedFile::fake()->create('large.pdf', 3000, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('courses.assignments.create', $course))
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseMissing('assignments', ['title' => 'Large files']);
    }

    public function test_assignment_create_page_renders_attachment_validation_errors(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $response = $this->actingAs($lecturer)
            ->from(route('courses.assignments.create', $course))
            ->post(route('courses.assignments.store', $course), [
                'title' => 'Large files',
                'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File->value,
                'is_published' => '1',
                'attachments' => [
                    \Illuminate\Http\UploadedFile::fake()->create('large.pdf', 3000, 'application/pdf'),
                ],
            ]);

        $this->actingAs($lecturer)
            ->get(route('courses.assignments.create', $course))
            ->assertOk()
            ->assertSee('Each attachment must be 2 MB or smaller.', false);
    }

    public function test_lecturer_cannot_add_more_than_three_assignment_attachments(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($lecturer)
            ->from(route('courses.assignments.create', $course))
            ->post(route('courses.assignments.store', $course), [
                'title' => 'Too many files',
                'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File->value,
                'is_published' => '1',
                'attachments' => [
                    \Illuminate\Http\UploadedFile::fake()->create('one.pdf', 10, 'application/pdf'),
                    \Illuminate\Http\UploadedFile::fake()->create('two.pdf', 10, 'application/pdf'),
                    \Illuminate\Http\UploadedFile::fake()->create('three.pdf', 10, 'application/pdf'),
                    \Illuminate\Http\UploadedFile::fake()->create('four.pdf', 10, 'application/pdf'),
                ],
            ])
            ->assertRedirect(route('courses.assignments.create', $course))
            ->assertSessionHasErrors('attachments');

        $this->assertDatabaseMissing('assignments', ['title' => 'Too many files']);
    }

    public function test_student_can_submit_external_link_for_link_only_assignment(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Capstone bundle',
            'delivery_method' => \App\Enums\SubmissionDeliveryMethod::Link,
            'drop_folder_url' => 'https://drive.google.com/drive/folders/demo-folder',
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->post(route('assignments.submissions.store', $assignment), [
                'external_url' => 'https://drive.google.com/file/d/demo-file-id/view?usp=sharing',
                'external_label' => 'capstone.zip',
                'notes' => 'Uploaded to shared folder.',
            ])
            ->assertRedirect();

        $submission = Submission::query()->where('assignment_id', $assignment->id)->first();
        $this->assertNotNull($submission);
        $this->assertSame(\App\Enums\SubmissionSource::Link, $submission->source);
        $this->assertSame('https://drive.google.com/file/d/demo-file-id/view?usp=sharing', $submission->external_url);
        $this->assertNull($submission->file_path);

        $this->actingAs($student)
            ->get(route('submissions.show', $submission))
            ->assertOk()
            ->assertSee('Open in Google Drive')
            ->assertSee('capstone.zip');
    }

    public function test_external_link_submission_rejects_unsupported_hosts(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Capstone bundle',
            'delivery_method' => \App\Enums\SubmissionDeliveryMethod::Link,
            'drop_folder_url' => 'https://drive.google.com/drive/folders/demo-folder',
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->post(route('assignments.submissions.store', $assignment), [
                'external_url' => 'https://example.com/files/capstone.zip',
            ])
            ->assertSessionHasErrors('external_url');
    }

    public function test_file_upload_still_works_for_file_only_assignment(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $assignment = Assignment::query()->create([
            'course_id' => $course->id,
            'created_by' => $lecturer->id,
            'title' => 'Lab report',
            'delivery_method' => \App\Enums\SubmissionDeliveryMethod::File,
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->post(route('assignments.submissions.store', $assignment), [
                'file' => \Illuminate\Http\UploadedFile::fake()->create('lab-report.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect();

        $submission = Submission::query()->where('assignment_id', $assignment->id)->first();
        $this->assertNotNull($submission);
        $this->assertSame(\App\Enums\SubmissionSource::File, $submission->source);
        $this->assertSame('lab-report.pdf', $submission->file_name);
        $this->assertNull($submission->external_url);
    }

    public function test_lecturer_can_add_course_material_and_student_can_view_index(): void
    {
        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $this->actingAs($lecturer)
            ->post(route('courses.materials.store', $course), [
                'category' => 'notes',
                'title' => 'Week 1 slides',
                'external_url' => 'https://example.com/slides',
            ])
            ->assertRedirect(route('courses.materials.index', $course));

        $this->actingAs($student)
            ->get(route('courses.materials.index', $course))
            ->assertOk()
            ->assertSee('Week 1 slides');
    }

    public function test_lecturer_can_edit_course_material_without_deleting_it(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($lecturer)
            ->post(route('courses.materials.store', $course), [
                'category' => 'notes',
                'title' => 'Original title',
                'external_url' => 'https://example.com/original',
            ])
            ->assertRedirect(route('courses.materials.index', $course));

        $material = \App\Models\CourseMaterial::query()->where('course_id', $course->id)->firstOrFail();

        $this->actingAs($lecturer)
            ->patch(route('course-materials.update', $material), [
                'category' => 'notes',
                'title' => 'Updated title',
                'external_url' => 'https://example.com/updated',
            ])
            ->assertRedirect(route('courses.materials.index', $course))
            ->assertSessionHas('success', 'Material updated.');

        $this->assertDatabaseHas('course_materials', [
            'id' => $material->id,
            'title' => 'Updated title',
            'external_url' => 'https://example.com/updated',
        ]);
    }

    public function test_create_material_rejects_file_over_size_limit(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($lecturer)
            ->from(route('courses.materials.create', $course))
            ->post(route('courses.materials.store', $course), [
                'category' => 'notes',
                'title' => 'Too large',
                'file' => \Illuminate\Http\UploadedFile::fake()->create('big.pdf', 4000, 'application/pdf'),
            ])
            ->assertRedirect(route('courses.materials.create', $course))
            ->assertSessionHasErrors('file');
    }

    public function test_create_material_requires_file_or_link(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);

        $this->actingAs($lecturer)
            ->from(route('courses.materials.create', $course))
            ->post(route('courses.materials.store', $course), [
                'category' => 'notes',
                'title' => 'Missing attachment',
            ])
            ->assertRedirect(route('courses.materials.create', $course))
            ->assertSessionHasErrors(['file', 'external_url']);
    }

    public function test_lecturer_can_publish_assessment_and_student_gets_score_without_answers(): void
    {
        Notification::fake();

        $lecturer = $this->makeLecturer();
        $student = $this->makeStudent();
        $course = $this->makeCourse($lecturer);
        $course->students()->attach($student->id);

        $this->actingAs($lecturer)
            ->post(route('courses.assessments.store', $course), [
                'title' => 'Quiz 1',
                'question_count' => 2,
                'marks_per_question' => 2,
            ])
            ->assertRedirect();

        $assessment = \App\Models\Assessment::query()->where('course_id', $course->id)->firstOrFail();

        $questions = [];
        for ($i = 0; $i < 2; $i++) {
            $questions[] = [
                'prompt' => 'Question '.($i + 1),
                'correct' => 1,
                'options' => [
                    ['label' => 'Correct'],
                    ['label' => 'Wrong A'],
                    ['label' => 'Wrong B'],
                    ['label' => 'Wrong C'],
                ],
            ];
        }

        $this->actingAs($lecturer)
            ->patch(route('assessments.update', $assessment), [
                'title' => 'Quiz 1',
                'questions' => $questions,
            ])
            ->assertRedirect(route('assessments.edit', $assessment));

        $this->actingAs($lecturer)
            ->post(route('assessments.publish', $assessment))
            ->assertRedirect(route('assessments.show', $assessment));

        Notification::assertSentTo($student, \App\Notifications\AssessmentPublishedNotification::class);

        $assessment->load('questions.options');
        $firstQuestion = $assessment->questions->first();
        $lastQuestion = $assessment->questions->last();
        $correctOption = $firstQuestion->options->firstWhere('is_correct', true);
        $wrongOption = $lastQuestion->options->firstWhere('is_correct', false);

        $this->actingAs($student)
            ->post(route('assessments.attempt.store', $assessment), [
                'answers' => [
                    $firstQuestion->id => $correctOption->id,
                    $lastQuestion->id => $wrongOption->id,
                ],
            ])
            ->assertRedirect(route('assessments.result', $assessment));

        $this->actingAs($student)
            ->get(route('assessments.result', $assessment))
            ->assertOk()
            ->assertSee('2 / 4')
            ->assertDontSee('Question 1')
            ->assertDontSee('Question 2');

        $this->actingAs($lecturer)
            ->get(route('assessments.show', $assessment))
            ->assertOk()
            ->assertSee('Student results')
            ->assertSee($student->name)
            ->assertSee('2 / 4')
            ->assertSee('1/1');

        $this->actingAs($lecturer)
            ->get(route('courses.assessments.index', $course))
            ->assertOk()
            ->assertSee('View results')
            ->assertSee('1 / 1 students');
    }

    public function test_timetable_csv_import_creates_class_sessions(): void
    {
        $lecturer = $this->makeLecturer();
        $course = $this->makeCourse($lecturer);
        $course->update(['semester' => '2026-S1']);

        $csv = "title,starts_at,ends_at,mode,meeting_link,location,semester\n";
        $csv .= "Lecture 1,2026-08-01 10:00:00,2026-08-01 11:00:00,online,https://meet.example.com,,2026-S1\n";

        $this->actingAs($lecturer)
            ->post(route('courses.sessions.timetable.import', $course), [
                'timetable' => \Illuminate\Http\UploadedFile::fake()->createWithContent('timetable.csv', $csv),
            ])
            ->assertRedirect(route('courses.sessions.index', $course));

        $this->assertDatabaseHas('class_sessions', [
            'course_id' => $course->id,
            'title' => 'Lecture 1',
        ]);
    }

    public function test_assessment_due_date_appears_on_calendar(): void
    {
        $student = $this->makeStudent();
        $course = $this->makeCourse();
        $course->students()->attach($student->id);

        \App\Models\Assessment::query()->create([
            'course_id' => $course->id,
            'created_by' => $course->lecturer_id,
            'title' => 'Midterm quiz',
            'question_count' => 15,
            'marks_per_question' => 2,
            'due_at' => now()->startOfMonth()->addDays(10)->setTime(17, 0),
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->get(route('calendar.index'))
            ->assertOk()
            ->assertSee('Midterm quiz');
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
