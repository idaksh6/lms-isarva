<?php

namespace App\Http\Controllers;

use App\Enums\SessionDeliveryMode;
use App\Models\ClassSession;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassSessionController extends Controller
{
    public function index(Course $course): View
    {
        $this->authorize('view', $course);

        $sessions = $course->classSessions()
            ->orderBy('starts_at')
            ->get();

        return view('courses.sessions.index', [
            'course' => $course->load('lecturer')->loadCount(['students', 'assignments', 'classSessions']),
            'sessions' => $sessions,
        ]);
    }

    public function create(Course $course): View
    {
        $this->authorize('create', ClassSession::class);
        $this->authorize('update', $course);

        return view('courses.sessions.create', [
            'course' => $course->load('lecturer')->loadCount(['students', 'assignments', 'classSessions']),
        ]);
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('create', ClassSession::class);
        $this->authorize('update', $course);

        $validated = $this->validateSession($request);

        $course->classSessions()->create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('courses.sessions.index', $course)
            ->with('success', 'Class session scheduled.');
    }

    public function edit(ClassSession $classSession): View
    {
        $this->authorize('update', $classSession);

        $classSession->load('course.lecturer');
        $course = $classSession->course->loadCount(['students', 'assignments', 'classSessions']);

        return view('courses.sessions.edit', [
            'course' => $course,
            'session' => $classSession,
        ]);
    }

    public function update(Request $request, ClassSession $classSession): RedirectResponse
    {
        $this->authorize('update', $classSession);

        $classSession->update($this->validateSession($request));

        return redirect()
            ->route('courses.sessions.index', $classSession->course)
            ->with('success', 'Class session updated.');
    }

    public function destroy(ClassSession $classSession): RedirectResponse
    {
        $this->authorize('delete', $classSession);

        $course = $classSession->course;
        $classSession->delete();

        return redirect()
            ->route('courses.sessions.index', $course)
            ->with('success', 'Class session removed.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validateSession(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:255'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['nullable', 'date', 'after:starts_at'],
            'mode' => ['required', Rule::enum(SessionDeliveryMode::class)],
            'meeting_link' => [
                'nullable',
                'url',
                'max:2048',
                Rule::requiredIf(fn () => $request->input('mode') === SessionDeliveryMode::Online->value),
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
                Rule::requiredIf(fn () => $request->input('mode') === SessionDeliveryMode::Offline->value),
            ],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['mode'] === SessionDeliveryMode::Online->value) {
            $validated['location'] = null;
        } else {
            $validated['meeting_link'] = null;
        }

        return $validated;
    }
}
