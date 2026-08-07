<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $courses = match (true) {
            $user->isAdmin() => Course::query()->with('lecturer')->withCount(['students', 'assignments'])->latest(),
            $user->isLecturer() => $user->taughtCourses()->withCount(['students', 'assignments'])->latest(),
            default => $user->enrolledCourses()->where('is_active', true)->with('lecturer')->withCount(['assignments', 'students'])->latest(),
        };

        if ($search = $request->string('q')->trim()->toString()) {
            $courses->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (($user->isAdmin() || $user->isLecturer()) && $request->filled('status')) {
            $status = $request->string('status')->toString();
            if ($status === 'active') {
                $courses->where('is_active', true);
            } elseif ($status === 'inactive') {
                $courses->where('is_active', false);
            }
        }

        return view('courses.index', [
            'courses' => $courses->get(),
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorize('create', Course::class);

        $lecturers = $request->user()->isAdmin()
            ? User::query()->where('role', 'lecturer')->orderBy('name')->get()
            : collect();

        return view('courses.create', compact('lecturers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'semester' => ['nullable', 'string', 'max:32'],
            'lecturer_id' => [
                $request->user()->isAdmin() ? 'required' : 'nullable',
                'exists:users,id',
            ],
        ]);

        if ($request->user()->isAdmin()) {
            $lecturer = User::query()
                ->where('id', $validated['lecturer_id'])
                ->where('role', 'lecturer')
                ->first();

            if (! $lecturer) {
                return back()
                    ->withInput()
                    ->withErrors(['lecturer_id' => 'Select an active lecturer account for this course.']);
            }

            $lecturerId = $lecturer->id;
        } else {
            $lecturerId = $request->user()->id;
        }

        $course = Course::query()->create([
            'code' => strtoupper($validated['code']),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'semester' => $validated['semester'] ?? null,
            'lecturer_id' => $lecturerId,
            'is_active' => false,
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Course created as disabled. Publish it from the course list when students should see it.');
    }

    public function show(Course $course): View
    {
        $this->authorize('view', $course);

        $user = request()->user();

        $course->load([
            'lecturer',
            'assignments' => fn ($q) => $user->isStudent()
                ? $q->where('is_published', true)->latest()
                : $q->latest(),
            'assessments' => fn ($q) => $user->isStudent()
                ? $q->where('is_published', true)->latest()
                : $q->latest(),
            'materials' => fn ($q) => $q->orderBy('sort_order')->orderBy('title'),
            'students',
        ]);

        $upcomingSessions = $course->classSessions()
            ->where('starts_at', '>=', now()->startOfDay())
            ->orderBy('starts_at')
            ->limit(5)
            ->get();

        $submissionsByAssignment = collect();
        $attemptsByAssessment = collect();
        if (request()->user()->isStudent()) {
            $submissionsByAssignment = request()->user()
                ->submissions()
                ->whereIn('assignment_id', $course->assignments->pluck('id'))
                ->get()
                ->keyBy('assignment_id');

            $attemptsByAssessment = \App\Models\AssessmentAttempt::query()
                ->where('user_id', request()->user()->id)
                ->whereIn('assessment_id', $course->assessments->pluck('id'))
                ->get()
                ->keyBy('assessment_id');
        }

        return view('courses.show', compact('course', 'submissionsByAssignment', 'attemptsByAssessment', 'upcomingSessions'));
    }

    public function edit(Course $course): View
    {
        $this->authorize('update', $course);

        $lecturers = request()->user()->isAdmin()
            ? User::query()->where('role', 'lecturer')->orderBy('name')->get()
            : collect();

        $course->load('lecturer')->loadCount(['students', 'assignments']);

        return view('courses.edit', compact('course', 'lecturers'));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:courses,code,'.$course->id],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'semester' => ['nullable', 'string', 'max:32'],
            'lecturer_id' => ['nullable', 'exists:users,id'],
        ]);

        $course->update([
            'code' => strtoupper($validated['code']),
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'semester' => $validated['semester'] ?? null,
            'lecturer_id' => $request->user()->isAdmin()
                ? ($validated['lecturer_id'] ?? $course->lecturer_id)
                : $course->lecturer_id,
        ]);

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Course updated.');
    }

    public function publish(Course $course): RedirectResponse
    {
        $this->authorize('update', $course);

        if ($course->is_active) {
            return redirect()
                ->route('courses.index')
                ->with('success', 'Course is already published to students.');
        }

        $course->update(['is_active' => true]);

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course enabled and published to students.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $this->authorize('delete', $course);

        $hasSubmissions = $course->assignments()
            ->whereHas('submissions')
            ->exists();

        if ($hasSubmissions) {
            $course->update(['is_active' => false]);

            return redirect()
                ->route('courses.index')
                ->with('success', 'Course archived because it has student submissions. It is now hidden from active lists.');
        }

        $course->assignments()->each(function ($assignment) {
            $assignment->attachments()->each(fn ($a) => $a->deleteFile());
            $assignment->attachments()->delete();
            $assignment->delete();
        });
        $course->enrollments()->delete();
        $course->announcements()->delete();
        $course->delete();

        return redirect()
            ->route('courses.index')
            ->with('success', 'Course deleted permanently.');
    }
}
