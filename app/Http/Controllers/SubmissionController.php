<?php

namespace App\Http\Controllers;

use App\Enums\SubmissionDeliveryMethod;
use App\Enums\SubmissionSource;
use App\Enums\SubmissionStatus;
use App\Models\Assignment;
use App\Models\Submission;
use App\Notifications\GradePostedNotification;
use App\Notifications\SubmissionReceivedNotification;
use App\Rules\ExternalSubmissionUrl;
use App\Support\ExternalSubmissionLink;
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

        $method = $assignment->delivery_method ?? SubmissionDeliveryMethod::File;
        $maxKb = UploadLimits::submissionMaxKilobytes();
        $file = $request->file('file');

        if ($file && ! $file->isValid()) {
            return back()
                ->withInput()
                ->withErrors(['file' => UploadLimits::fileUploadErrorMessage($file->getError())]);
        }

        $rules = [
            'notes' => ['nullable', 'string', 'max:5000'],
            'external_label' => ['nullable', 'string', 'max:255'],
        ];

        if ($method === SubmissionDeliveryMethod::File) {
            $rules['file'] = ['required', 'file', "max:{$maxKb}"];
        } elseif ($method === SubmissionDeliveryMethod::Link) {
            $rules['external_url'] = ['required', 'url', 'max:2048', new ExternalSubmissionUrl];
        } else {
            $rules['file'] = ['required_without:external_url', 'nullable', 'file', "max:{$maxKb}"];
            $rules['external_url'] = ['required_without:file', 'nullable', 'url', 'max:2048', new ExternalSubmissionUrl];
        }

        $validated = $request->validate($rules, [
            'file.required' => 'Please upload your submission file.',
            'file.required_without' => 'Upload a file or paste a cloud share link.',
            'file.max' => "The file may not be larger than {$maxKb} kilobytes (about ".UploadLimits::submissionMaxMegabytes().' MB).',
            'file.uploaded' => UploadLimits::fileUploadErrorMessage($file?->getError() ?? UPLOAD_ERR_NO_FILE),
            'external_url.required' => 'Paste the share link to your file on Google Drive, Dropbox, or OneDrive.',
            'external_url.required_without' => 'Upload a file or paste a cloud share link.',
        ]);

        $useFile = $assignment->acceptsFileUpload() && $request->hasFile('file');
        $useLink = $assignment->acceptsExternalLink() && filled($validated['external_url'] ?? null);

        if (! $useFile && ! $useLink) {
            return back()
                ->withInput()
                ->withErrors($method === SubmissionDeliveryMethod::Link
                    ? ['external_url' => 'Paste the share link to your uploaded file.']
                    : ['file' => 'Please upload your submission file.']);
        }

        $status = $assignment->isOverdue()
            ? SubmissionStatus::Late
            : SubmissionStatus::Submitted;

        $payload = [
            'notes' => $validated['notes'] ?? null,
            'status' => $status,
            'submitted_at' => now(),
            'score' => null,
            'letter_grade' => null,
            'feedback' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ];

        if ($useFile) {
            $file = $request->file('file');
            $path = $file->store('submissions', 'public');

            $payload['source'] = SubmissionSource::File;
            $payload['file_path'] = $path;
            $payload['file_name'] = $file->getClientOriginalName();
            $payload['external_url'] = null;
            $payload['external_label'] = null;
        } else {
            $externalUrl = $validated['external_url'];

            $payload['source'] = SubmissionSource::Link;
            $payload['external_url'] = $externalUrl;
            $payload['external_label'] = ExternalSubmissionLink::labelFromUrl(
                $externalUrl,
                $validated['external_label'] ?? null
            );
            $payload['file_path'] = null;
            $payload['file_name'] = null;
        }

        if ($existing) {
            if ($existing->file_path) {
                Storage::disk('public')->delete($existing->file_path);
            }

            $existing->update($payload);
            $submission = $existing;
        } else {
            $submission = Submission::query()->create(array_merge($payload, [
                'assignment_id' => $assignment->id,
                'user_id' => $request->user()->id,
            ]));
        }

        $lecturer = $assignment->course->lecturer;
        if ($lecturer && $lecturer->isActive()) {
            $lecturer->notify(new SubmissionReceivedNotification($submission));
        }

        return redirect()
            ->route('submissions.show', $submission)
            ->with('success', $existing ? 'Your revised work was submitted.' : 'Your work was submitted successfully.');
    }

    public function show(Request $request, Submission $submission): View
    {
        $this->authorize('view', $submission);

        $submission->load(['assignment.course', 'student', 'reviewer']);

        $aiGeneration = null;
        if ($request->integer('ai')) {
            $aiGeneration = \App\Models\AiGeneration::query()
                ->where('id', $request->integer('ai'))
                ->where('user_id', $request->user()->id)
                ->first();
        }

        return view('submissions.show', [
            'submission' => $submission,
            'aiGeneration' => $aiGeneration,
            'aiEnabled' => (bool) config('ai.enabled') && (bool) config('ai.features.feedback_draft'),
        ]);
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
