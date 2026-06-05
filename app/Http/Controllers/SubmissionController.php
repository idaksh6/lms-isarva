<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use App\Notifications\GradePostedNotification;
use App\Notifications\SubmissionReceivedNotification;
use App\Support\GradeHelper;
use App\Support\UploadLimits;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    public function create(Assignment $assignment): View
    {
        $this->authorize('create', [Submission::class, $assignment]);

        $existing = $assignment->submissions()
            ->where('user_id', request()->user()->id)
            ->first();

        if ($existing && ! $existing->canResubmit()) {
            return view('submissions.show', ['submission' => $existing->load('assignment.course')]);
        }

        return view('submissions.create', [
            'assignment' => $assignment,
            'maxUploadMb' => UploadLimits::submissionMaxMegabytes(),
            'resubmit' => $existing?->canResubmit() ?? false,
        ]);
    }

    public function store(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->authorize('create', [Submission::class, $assignment]);

        $existing = $assignment->submissions()
            ->where('user_id', $request->user()->id)
            ->first();

        if ($existing && ! $existing->canResubmit()) {
            return back()->with('error', 'You have already submitted work for this assignment.');
        }

        $file = $request->file('file');
        if ($file && ! $file->isValid()) {
            return back()
                ->withInput()
                ->withErrors(['file' => UploadLimits::fileUploadErrorMessage($file->getError())]);
        }

        $maxKb = UploadLimits::submissionMaxKilobytes();

        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:5000'],
            'file' => ['required', 'file', "max:{$maxKb}"],
        ], [
            'file.max' => "The file may not be larger than {$maxKb} kilobytes (about ".UploadLimits::submissionMaxMegabytes().' MB).',
            'file.uploaded' => UploadLimits::fileUploadErrorMessage($file?->getError() ?? UPLOAD_ERR_NO_FILE),
        ]);

        $file = $request->file('file');
        $path = $file->store('submissions', 'public');

        $status = $assignment->isOverdue()
            ? SubmissionStatus::Late
            : SubmissionStatus::Submitted;

        if ($existing) {
            if ($existing->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }

            $existing->update([
                'notes' => $validated['notes'] ?? null,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'status' => $status,
                'submitted_at' => now(),
                'score' => null,
                'letter_grade' => null,
                'feedback' => null,
                'reviewed_at' => null,
                'reviewed_by' => null,
            ]);

            $submission = $existing;
        } else {
            $submission = Submission::query()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $request->user()->id,
                'notes' => $validated['notes'] ?? null,
                'file_path' => $path,
                'file_name' => $file->getClientOriginalName(),
                'status' => $status,
                'submitted_at' => now(),
            ]);
        }

        $lecturer = $assignment->course->lecturer;
        if ($lecturer && $lecturer->isActive()) {
            $lecturer->notify(new SubmissionReceivedNotification($submission));
        }

        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', $existing ? 'Your revised work was submitted.' : 'Your work was submitted successfully.');
    }

    public function show(Submission $submission): View
    {
        $this->authorize('view', $submission);

        $submission->load(['assignment.course', 'student', 'reviewer']);

        return view('submissions.show', compact('submission'));
    }

    public function review(Request $request, Submission $submission): RedirectResponse
    {
        $this->authorize('review', $submission);

        $validated = $request->validate([
            'action' => ['required', 'in:grade,revision,reviewed'],
            'score' => ['nullable', 'numeric', 'min:0', 'max:100', 'required_if:action,grade'],
            'feedback' => ['nullable', 'string', 'max:10000'],
        ]);

        $user = $request->user();

        if ($validated['action'] === 'revision') {
            $submission->update([
                'status' => SubmissionStatus::NeedsRevision,
                'feedback' => $validated['feedback'] ?? $submission->feedback,
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
            ]);

            return back()->with('success', 'Student was asked to revise and resubmit.');
        }

        if ($validated['action'] === 'grade') {
            $score = (float) $validated['score'];
            $letter = GradeHelper::letterFromScore($score);

            $submission->update([
                'status' => SubmissionStatus::Reviewed,
                'score' => $score,
                'letter_grade' => $letter,
                'feedback' => $validated['feedback'] ?? null,
                'reviewed_at' => now(),
                'reviewed_by' => $user->id,
            ]);

            $submission->student->notify(new GradePostedNotification($submission));

            return back()->with('success', 'Grade and feedback posted to the student.');
        }

        $submission->update([
            'status' => SubmissionStatus::Reviewed,
            'feedback' => $validated['feedback'] ?? $submission->feedback,
            'reviewed_at' => now(),
            'reviewed_by' => $user->id,
        ]);

        return back()->with('success', 'Submission marked as reviewed.');
    }
}
