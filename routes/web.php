<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\AnswerController;
use App\Http\Controllers\AssessmentAttemptController;
use App\Http\Controllers\AssessmentController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\AssignmentHubController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ClassSessionController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseMaterialController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubmissionController;
use App\Http\Controllers\SubmissionHubController;
use App\Http\Controllers\TimetableImportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('courses', CourseController::class)->only([
        'index', 'create', 'store', 'show', 'edit', 'update', 'destroy',
    ]);
    Route::post('courses/{course}/publish', [CourseController::class, 'publish'])
        ->name('courses.publish');

    Route::get('courses/{course}/enrollments', [EnrollmentController::class, 'edit'])
        ->name('courses.enrollments.edit');
    Route::post('courses/{course}/enrollments', [EnrollmentController::class, 'store'])
        ->name('courses.enrollments.store');
    Route::delete('courses/{course}/enrollments/{user}', [EnrollmentController::class, 'destroy'])
        ->name('courses.enrollments.destroy');

    Route::get('courses/{course}/assignments/create', [AssignmentController::class, 'create'])
        ->name('courses.assignments.create');
    Route::post('courses/{course}/assignments', [AssignmentController::class, 'store'])
        ->name('courses.assignments.store');

    Route::get('courses/{course}/sessions', [ClassSessionController::class, 'index'])
        ->name('courses.sessions.index');
    Route::get('courses/{course}/sessions/create', [ClassSessionController::class, 'create'])
        ->name('courses.sessions.create');
    Route::post('courses/{course}/sessions', [ClassSessionController::class, 'store'])
        ->name('courses.sessions.store');
    Route::get('class-sessions/{classSession}/edit', [ClassSessionController::class, 'edit'])
        ->name('class-sessions.edit');
    Route::patch('class-sessions/{classSession}', [ClassSessionController::class, 'update'])
        ->name('class-sessions.update');
    Route::delete('class-sessions/{classSession}', [ClassSessionController::class, 'destroy'])
        ->name('class-sessions.destroy');

    Route::post('courses/{course}/sessions/timetable', [TimetableImportController::class, 'store'])
        ->name('courses.sessions.timetable.import');

    Route::get('courses/{course}/materials', [CourseMaterialController::class, 'index'])
        ->name('courses.materials.index');
    Route::get('courses/{course}/materials/create', [CourseMaterialController::class, 'create'])
        ->name('courses.materials.create');
    Route::post('courses/{course}/materials', [CourseMaterialController::class, 'store'])
        ->name('courses.materials.store');
    Route::get('course-materials/{material}/edit', [CourseMaterialController::class, 'edit'])
        ->name('course-materials.edit');
    Route::patch('course-materials/{material}', [CourseMaterialController::class, 'update'])
        ->name('course-materials.update');
    Route::delete('course-materials/{material}', [CourseMaterialController::class, 'destroy'])
        ->name('course-materials.destroy');

    Route::get('courses/{course}/assessments', [AssessmentController::class, 'index'])
        ->name('courses.assessments.index');
    Route::get('courses/{course}/assessments/create', [AssessmentController::class, 'create'])
        ->name('courses.assessments.create');
    Route::post('courses/{course}/assessments', [AssessmentController::class, 'store'])
        ->name('courses.assessments.store');
    Route::get('assessments/{assessment}', [AssessmentController::class, 'show'])
        ->name('assessments.show');
    Route::get('assessments/{assessment}/edit', [AssessmentController::class, 'edit'])
        ->name('assessments.edit');
    Route::patch('assessments/{assessment}', [AssessmentController::class, 'update'])
        ->name('assessments.update');
    Route::post('assessments/{assessment}/publish', [AssessmentController::class, 'publish'])
        ->name('assessments.publish');
    Route::delete('assessments/{assessment}', [AssessmentController::class, 'destroy'])
        ->name('assessments.destroy');
    Route::get('assessments/{assessment}/attempt', [AssessmentAttemptController::class, 'create'])
        ->name('assessments.attempt');
    Route::post('assessments/{assessment}/attempt', [AssessmentAttemptController::class, 'store'])
        ->name('assessments.attempt.store');
    Route::get('assessments/{assessment}/result', [AssessmentAttemptController::class, 'result'])
        ->name('assessments.result');

    Route::get('assignments', [AssignmentHubController::class, 'index'])->name('assignments.index');
    Route::get('assignments/{assignment}', [AssignmentController::class, 'show'])
        ->name('assignments.show');
    Route::get('assignments/{assignment}/edit', [AssignmentController::class, 'edit'])
        ->name('assignments.edit');
    Route::patch('assignments/{assignment}', [AssignmentController::class, 'update'])
        ->name('assignments.update');
    Route::delete('assignments/{assignment}', [AssignmentController::class, 'destroy'])
        ->name('assignments.destroy');

    Route::get('submissions', [SubmissionHubController::class, 'index'])->name('submissions.index');
    Route::get('assignments/{assignment}/submit', [SubmissionController::class, 'create'])
        ->name('assignments.submit');
    Route::post('assignments/{assignment}/submissions', [SubmissionController::class, 'store'])
        ->name('assignments.submissions.store');
    Route::get('submissions/{submission}', [SubmissionController::class, 'show'])
        ->name('submissions.show');
    Route::patch('submissions/{submission}/review', [SubmissionController::class, 'review'])
        ->name('submissions.review');

    Route::get('gradebook', [GradebookController::class, 'index'])->name('gradebook.index');
    Route::get('gradebook/export', [GradebookController::class, 'export'])->name('gradebook.export');

    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('reports/assignments', [ReportController::class, 'assignments'])->name('reports.assignments');
    Route::get('reports/assignments/export', [ReportController::class, 'exportAssignments'])->name('reports.assignments.export');
    Route::get('reports/activity', [ReportController::class, 'activity'])->name('reports.activity');
    Route::get('reports/activity/export', [ReportController::class, 'exportActivity'])->name('reports.activity.export');

    Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    Route::post('announcements', [AnnouncementController::class, 'store'])->name('announcements.store');
    Route::delete('announcements/{announcement}', [AnnouncementController::class, 'destroy'])
        ->name('announcements.destroy');

    Route::get('questions', [QuestionController::class, 'index'])->name('questions.index');
    Route::get('questions/create', [QuestionController::class, 'create'])->name('questions.create');
    Route::post('questions', [QuestionController::class, 'store'])->name('questions.store');
    Route::get('questions/{question}/panel', [QuestionController::class, 'panel'])->name('questions.panel');
    Route::get('questions/{question}/feed', [QuestionController::class, 'feed'])->name('questions.feed');
    Route::get('questions/{question}', [QuestionController::class, 'show'])->name('questions.show');
    Route::delete('questions/{question}', [QuestionController::class, 'destroy'])->name('questions.destroy');
    Route::post('questions/{question}/answers', [AnswerController::class, 'store'])->name('questions.answers.store');
    Route::patch('questions/{question}/answers/{answer}/accept', [AnswerController::class, 'accept'])->name('questions.answers.accept');
    Route::delete('answers/{answer}', [AnswerController::class, 'destroy'])->name('answers.destroy');

    Route::get('calendar', [CalendarController::class, 'index'])->name('calendar.index');

    Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::patch('settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::patch('settings/theme', [SettingsController::class, 'updateTheme'])->name('settings.theme');

    Route::get('help', [HelpController::class, 'index'])->name('help.index');

    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');
    Route::patch('notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read-all');

    Route::get('media/course-materials/{material}', [MediaController::class, 'courseMaterial'])
        ->name('media.course-material');
    Route::get('media/course-materials/{material}/download', [MediaController::class, 'downloadCourseMaterial'])
        ->name('media.course-material.download');
    Route::get('media/assignment-attachments/{attachment}', [MediaController::class, 'assignmentAttachment'])
        ->name('media.assignment-attachment');
    Route::get('media/assignment-attachments/{attachment}/download', [MediaController::class, 'downloadAssignmentAttachment'])
        ->name('media.assignment-attachment.download');
    Route::get('media/submissions/{submission}', [MediaController::class, 'submission'])
        ->name('media.submission');
    Route::get('media/submissions/{submission}/download', [MediaController::class, 'downloadSubmission'])
        ->name('media.submission.download');

    Route::middleware('role:admin,lecturer')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::get('users/bulk-import', [AdminUserController::class, 'bulkImportForm'])->name('users.bulk-import');
        Route::post('users/bulk-import', [AdminUserController::class, 'bulkImportStore'])->name('users.bulk-import.store');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        Route::patch('users/{user}/deactivate', [AdminUserController::class, 'deactivate'])->name('users.deactivate');
        Route::patch('users/{user}/activate', [AdminUserController::class, 'activate'])->name('users.activate');
        Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
