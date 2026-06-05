<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\Course;
use App\Notifications\AssignmentPublishedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    public function create(Course $course): View
    {
        $this->authorize('create', Assignment::class);
        $this->authorize('update', $course);

        $course->load('lecturer')->loadCount(['students', 'assignments']);

        return view('assignments.create', compact('course'));
    }

    public function store(Request $request, Course $course): RedirectResponse
    {
        $this->authorize('create', Assignment::class);
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $assignment = $course->assignments()->create([
            'created_by' => $request->user()->id,
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'is_published' => $request->boolean('is_published', true),
        ]);

        $this->storeUploadedAttachments($assignment, $request->file('attachments', []));

        if ($assignment->is_published) {
            $this->notifyStudents($course, $assignment);
        }

        return redirect()
            ->route('assignments.show', $assignment)
            ->with('success', 'Assignment published to students.');
    }

    public function show(Assignment $assignment): View
    {
        $this->authorize('view', $assignment);

        $assignment->load(['course.lecturer', 'submissions.student', 'attachments'])->loadCount('submissions');

        $userSubmission = null;
        if (request()->user()->isStudent()) {
            $userSubmission = $assignment->submissions()
                ->where('user_id', request()->user()->id)
                ->first();
        }

        return view('assignments.show', compact('assignment', 'userSubmission'));
    }

    public function edit(Assignment $assignment): View
    {
        $this->authorize('update', $assignment);

        $assignment->load('attachments');

        return view('assignments.edit', compact('assignment'));
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('update', $assignment);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'instructions' => ['nullable', 'string'],
            'due_at' => ['nullable', 'date'],
            'attachments' => ['nullable', 'array', 'max:5'],
            'attachments.*' => ['file', 'max:10240'],
            'is_published' => ['sometimes', 'boolean'],
        ]);

        $assignment->update([
            'title' => $validated['title'],
            'instructions' => $validated['instructions'] ?? null,
            'due_at' => $validated['due_at'] ?? null,
            'is_published' => $request->boolean('is_published', $assignment->is_published),
        ]);

        $existingCount = $assignment->attachments()->count();
        $newFiles = $request->file('attachments', []);
        if ($existingCount + count($newFiles) > 5) {
            return back()
                ->withErrors(['attachments' => 'An assignment can have at most 5 files. Remove some or upload fewer.'])
                ->withInput();
        }

        $this->storeUploadedAttachments($assignment, $newFiles);

        if ($assignment->is_published && $assignment->wasChanged('is_published')) {
            $this->notifyStudents($assignment->course, $assignment);
        }

        return redirect()
            ->route('assignments.show', $assignment)
            ->with('success', 'Assignment updated.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->authorize('delete', $assignment);

        $course = $assignment->course;
        $assignment->load('submissions', 'attachments');

        foreach ($assignment->submissions as $submission) {
            if ($submission->file_path) {
                Storage::disk('public')->delete($submission->file_path);
            }
        }
        $assignment->submissions()->delete();

        $assignment->attachments()->each(fn ($a) => $a->deleteFile());
        $assignment->attachments()->delete();
        $assignment->delete();

        return redirect()
            ->route('courses.show', $course)
            ->with('success', 'Assignment deleted.');
    }

    private function notifyStudents(Course $course, Assignment $assignment): void
    {
        $course->load('students');

        foreach ($course->students as $student) {
            if ($student->isActive()) {
                $student->notify(new AssignmentPublishedNotification($assignment));
            }
        }
    }

    /**
     * @param  array<int, UploadedFile>  $files
     */
    private function storeUploadedAttachments(Assignment $assignment, array $files): void
    {
        foreach ($files as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }

            $path = $file->store('assignments', 'public');

            $assignment->attachments()->create([
                'path' => $path,
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
            ]);
        }
    }
}
