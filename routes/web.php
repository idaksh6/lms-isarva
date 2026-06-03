<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('courses', CourseController::class)->only([
        'index', 'create', 'store', 'show', 'edit', 'update',
    ]);

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

    Route::get('assignments/{assignment}', [AssignmentController::class, 'show'])
        ->name('assignments.show');
    Route::get('assignments/{assignment}/edit', [AssignmentController::class, 'edit'])
        ->name('assignments.edit');
    Route::patch('assignments/{assignment}', [AssignmentController::class, 'update'])
        ->name('assignments.update');

    Route::get('assignments/{assignment}/submit', [SubmissionController::class, 'create'])
        ->name('assignments.submit');
    Route::post('assignments/{assignment}/submissions', [SubmissionController::class, 'store'])
        ->name('assignments.submissions.store');
    Route::get('submissions/{submission}', [SubmissionController::class, 'show'])
        ->name('submissions.show');
    Route::patch('submissions/{submission}/reviewed', [SubmissionController::class, 'markReviewed'])
        ->name('submissions.reviewed');

    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('users', [AdminUserController::class, 'index'])->name('users.index');
        Route::get('users/create', [AdminUserController::class, 'create'])->name('users.create');
        Route::post('users', [AdminUserController::class, 'store'])->name('users.store');
        Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
        Route::patch('users/{user}', [AdminUserController::class, 'update'])->name('users.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
