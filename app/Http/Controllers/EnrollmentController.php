<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EnrollmentController extends Controller
{
    public function edit(Course $course): View
    {
        $this->authorize('manageEnrollments', $course);

        $enrolledIds = $course->students()->pluck('users.id');
        $availableStudents = User::query()
            ->where('role', 'student')
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('name')
            ->get();

        return view('courses.enrollments', [
            'course' => $course->load(['lecturer', 'students'])->loadCount('assignments'),
            'availableStudents' => $availableStudents,
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('manageEnrollments', $course);

        $validated = $request->validate([
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['exists:users,id'],
        ]);

        $students = User::query()
            ->where('role', 'student')
            ->whereIn('id', $validated['student_ids'])
            ->pluck('id');

        $course->students()->syncWithoutDetaching($students);

        return back()->with('success', count($students).' student(s) enrolled.');
    }

    public function destroy(Course $course, User $user): RedirectResponse
    {
        $this->authorize('manageEnrollments', $course);

        $course->students()->detach($user->id);

        return back()->with('success', 'Student removed from course.');
    }
}
