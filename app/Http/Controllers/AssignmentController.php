<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\AssignmentAttachment;
use App\Models\Course;
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

        return redirect()
            ->route('assignments.show', $assignment)
            ->with('success', 'Assignment updated.');
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
