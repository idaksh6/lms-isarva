<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function create(Assignment $assignment): View
    {
        $this->authorize('create', [Submission::class, $assignment]);

        $existing = $assignment->submissions()
            ->where('user_id', request()->user()->id)
            ->first();

        if ($existing) {
            return view('submissions.show', ['submission' => $existing->load('assignment.course')]);
        }

        return view('submissions.create', compact('assignment'));
    }

    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('create', [Submission::class, $assignment]);

        if ($assignment->submissions()->where('user_id', $request->user()->id)->exists()) {
            return back()->with('error', 'You have already submitted work for this assignment.');
        }

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
            'file' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('file');
        $path = $file->store('submissions', 'public');

        $status = $assignment->isOverdue()
            ? SubmissionStatus::Late
            : SubmissionStatus::Submitted;

        $submission = Submission::query()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $request->user()->id,
            'notes' => $validated['notes'] ?? null,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'status' => $status,
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', 'Your work was submitted successfully.');
    }

    public function show(Submission $submission): View
    {
        $this->authorize('view', $submission);

        $submission->load(['assignment.course', 'student']);

        return view('submissions.show', compact('submission'));
    }

    public function markReviewed(Submission $submission): RedirectResponse
    {
        $user = request()->user();
        if (! $user->isLecturer() && ! $user->isAdmin()) {
            abort(403);
        }

        if ($user->isLecturer() && $submission->assignment->course->lecturer_id !== $user->id) {
            abort(403);
        }

        $submission->update(['status' => SubmissionStatus::Reviewed]);

        return back()->with('success', 'Submission marked as reviewed.');
    }
}
